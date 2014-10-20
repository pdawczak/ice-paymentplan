<?php

namespace spec\Ice\PaymentPlan\Calculator\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UnsupportedPlanExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Calculator\Exception\UnsupportedPlanException');
        $this->shouldHaveType('Exception');
    }
}
