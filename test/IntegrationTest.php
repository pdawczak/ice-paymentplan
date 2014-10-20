<?php

namespace test;

use Ice\PaymentPlan\DueDate;
use Ice\PaymentPlan\Calculator\LegacySupportDecorator;
use Ice\PaymentPlan\Calculator\Modifier\BursaryOffFinalPaymentModifier;
use Ice\PaymentPlan\Calculator\ModifierAwareCalculator;
use Ice\PaymentPlan\Calculator\CompositePlanCalculator;
use Ice\PaymentPlan\Calculator\PercentOnDateCalculator;
use Ice\PaymentPlan\PaymentPlan;
use Ice\PaymentPlan\PlanDefinition;
use Ice\PaymentPlan\PlannedPayment;
use Ice\PaymentPlan\PlanParameters;
use Money\Money;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    function setUp()
    {
        $compositeFactory = new CompositePlanCalculator();
        $compositeFactory->registerCalculator(new PercentOnDateCalculator());
        $this->factory = new ModifierAwareCalculator($compositeFactory);
        $this->factory->registerModifier('bursary_off_final_payment', new BursaryOffFinalPaymentModifier());
    }

    function testIntegration()
    {
        $definition = PlanDefinition::withNameAndAttributesAsArray(
            'PercentOnDate',
            [
                'short_description' => '20/40/40 split',
                'long_description' => '20% immediately, 40% on 2014/01/01, 40% on 2014/03/01, except that bursaries are always taken from the final instalment',
                'payments' => [
                    'immediate' => 0.2,
                    '2014-01-01' => 0.4,
                    '2014-03-01' => 0.4
                ],
                'modifiers' => [
                    'bursary_off_final_payment'
                ]
            ]
        );

        $params = PlanParameters::fromArray([
            'bursary_total_deduction' => 10
        ]);

        $this->assertTrue($this->factory->isAvailable(
            $definition,
            Money::GBP(90),
            $params
        ));

        $plan = $this->factory->getPlan(
            $definition,
            Money::GBP(90),
            $params
        );

        /**
         * The net booking cost is 90, which includes a 10 deduction for a bursary. Without the bursary, we'd expect
         * the 100 to be split as 20/40/40. With the bursary_off_final_payment modifier, this becomes 20/40/30
         */
        $this->assertEquals(new PaymentPlan([
                PlannedPayment::immediate(Money::GBP(20)),
                PlannedPayment::withDueDate(Money::GBP(40), DueDate::fromString('2014-01-01')),
                PlannedPayment::withDueDate(Money::GBP(30), DueDate::fromString('2014-03-01'))
            ],
            '20/40/40 split',
            '20% immediately, 40% on 2014/01/01, 40% on 2014/03/01, except that bursaries are always taken from the final instalment'
        ), $plan);
    }
}
