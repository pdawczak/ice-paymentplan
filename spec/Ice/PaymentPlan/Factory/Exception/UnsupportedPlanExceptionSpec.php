<?php

namespace spec\Ice\PaymentPlan\Factory\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UnsupportedPlanExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Factory\Exception\UnsupportedPlanException');
        $this->shouldHaveType('Exception');
    }
}
