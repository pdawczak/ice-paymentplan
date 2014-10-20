<?php

namespace Ice\PaymentPlan\Calculator;

interface PlanModifierInterface extends PaymentPlanCalculatorInterface
{
    public function setBaseCalculator(PaymentPlanCalculatorInterface $baseCalculator);
}
