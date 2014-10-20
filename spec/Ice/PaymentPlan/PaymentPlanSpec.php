<?php

namespace spec\Ice\PaymentPlan;

use Ice\PaymentPlan\PlannedPayment;
use Money\Money;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PaymentPlanSpec extends ObjectBehavior
{
    function let(
        PlannedPayment $payment1,
        PlannedPayment $payment2,
        Money $amount1,
        Money $amount2
    )
    {
        $payment1->getAmount()->willReturn($amount1);
        $payment2->getAmount()->willReturn($amount2);
        $this->beConstructedWith([$payment1, $payment2], 'foo', 'foofoo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\PaymentPlan');
    }

    function it_should_have_a_short_description()
    {
        $this->getShortDescription()->shouldReturn('foo');
    }


    function it_should_have_a_long_description()
    {
        $this->getLongDescription()->shouldReturn('foofoo');
    }

    function it_should_calculate_a_plan_total_amount(
        $amount1,
        $amount2,
        Money $totalAmount
    )
    {
        $amount1->add($amount2)->willReturn($totalAmount);
        $this->getTotalAmount()->shouldReturn($totalAmount);
    }
}
