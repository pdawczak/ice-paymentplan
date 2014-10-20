<?php

namespace spec\Ice\PaymentPlan\Factory\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InsufficientParametersExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ice\PaymentPlan\Factory\Exception\InsufficientParametersException');
        $this->shouldHaveType('Exception');
    }
}
