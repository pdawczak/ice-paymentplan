<?php

namespace spec\Ice\PaymentPlan\Calculator;

use Ice\PaymentPlan\Calculator\Exception\UnsupportedPlanException;
use Ice\PaymentPlan\Calculator\PaymentPlanCalculatorInterface;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompositePlanCalculatorSpec extends ObjectBehavior
{
    function let(
        PaymentPlanCalculatorInterface $delegatedFooCalculator
    )
    {
        $delegatedFooCalculator->supportsDefinition('Foo')->willReturn(true);
        $delegatedFooCalculator->supportsDefinition(Argument::not('Foo'))->willReturn(false);
        $this->registerCalculator($delegatedFooCalculator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Calculator\CompositePlanCalculator');
        $this->shouldImplement('Ice\PaymentPlan\Calculator\PaymentPlanCalculatorInterface');
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

    function it_should_use_a_calculator_to_return_a_plan(
        PaymentPlan $fooPlan,
        $delegatedFooCalculator
    )
    {
        $delegatedFooCalculator->getPlan(
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
