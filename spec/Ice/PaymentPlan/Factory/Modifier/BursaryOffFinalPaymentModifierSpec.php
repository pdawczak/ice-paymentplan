<?php

namespace spec\Ice\PaymentPlan\Factory\Modifier;

use Ice\PaymentPlan\DueDate;
use Ice\PaymentPlan\PlanDefinition as Def;
use Ice\PaymentPlan\PlanParameters as Params;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\Factory\PaymentPlanFactoryInterface;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BursaryOffFinalPaymentModifierSpec extends ObjectBehavior
{
    function let(
        PaymentPlanFactoryInterface $baseFactory
    ) {
        $this->setBaseFactory($baseFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Factory\Modifier\BursaryOffFinalPaymentModifier');
        $this->shouldImplement('Ice\PaymentPlan\Factory\PlanModifierInterface');
    }

    function it_supports_the_same_plans_as_the_factory_it_decorates(
        $baseFactory
    )
    {
        $baseFactory->supportsDefinition(Def::withName('foo'))->willReturn(true);
        $baseFactory->supportsDefinition(Def::withName('bar'))->willReturn(false);
        $baseFactory->supportsDefinition(Def::withName('foo'))->shouldBeCalled();
        $baseFactory->supportsDefinition(Def::withName('bar'))->shouldBeCalled();
        $this->supportsDefinition(Def::withName('foo'))->shouldEqual(true);
        $this->supportsDefinition(Def::withName('bar'))->shouldEqual(false);
    }

    function it_is_available_whenever_the_decoratee_is_available(
        $baseFactory,
        $mockBool
    )
    {
        $baseFactory->isAvailable(Def::withName('Foo'), Money::GBP(100), Params::none())
            ->shouldBeCalled()
            ->willReturn($mockBool)
        ;
        $this->isAvailable(Def::withName('Foo'), Money::GBP(100), Params::none())->shouldReturn($mockBool);
    }

    function it_should_generate_a_plan_using_its_child_factory(
        $baseFactory,
        PaymentPlan $childPlan
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none())->willReturn($childPlan);
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none())->shouldBeCalled();
        $this->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none());
    }

    function it_should_not_modify_a_plan_if_there_is_no_bursary(
        $baseFactory,
        PaymentPlan $childPlan
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none())->willReturn($childPlan);
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none())->shouldBeCalled();
        $this->getPlan(Def::withName('Foo'), Money::GBP(100), Params::none())->shouldReturn($childPlan);
    }

    function it_should_apply_the_bursary_to_the_final_payment_when_there_are_two(
        $baseFactory,
        PaymentPlan $planBeforeBursary,
        PaymentPlan $finalPlan
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(200), Argument::any())
            ->shouldBeCalled()
            ->willReturn($planBeforeBursary);

        $planBeforeBursary->getPlannedPayments()->willReturn([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(150), DueDate::fromString('2014-01-01'))
        ]);

        $planBeforeBursary->getShortDescription()->willReturn('foo');
        $planBeforeBursary->getLongDescription()->willReturn('foofoo');

        $finalPlan = new PaymentPlan([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(100), DueDate::fromString('2014-01-01'))
        ], 'foo', 'foofoo');

        $this->getPlan(
            Def::withName('Foo'),
            Money::GBP(150),
            Params::fromArray(['bursary_total_deduction'=>50])
        )->shouldBeLike($finalPlan);
    }

    function it_should_apply_the_bursary_when_there_is_only_one_payment(
        $baseFactory,
        PaymentPlan $planBeforeBursary,
        PaymentPlan $finalPlan
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(200), Argument::any())
            ->willReturn($planBeforeBursary);

        $planBeforeBursary->getPlannedPayments()->willReturn([
            PlannedPayment::immediate(Money::GBP(200))
        ]);
        $planBeforeBursary->getShortDescription()->willReturn('foo');
        $planBeforeBursary->getLongDescription()->willReturn('foofoo');

        $finalPlan = new PaymentPlan([
            PlannedPayment::immediate(Money::GBP(150))
        ], 'foo', 'foofoo');

        $this->getPlan(
            Def::withName('Foo'),
            Money::GBP(150),
            Params::fromArray(['bursary_total_deduction'=>50])
        )->shouldBeLike($finalPlan);
    }

    function it_should_apply_the_bursary_only_to_the_final_payment_in_a_year(
        $baseFactory,
        PaymentPlan $planBeforeBursary
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(250), Argument::any())
            ->willReturn($planBeforeBursary);

        $planBeforeBursary->getPlannedPayments()->willReturn([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-01-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-02-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-03-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-04-01'))
        ]);
        $planBeforeBursary->getShortDescription()->willReturn('foo');
        $planBeforeBursary->getLongDescription()->willReturn('foofoo');

        $finalPlan = new PaymentPlan([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-01-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-02-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-03-01')),
            PlannedPayment::withDueDate(Money::GBP(30), DueDate::fromString('2014-04-01'))
        ], 'foo', 'foofoo');

        $this->getPlan(
            Def::withName('Foo'),
            Money::GBP(230),
            Params::fromArray(['bursary_total_deduction'=>20])
        )->shouldBeLike($finalPlan);
    }

    function it_should_spread_the_bursary_when_the_plan_spans_multiple_years(
        $baseFactory,
        PaymentPlan $planBeforeBursary,
        PaymentPlan $finalPlan
    )
    {
        $baseFactory->getPlan(Def::withName('Foo'), Money::GBP(250), Argument::any())
            ->willReturn($planBeforeBursary);

        $planBeforeBursary->getPlannedPayments()->willReturn([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-01-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-06-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2015-01-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2015-06-01'))
        ]);
        $planBeforeBursary->getShortDescription()->willReturn('foo');
        $planBeforeBursary->getLongDescription()->willReturn('foofoo');

        $finalPlan = new PaymentPlan([
            PlannedPayment::immediate(Money::GBP(50)),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2014-01-01')),
            PlannedPayment::withDueDate(Money::GBP(40), DueDate::fromString('2014-06-01')),
            PlannedPayment::withDueDate(Money::GBP(50), DueDate::fromString('2015-01-01')),
            PlannedPayment::withDueDate(Money::GBP(40), DueDate::fromString('2015-06-01'))
        ], 'foo', 'foofoo');

        $this->getPlan(
            Def::withName('Foo'),
            Money::GBP(230),
            Params::fromArray(['bursary_total_deduction'=>20])
        )
            ->shouldBeLike($finalPlan)
        ;
    }
}
