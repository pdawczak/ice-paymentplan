<?php

namespace spec\Ice\PaymentPlan;

use Ice\PaymentPlan\DueDate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DueDateSpec extends ObjectBehavior
{
    function it_is_initializable_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['2014-01-01']);
        $this->shouldHaveType('Ice\PaymentPlan\DueDate');
    }

    function it_can_tell_when_a_due_date_is_exactly_one_year_earlier()
    {
        $this->beConstructedThrough('fromString', ['2014-01-01']);
        $this->isExactlyOneYearEarlierThan(DueDate::fromString('2015-01-01'))->shouldReturn(true);
        $this->isExactlyOneYearEarlierThan(DueDate::fromString('2015-01-02'))->shouldReturn(false);
        $this->isExactlyOneYearEarlierThan(DueDate::fromString('2015-02-01'))->shouldReturn(false);
        $this->isExactlyOneYearEarlierThan(DueDate::fromString('2013-01-01'))->shouldReturn(false);
    }

    function it_can_be_formatted()
    {
        $this->beConstructedThrough('fromString', ['2014-01-01']);
        $this->format('Y/m/d')->shouldReturn('2014/01/01');
    }

    function it_can_be_cast_to_a_string()
    {
        $this->beConstructedThrough('fromString', ['2014-01-01']);
        $this->__toString()->shouldReturn('2014-01-01');
    }
}
