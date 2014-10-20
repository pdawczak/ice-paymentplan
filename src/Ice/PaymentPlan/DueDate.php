<?php

namespace Ice\PaymentPlan;

/**
 * Class DueDate
 *
 * @package Ice\PaymentPlan
 * @author Rob Hogan <rh389>
 * @immutable
 */
class DueDate
{
    /**
     * Four digit year
     *
     * @var int
     */
    private $year;

    /**
     * Between 1 and 12
     *
     * @var int
     */
    private $month;

    /**
     * Between 1 and 31
     *
     * @var int
     */
    private $day;

    private function __construct($year, $month, $day)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public static function fromString($dateString)
    {
        $dateTime = new \DateTime($dateString);
        return new static(
            intval($dateTime->format('Y')),
            intval($dateTime->format('m')),
            intval($dateTime->format('j'))
        );
    }

    /**
     * @param DueDate $dueDate
     * @return bool
     */
    public function isExactlyOneYearEarlierThan(DueDate $dueDate)
    {
        return (
            ($this->year + 1) === $dueDate->year &&
            $this->month === $dueDate->month &&
            $this->day === $dueDate->day
        );
    }
}
