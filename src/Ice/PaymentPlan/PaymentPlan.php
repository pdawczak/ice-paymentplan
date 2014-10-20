<?php

namespace Ice\PaymentPlan;

use Money\Money;

/**
 * Class PaymentPlan
 *
 * @package Ice\PaymentPlan
 * @author Rob Hogan <rh389>
 * @immutable
 */
class PaymentPlan
{
    /**
     * @var PlannedPayment[]
     */
    private $plannedPayments = [];

    /**
     * @var string
     */
    private $shortDescription;

    /**
     * @var string
     */
    private $longDescription;

    public function __construct(
        array $plannedPayments = [],
        $shortDescription,
        $longDescription
    )
    {
        $this->plannedPayments = $plannedPayments;
        $this->shortDescription = $shortDescription;
        $this->longDescription = $longDescription;
    }

    /**
     * @return PlannedPayment[]
     */
    public function getPlannedPayments()
    {
        return $this->plannedPayments;
    }

    /**
     * @return Money
     */
    public function getTotalAmount()
    {
        $total = null;
        foreach ($this->plannedPayments as $payment) {
            if (!$total instanceof Money) {
                $total = $payment->getAmount();
            } else {
                $total = $total->add($payment->getAmount());
            }
        }
        return $total;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
}
