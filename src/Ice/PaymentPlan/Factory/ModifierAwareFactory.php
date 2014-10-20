<?php

namespace Ice\PaymentPlan\Factory;

use Ice\PaymentPlan\Factory\Exception\UnsupportedModifierException;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\Factory\PlanModifierInterface;
use Money\Money;

/**
 * Class ModifierAwareFactory
 * @package Ice\PaymentPlan\Factory
 */
class ModifierAwareFactory implements PaymentPlanFactoryInterface
{
    /**
     * @var PaymentPlanFactoryInterface
     */
    private $childFactory;

    /**
     * @var PlanModifierInterface[]
     */
    private $modifiers;

    /**
     * @param PaymentPlanFactoryInterface $childFactory
     */
    public function __construct(PaymentPlanFactoryInterface $childFactory)
    {
        $this->childFactory = $childFactory;
    }

    /**
     * @param $alias
     * @param PaymentPlanFactoryInterface $modifier
     * @return $this
     */
    public function registerModifier($alias, PaymentPlanFactoryInterface $modifier)
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
        $factory = $this->getModifiedFactory(
            $definition->hasAttribute('modifiers') ? $definition->getAttribute('modifiers') : []
        );

        return $factory->getPlan($definition, $amountToPay, $parameters);
    }

    /**
     * @param array|string[] $modifiers
     * @throws UnsupportedModifierException
     * @return PaymentPlanFactoryInterface
     */
    private function getModifiedFactory($modifiers)
    {
        $baseFactory = $this->childFactory;

        foreach ($modifiers as $modifierAlias) {
            if (!isset($this->modifiers[$modifierAlias])) {
                throw new UnsupportedModifierException("Unsupported modifier: '$modifierAlias'");
            }

            $modifier = $this->modifiers[$modifierAlias];
            $modifier->setBaseFactory($baseFactory);
            $baseFactory = $modifier;
        }

        return $baseFactory;
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
        return $this->childFactory->isAvailable($definition, $amountToPay, $parameters);
    }

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        return $this->childFactory->supportsDefinition($definition);
    }
}
