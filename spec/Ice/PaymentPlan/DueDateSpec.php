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
        $this->shouldBeExactlyOneYearEarlierThan(DueDate::fromString('2015-01-01'));
        $this->shouldNotBeExactlyOneYearEarlierThan(DueDate::fromString('2015-01-02'));
        $this->shouldNotBeExactlyOneYearEarlierThan(DueDate::fromString('2015-02-01'));
        $this->shouldNotBeExactlyOneYearEarlierThan(DueDate::fromString('2013-01-01'));
    }
}
