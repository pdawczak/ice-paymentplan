<?php

namespace Ice\PaymentPlan\Calculator;

use Ice\PaymentPlan\Calculator\Exception\UnsupportedModifierException;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\Calculator\PlanModifierInterface;
use Money\Money;

/**
 * Class ModifierAwareCalculator
 * @package Ice\PaymentPlan\Calculator
 */
class ModifierAwareCalculator implements PaymentPlanCalculatorInterface
{
    /**
     * @var PaymentPlanCalculatorInterface
     */
    private $childCalculator;

    /**
     * @var PlanModifierInterface[]
     */
    private $modifiers;

    /**
     * @param PaymentPlanCalculatorInterface $childCalculator
     */
    public function __construct(PaymentPlanCalculatorInterface $childCalculator)
    {
        $this->childCalculator = $childCalculator;
    }

    /**
     * @param $alias
     * @param PaymentPlanCalculatorInterface $modifier
     * @return $this
     */
    public function registerModifier($alias, PaymentPlanCalculatorInterface $modifier)
    {
        $this->modifiers[$alias] = $modifier;
        return $this;
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param \Ice\PaymentPlan\PlanParameters $parameters
     * @internal param string $planCode
     * @return \Ice\PaymentPlan\PaymentPlan
     */
    public function getPlan(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        $calculator = $this->getModifiedCalculator(
            $definition->hasAttribute('modifiers') ? $definition->getAttribute('modifiers') : []
        );

        return $calculator->getPlan($definition, $amountToPay, $parameters);
    }

    /**
     * @param array|string[] $modifiers
     * @throws UnsupportedModifierException
     * @return PaymentPlanCalculatorInterface
     */
    private function getModifiedCalculator($modifiers)
    {
        $baseCalculator = $this->childCalculator;

        foreach ($modifiers as $modifierAlias) {
            if (!isset($this->modifiers[$modifierAlias])) {
                throw new UnsupportedModifierException("Unsupported modifier: '$modifierAlias'");
            }

            $modifier = $this->modifiers[$modifierAlias];
            $modifier->setBaseCalculator($baseCalculator);
            $baseCalculator = $modifier;
        }

        return $baseCalculator;
    }

    /**
     * @param PlanDefinition $definition
     * @param Money $amountToPay
     * @param PlanParameters $parameters
     * @internal param string $planCode
     * @return bool
     */
    public function isAvailable(PlanDefinition $definition, Money $amountToPay, PlanParameters $parameters)
    {
        return $this->childCalculator->isAvailable($definition, $amountToPay, $parameters);
    }

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        return $this->childCalculator->supportsDefinition($definition);
    }
}
