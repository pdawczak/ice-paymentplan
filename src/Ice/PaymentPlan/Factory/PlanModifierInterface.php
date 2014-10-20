<?php

namespace Ice\PaymentPlan\Factory;

interface PlanModifierInterface extends PaymentPlanFactoryInterface
{
    public function setBaseFactory(PaymentPlanFactoryInterface $baseFactory);
}
