<?php

namespace Ice\PaymentPlan\Calculator;

/**
 * Interface PlanModifierInterface
 *
 * @package Ice\PaymentPlan\Calculator
 * @author Rob Hogan <rh389>
 */
interface PlanModifierInterface extends PaymentPlanCalculatorInterface
{
    /**
     * Set the calculator which this modifier will use as a base in subsequent calls to getPlan
     *
     * @param PaymentPlanCalculatorInterface $baseCalculator
     * @return PlanModifierInterface
     */
    public function setBaseCalculator(PaymentPlanCalculatorInterface $baseCalculator);
}
