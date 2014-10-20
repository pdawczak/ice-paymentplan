<?php

namespace Ice\PaymentPlan\Factory;

use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\Factory\Exception\UnsupportedPlanException;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\DueDate;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\PaymentPlan;
use Money\Money;

class PercentOnDateFactory implements PaymentPlanFactoryInterface
{
    public function supportsDefinition(PlanDefinition $planCode)
    {
        return $planCode->getName() === 'PercentOnDate';
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @return PaymentPlan
     */
    public function getPlan(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        /** @var array $paymentProportions */
        $paymentProportions = $definition->getAttribute('payments');

        //The values of the payments array is a ratios array
        /** @var Money[] $amounts */
        $amounts = $amountToPay->allocate(array_values($paymentProportions));

        $payments = array_map(function($rawDate, $amount) {
            //rawDate is either 'immediate' or a 'Y-m-d' string, or invalid.
            if ($rawDate === 'immediate') {
                return PlannedPayment::immediate($amount);
            }
            return PlannedPayment::withDueDate($amount, DueDate::fromString($rawDate));
        }, array_keys($paymentProportions), $amounts);

        return new PaymentPlan($payments, $definition->getAttribute('short_description'), $definition->getAttribute('long_description'));
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @return bool
     */
    public function isAvailable(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        return $definition->hasAttributes([
            'short_description',
            'long_description',
            'payments'
        ]);
    }
}
