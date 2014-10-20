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

    private function __construct(Money $amount, DueDate $dueDate = null)
    {
        $this->amount = $amount;
        $this->dueDate = $dueDate;
    }

    public static function immediate(Money $amount)
    {
        return new static($amount);
    }

    public static function withDueDate(Money $amount, DueDate $dueDate)
    {
        return new static($amount, $dueDate);
    }

    public function addToAmount(Money $money)
    {
        return new static($this->amount->add($money), $this->dueDate);
    }

    public function subtractFromAmount(Money $money)
    {
        return new static($this->amount->subtract($money), $this->dueDate);
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getDueDate()
    {
        if ($this->dueDate !== null) {
            return $this->dueDate;
        }
        throw new \Exception('No due date');
    }

    public function hasDueDate()
    {
        return $this->dueDate !== null;
    }
}
