<?php

namespace Ice\PaymentPlan;

use Money\Money;

/**
 * Class PlannedPayment
 *
 * @package Ice\PaymentPlan
 * @author Rob Hogan <rh389>
 * @immutable
 */
class PlannedPayment
{
    /** @var Money  */
    private $amount;

    /** @var DueDate  */
    private $dueDate;

    /**
     * @param Money $amount
     * @param DueDate $dueDate
     */
    private function __construct(Money $amount, DueDate $dueDate = null)
    {
        $this->amount = $amount;
        $this->dueDate = $dueDate;
    }

    /**
     * Create a payment due immediately
     *
     * @param Money $amount
     * @return PlannedPayment
     */
    public static function immediate(Money $amount)
    {
        return new static($amount);
    }

    /**
     * Create a payment due on some specified date
     *
     * @param Money $amount
     * @param DueDate $dueDate
     * @return PlannedPayment
     */
    public static function withDueDate(Money $amount, DueDate $dueDate)
    {
        return new static($amount, $dueDate);
    }

    /**
     * Return a new PlannedPayment with the same due date but amount increased by $money
     *
     * @param Money $money
     * @return PlannedPayment
     */
    public function addAmount(Money $money)
    {
        return new static($this->amount->add($money), $this->dueDate);
    }

    /**
     * Return a new PlannedPayment with the same due date but amount lessened by $money
     *
     * @param Money $money
     * @return PlannedPayment
     */
    public function subtractAmount(Money $money)
    {
        return new static($this->amount->subtract($money), $this->dueDate);
    }

    /**
     * @return Money
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return DueDate
     * @throws \Exception
     */
    public function getDueDate()
    {
        if ($this->dueDate !== null) {
            return $this->dueDate;
        }
        throw new \Exception('No due date');
    }

    /**
     * @return bool
     */
    public function hasDueDate()
    {
        return $this->dueDate !== null;
    }
}
