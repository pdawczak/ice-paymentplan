<?php

namespace spec\Ice\PaymentPlan;

use Money\Money;
use Ice\PaymentPlan\DueDate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PlannedPaymentSpec extends ObjectBehavior
{
    function let(
        Money $amount,
        DueDate $dueDate
    )
    {
        $this->beConstructedThrough('withDueDate', [$amount, $dueDate]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\PlannedPayment');
    }

    function it_should_expose_its_amount($amount)
    {
        $this->getAmount()->shouldReturn($amount);
    }

    function it_should_say_whether_it_has_a_due_date()
    {
        $this->hasDueDate()->shouldReturn(true);
    }

    function it_should_not_have_a_due_date_if_it_is_immediate($amount)
    {
        $this->beConstructedThrough('immediate', [$amount]);
        $this->hasDueDate()->shouldReturn(false);
        $this->shouldThrow()->during('getDueDate');
    }

    function it_should_expose_its_due_date($dueDate)
    {
        $this->getDueDate()->shouldReturn($dueDate);
    }
}
