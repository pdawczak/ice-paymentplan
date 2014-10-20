<?php

namespace Ice\PaymentPlan;

/**
 * Class PlanParameters
 *
 * Payment plan parameters represent key-value properties of the current person or booking which may affect the
 * calculated plan, for example bursary_total_deduction is a property of a particular booking, which affects the plan
 * if the bursary_off_final_payment modifier is used.
 *
 * @package Ice\PaymentPlan
 * @author Rob Hogan <rh389>
 * @immutable
 */
class PlanParameters
{
    private $parameters;

    private function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public static function fromArray(array $attributes)
    {
        return new static($attributes);
    }

    public function hasParameter($name)
    {
        return in_array($name, array_keys($this->parameters));
    }

    public function hasParameters(array $names)
    {
        return count(array_diff_key(array_flip($names), $this->parameters)) === 0;
    }

    public function get($name)
    {
        return $this->parameters[$name];
    }

    public static function none()
    {
        return new static([]);
    }
}
