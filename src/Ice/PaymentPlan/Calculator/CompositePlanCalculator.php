<?php

namespace Ice\PaymentPlan\Calculator;

use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\Calculator\Exception\UnsupportedPlanException;
use Ice\PaymentPlan\PlanParameters;
use Money\Money;

/**
 * Class CompositePlanCalculator
 *
 * The composite calculator delegates plan generation to the the first registered calculator which supports the given
 * definition.
 *
 * @package Ice\PaymentPlan\Calculator
 * @author Rob Hogan <rh389>
 */
class CompositePlanCalculator implements PaymentPlanCalculatorInterface
{
    /**
     * @var PaymentPlanCalculatorInterface[] array
     */
    protected $factories = [];

    /**
     * @param PaymentPlanCalculatorInterface $calculator
     */
    public function registerCalculator(PaymentPlanCalculatorInterface $calculator)
    {
        $this->factories[] = $calculator;
    }

    /**
     * @param PlanDefinition $definition
     * @throws UnsupportedPlanException
     * @return PaymentPlanCalculatorInterface
     */
    private function getCalculatorFor(PlanDefinition $definition)
    {
        foreach ($this->factories as $calculator) {
            if ($calculator->supportsDefinition($definition)) {
                return $calculator;
            }
        }
        throw new UnsupportedPlanException('Unsupported plan: '. $definition);
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param \Ice\PaymentPlan\PlanParameters $parameters
     * @throws UnsupportedPlanException
     * @internal param string $planCode
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    public function getPlan(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        return $this->getCalculatorFor($definition)->getPlan($definition, $amountToPay, $parameters);
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @throws UnsupportedPlanException
     * @return bool
     */
    public function isAvailable(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        return $this->getCalculatorFor($definition)->isAvailable($definition, $amountToPay, $parameters);
    }

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        foreach ($this->factories as $calculator) {
            if ($calculator->supportsDefinition($definition)) {
                return true;
            }
        }
        return false;
    }
}
