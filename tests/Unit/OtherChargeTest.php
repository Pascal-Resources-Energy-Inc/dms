<?php

namespace Tests\Unit;

use App\OtherCharge;
use Tests\TestCase;

class OtherChargeTest extends TestCase
{
    /** @test */
    public function it_formats_charge_and_discount_amounts_for_fixed_and_percentage_types()
    {
        $fixedCharge = new OtherCharge(['type' => 'charge', 'charge_type' => 'fixed', 'amount' => 125.5]);
        $percentageCharge = new OtherCharge(['type' => 'charge', 'charge_type' => 'percentage', 'amount' => 5]);
        $fixedDiscount = new OtherCharge(['type' => 'discount', 'charge_type' => 'fixed', 'amount' => -75]);
        $percentageDiscount = new OtherCharge(['type' => 'discount', 'charge_type' => 'percentage', 'amount' => -10]);

        $this->assertSame('PHP 125.50', $fixedCharge->formattedAmount());
        $this->assertSame('5%', $percentageCharge->formattedAmount());
        $this->assertSame('-PHP 75.00', $fixedDiscount->formattedAmount());
        $this->assertSame('-10%', $percentageDiscount->formattedAmount());
        $this->assertSame('Charge', $percentageCharge->typeLabel());
        $this->assertSame('Percentage', $percentageCharge->chargeTypeLabel());
    }
}
