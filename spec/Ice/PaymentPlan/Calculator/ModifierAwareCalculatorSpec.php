<?php

namespace spec\Ice\PaymentPlan\Calculator;

use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\Calculator\PlanModifierInterface;
use Ice\PaymentPlan\Calculator\PaymentPlanCalculatorInterface;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\PlanDefinition;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ModifierAwareCalculatorSpec extends ObjectBehavior
{
    function let(
        PaymentPlanCalculatorInterface $basePlan
    )
    {
        $this->beConstructedWith($basePlan);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Calculator\ModifierAwareCalculator');
        $this->shouldImplement('Ice\PaymentPlan\Calculator\PaymentPlanCalculatorInterface');
    }

    function it_should_support_the_definitions_of_its_base_calculator($basePlan, $mockBool, PlanDefinition $def)
    {
        $basePlan->supportsDefinition($def)->willReturn($mockBool);
        $this->supportsDefinition($def)->shouldReturn($mockBool);
    }

    function it_should_have_the_same_availability_as_its_base_calculator(
        $basePlan,
        $mockBool,
        PlanDefinition $def,
        PlanParameters $params,
        Money $amount
    )
    {
        $basePlan->isAvailable($def, $amount, $params)->willReturn($mockBool);
        $this->isAvailable($def, $amount, $params)->shouldReturn($mockBool);
    }

    function it_should_use_a_registered_modifier_when_it_is_part_of_the_plan_definition(
        $basePlan,
        PlanModifierInterface $modifier,
        PaymentPlan $planProvidedByModifier,
        PaymentPlan $unmodifiedPlan
    )
    {
        $definition = PlanDefinition::withNameAndAttributesAsArray('Foo', [
            'modifiers' => [
                'mymodifier'
            ]
        ]);

        $this->registerModifier('mymodifier', $modifier);

        $modifier->setBaseCalculator($basePlan)->shouldBeCalled();
        $modifier->getPlan($definition, Money::GBP(100), PlanParameters::none())
            ->shouldBeCalled()
            ->willReturn($planProvidedByModifier)
        ;

        $basePlan->getPlan(PlanDefinition::withName('Foo'), Money::GBP(100), PlanParameters::none())
            ->willReturn($unmodifiedPlan);

        $this->getPlan($definition, Money::GBP(100), PlanParameters::none())
            ->shouldReturn($planProvidedByModifier)
        ;

        $this->getPlan(PlanDefinition::withName('Foo'), Money::GBP(100), PlanParameters::none())
            ->shouldReturn($unmodifiedPlan)
        ;
    }
}
