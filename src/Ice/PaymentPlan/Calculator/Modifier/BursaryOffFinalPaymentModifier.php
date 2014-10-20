<?php

namespace Ice\PaymentPlan\Calculator\Modifier;

use Ice\PaymentPlan\Calculator\PlanModifierInterface;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\Calculator\PaymentPlanCalculatorInterface;
use Money\Money;

/**
 * Class BursaryOffFinalPaymentModifier
 *
 * @package Ice\PaymentPlan\Calculator\Modifier
 * @author Rob Hogan <rh389>
 */
class BursaryOffFinalPaymentModifier implements PlanModifierInterface
{
    /** @var PaymentPlanCalculatorInterface */
    private $childCalculator;

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        //Support exactly what the child supports
        return $this->childCalculator->supportsDefinition($definition);
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @internal param string $planCode
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    public function getPlan(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        if (
            $parameters->hasParameter('bursary_total_deduction') &&
            ($bursaryTotalDeduction = intval($parameters->get('bursary_total_deduction'))) > 0
        ) {
            $absoluteBursaryTotal = Money::GBP($bursaryTotalDeduction);
            $amountBeforeBursary = $amountToPay->add($absoluteBursaryTotal);
            $planWithoutBursary = $this->childCalculator->getPlan($definition, $amountBeforeBursary, $parameters);
            return $this->subtractBursaryFromPlan($planWithoutBursary, $absoluteBursaryTotal);
        }

        return $this->childCalculator->getPlan($definition, $amountToPay, $parameters);
    }

    /**
     * @param \Ice\PaymentPlan\PaymentPlan $plan
     * @param Money $amountToSubtract
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    private function subtractBursaryFromPlan(PaymentPlan $plan, Money $amountToSubtract)
    {
        /** @var PlannedPayment[] $newReversedPlannedPayments */
        $newReversedPlannedPayments = array_reverse($plan->getPlannedPayments());

        //Work out which payments to spread the subtraction over.
        $indicesToSubtractFrom = $this->getIndicesToSubtractFrom($newReversedPlannedPayments);

        //Work out the sizes of the individual subtractions
        $spreadDiscounts = $this->splitMoneyEvenly($amountToSubtract, count($indicesToSubtractFrom));

        foreach ($spreadDiscounts as $loopIndex => $discountPart) {
            $paymentIndex = $indicesToSubtractFrom[$loopIndex];
            $newReversedPlannedPayments[$paymentIndex] =
                $newReversedPlannedPayments[$paymentIndex]->subtractFromAmount($discountPart);
        }

        return new PaymentPlan(
            array_reverse($newReversedPlannedPayments),
            $plan->getShortDescription(),
            $plan->getLongDescription()
        );
    }

    /**
     * Given an array of planned payments *IN REVERSE CHRONOLOGICAL ORDER*, return the indices of those which should
     * be subtracted from under this policy.
     *
     * @param PlannedPayment[] $reversedOriginalPlannedPayments
     * @return array|integer[]
     */
    private function getIndicesToSubtractFrom(array $reversedOriginalPlannedPayments)
    {
        //We always want to subtract from the final payment, which will be the first in the given reverse-order array
        $indicesToSubtractFrom = [0];

        /*
         * The business policy is to deduct the bursary from the final payment of each year of the course.
         *
         * Step through the payments in reverse chronological order. Always deduct from the first of these (the final
         * payment) and also from any payment a due a year earlier (and a year earlier than that...).
         */
        foreach ($reversedOriginalPlannedPayments as $index => $plannedPayment) {
            //On first run, note that we've already chosen to subtract from the final payment
            if (!isset($previouslyIdentifiedPayment)) {
                $previouslyIdentifiedPayment = $plannedPayment;
                continue;
            }

            //Only deduct from other payments if they are exactly one year earlier than the payment previously marked
            //for deduction. This will be the case if the payment plan spans multiple years.
            if (
                $plannedPayment->hasDueDate() &&
                $plannedPayment->getDueDate()->isExactlyOneYearEarlierThan($previouslyIdentifiedPayment->getDueDate())
            ) {
                //This payment should be deducted from. Remember its index.
                $indicesToSubtractFrom[] = $index;
                $previouslyIdentifiedPayment = $plannedPayment;
            }
        }

        return $indicesToSubtractFrom;
    }

    /**
     * @param Money $total
     * @param integer $numberOfParts
     * @return Money[]
     */
    private function splitMoneyEvenly(Money $total, $numberOfParts)
    {
        /*
         * Use money's allocate method to divide the amount by the divisor - this is better than just dividing manually
         * as the allocate algorithm ensures against the total changing due to rounding error.
         */

        if (1 === $numberOfParts) {
            return [$total];
        }

        if (!is_int($numberOfParts) || $numberOfParts <= 0) {
            throw new \RuntimeException("Invalid divisor");
        }

        //Use an array with $numberOfParts elements each with value 1/$numberOfParts as the input $ratios
        return $total->allocate(array_pad([], $numberOfParts, 1 / $numberOfParts));
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @return bool
     */
    public function isAvailable(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        return $this->childCalculator->isAvailable($definition, $amountToPay, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCalculator(PaymentPlanCalculatorInterface $baseCalculator)
    {
        $this->childCalculator = $baseCalculator;
        return $this;
    }
}
