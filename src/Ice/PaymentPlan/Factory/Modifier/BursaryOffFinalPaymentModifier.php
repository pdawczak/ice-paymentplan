<?php

namespace Ice\PaymentPlan\Factory\Modifier;

use Ice\PaymentPlan\Factory\PlanModifierInterface;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\Factory\PaymentPlanFactoryInterface;
use Money\Money;

class BursaryOffFinalPaymentModifier implements PlanModifierInterface
{
    /** @var PaymentPlanFactoryInterface */
    private $childFactory;

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        //Support exactly what the child supports
        return $this->childFactory->supportsDefinition($definition);
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
            $planWithoutBursary = $this->childFactory->getPlan($definition, $amountBeforeBursary, $parameters);
            return $this->subtractBursaryFromPlan($planWithoutBursary, $absoluteBursaryTotal);
        }

        return $this->childFactory->getPlan($definition, $amountToPay, $parameters);
    }

    /**
     * @param \Ice\PaymentPlan\PaymentPlan $plan
     * @param Money $amountToSubtract
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    private function subtractBursaryFromPlan(PaymentPlan $plan, Money $amountToSubtract)
    {
        /** @var PlannedPayment[] $reversedOriginalPlannedPayments */
        $reversedOriginalPlannedPayments = array_reverse($plan->getPlannedPayments());

        /** @var PlannedPayment[] $newReversedPlannedPayments */
        $newReversedPlannedPayments = [];
        $indicesToSubtractFrom = [];

        /*
         * The business policy is to deduct the bursary from the final payment of each year of the course.
         *
         * Step through the payments in reverse chronological order. Always deduct from the first of these (the final
         * payment) and also from any payment a due a year earlier (and a year earlier than that...).
         */
        foreach ($reversedOriginalPlannedPayments as $index => $plannedPayment) {
            $newReversedPlannedPayments[] = $plannedPayment;

            //Only consider dated payments here
            if (!$plannedPayment->hasDueDate()) {
                continue;
            }

            //If we've already identified a payment to deduct from, continue unless this payment is the corresponding
            //payment for the previous year.
            if (
                isset($previouslyIdentifiedPayment) &&
                !$plannedPayment->getDueDate()->isExactlyOneYearEarlierThan($previouslyIdentifiedPayment->getDueDate())
            ) {
                continue;
            }

            //This payment should be deducted from. Remember its index.
            $indicesToSubtractFrom[] = $index;
            $previouslyIdentifiedPayment = $plannedPayment;
        }

        if (0 === count($indicesToSubtractFrom)) {
            //No payments were identified - this will be the case if full payment is due immediately. Deduct everything
            //from the final (and probably only) payment.
            $indicesToSubtractFrom = [0];
        }

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
     * @param Money $total
     * @param $numberOfParts
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
        return $this->childFactory->isAvailable($definition, $amountToPay, $parameters);
    }

    public function setBaseFactory(PaymentPlanFactoryInterface $baseFactory)
    {
        $this->childFactory = $baseFactory;
    }
}
