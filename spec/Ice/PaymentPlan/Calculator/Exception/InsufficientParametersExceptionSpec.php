<?php

namespace spec\Ice\PaymentPlan\Calculator\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InsufficientParametersExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Calculator\Exception\InsufficientParametersException');
        $this->shouldHaveType('Exception');
    }
}
