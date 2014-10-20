<?php

namespace Ice\PaymentPlan\Calculator;

use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PaymentPlan;
use Money\Money;

interface PaymentPlanCalculatorInterface
{
    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param \Ice\PaymentPlan\PlanParameters $parameters
     * @internal param string $planCode
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    public function getPlan(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters);

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @internal param string $planCode
     * @return bool
     */
    public function isAvailable(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters);

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition);
}
