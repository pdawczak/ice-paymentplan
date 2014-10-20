<?php

namespace spec\Ice\PaymentPlan\Factory;

use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlanParameters;
use Ice\PaymentPlan\DueDate;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\PaymentPlan;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PercentOnDateFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Factory\PercentOnDateFactory');
        $this->shouldImplement('Ice\PaymentPlan\Factory\PaymentPlanFactoryInterface');
    }

    function it_should_only_support_percent_on_date()
    {
        $this->supportsDefinition(PlanDefinition::withName('PercentOnDate'))->shouldReturn(true);
        $this->supportsDefinition(PlanDefinition::withName('Foo'))->shouldReturn(false);
        $this->supportsDefinition(PlanDefinition::withName('Bar'))->shouldReturn(false);
    }

    function it_should_require_definition_attributes()
    {
        $this->isAvailable(
            PlanDefinition::withName('PercentOnDate'),
            Money::GBP(100),
            PlanParameters::none()
        )->shouldReturn(false);

        $this->isAvailable(
            PlanDefinition::withNameAndAttributesAsArray('PercentOnDate', [
                'short_description' => 'Foo',
                'long_description' => 'FooFoo',
                'payments' => [
                    'immediate' => 0.1,
                    '2014-01-01' => 0.7,
                    '2015-01-01' => 0.2
                ]
            ]),
            Money::GBP(100),
            PlanParameters::none()
        )->shouldReturn(true);
    }

    function it_should_return_a_plan_based_on_definition_attributes()
    {
        $expected = new PaymentPlan([
                PlannedPayment::immediate(Money::GBP(10)),
                PlannedPayment::withDueDate(Money::GBP(70), DueDate::fromString('2014-01-01')),
                PlannedPayment::withDueDate(Money::GBP(20), DueDate::fromString('2015-01-01'))
        ], 'Foo', 'FooFoo');

        $this->getPlan(PlanDefinition::withNameAndAttributesAsArray('PercentOnDate', [
                'short_description' => 'Foo',
                'long_description' => 'FooFoo',
                'payments' => [
                    'immediate' => 0.1,
                    '2014-01-01' => 0.7,
                    '2015-01-01' => 0.2
                ]
            ]),
            Money::GBP(100),
            PlanParameters::none()
        )->shouldBeLike($expected);
    }
}
