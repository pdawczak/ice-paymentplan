<?php

namespace spec\Ice\PaymentPlan\Factory;

use Ice\PaymentPlan\Factory\Exception\UnsupportedPlanException;
use Ice\PaymentPlan\Factory\PaymentPlanFactoryInterface;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PaymentPlanCompositeFactorySpec extends ObjectBehavior
{
    function let(
        PaymentPlanFactoryInterface $delegatedFooFactory
    )
    {
        $delegatedFooFactory->supportsDefinition('Foo')->willReturn(true);
        $delegatedFooFactory->supportsDefinition(Argument::not('Foo'))->willReturn(false);
        $this->registerFactory($delegatedFooFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Factory\PaymentPlanCompositeFactory');
        $this->shouldImplement('Ice\PaymentPlan\Factory\PaymentPlanFactoryInterface');
    }

    function it_should_throw_an_exception_for_an_unsupported_plan()
    {
        $this->shouldThrow(new UnsupportedPlanException(
            'Unsupported plan: Bar'
        ))->during('getPlan', [
            PlanDefinition::withName('Bar'),
            Money::GBP(100),
            PlanParameters::none()]
        );
    }

    function it_should_use_a_factory_to_return_a_plan(
        PaymentPlan $fooPlan,
        $delegatedFooFactory
    )
    {
        $delegatedFooFactory->getPlan(
            PlanDefinition::withName('Foo'),
            Money::GBP(100),
            PlanParameters::none()
        )->willReturn($fooPlan);

        $this->getPlan(
            PlanDefinition::withName('Foo'),
            Money::GBP(100),
            PlanParameters::none()
        )->shouldReturn($fooPlan);
    }

    function it_should_support_whatever_plan_codes_its_children_support()
    {
        $this->supportsDefinition(PlanDefinition::withName('Foo'))->shouldReturn(true);
        $this->supportsDefinition(PlanDefinition::withName('Bar'))->shouldReturn(false);
    }
}
