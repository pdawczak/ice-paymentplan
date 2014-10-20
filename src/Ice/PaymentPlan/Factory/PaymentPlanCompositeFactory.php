<?php

namespace Ice\PaymentPlan\Factory;

use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\Factory\Exception\UnsupportedPlanException;
use Ice\PaymentPlan\PlanParameters;
use Money\Money;

/**
 * Class PaymentPlanCompositeFactory
 *
 * The composite factory delegates plan generation to the the first registered factory which supports the given definition.
 * This is intended to be used as a 'Master' factory when given all other factory implementations
 *
 * @package Ice\PaymentPlan\Factory
 * @author Rob Hogan <rh389>
 */
class PaymentPlanCompositeFactory implements PaymentPlanFactoryInterface
{
    /**
     * @var PaymentPlanFactoryInterface[] array
     */
    protected $factories = [];

    /**
     * @param PaymentPlanFactoryInterface $factory
     */
    public function registerFactory(PaymentPlanFactoryInterface $factory)
    {
        $this->factories[] = $factory;
    }

    /**
     * @param PlanDefinition $definition
     * @throws UnsupportedPlanException
     * @return PaymentPlanFactoryInterface
     */
    private function getFactoryFor(PlanDefinition $definition)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supportsDefinition($definition)) {
                return $factory;
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
        return $this->getFactoryFor($definition)->getPlan($definition, $amountToPay, $parameters);
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
        return $this->getFactoryFor($definition)->isAvailable($definition, $amountToPay, $parameters);
    }

    /**
     * @param PlanDefinition $definition
     * @return bool
     */
    public function supportsDefinition(PlanDefinition $definition)
    {
        foreach ($this->factories as $factory) {
            if ($factory->supportsDefinition($definition)) {
                return true;
            }
        }
        return false;
    }
}
