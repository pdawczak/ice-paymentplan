<?php

namespace Ice\PaymentPlan;

/**
 * Class PlanDefinition
 *
 * A Payment Plan Definition is determined by its current configuration against a course (or product), and should
 * not vary between users or bookings.
 *
 * A definition always contains a code, but some definitions may be augmented by arbitrary primitive-valued attributes,
 * such as ['course_start_date' => '2014-01-01'], which may be interpreted by the plan factory
 *
 * @package Ice\PaymentPlan
 * @author Rob Hogan <rh389>
 * @immutable
 */
class PlanDefinition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $attributes;

    private function __construct($name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public static function withNameAndAttributesAsArray($code, array $attributes)
    {
        return new static($code, $attributes);
    }

    public static function withName($name)
    {
        return new static($name, []);
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $attributeName
     */
    public function hasAttribute($attributeName)
    {
        return in_array($attributeName, array_keys($this->attributes));
    }

    /**
     * @param string $attributeName
     */
    public function getAttribute($attributeName)
    {
        return $this->attributes[$attributeName];
    }

    public function isContainedBy(PlanDefinition $planDefinition)
    {
        return $planDefinition->name === $this->name;
    }

    public function hasAttributes(array $names)
    {
        return count(array_diff_key(array_flip($names), $this->attributes)) === 0;
    }

    public function __toString()
    {
        return $this->name;
    }
}
