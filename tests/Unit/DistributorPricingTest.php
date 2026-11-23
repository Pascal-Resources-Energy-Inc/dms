<?php

namespace Tests\Unit;

use App\AreaDistributor;
use App\Item;
use App\ProvincialDistributor;
use App\Support\DistributorPricing;
use App\User;
use Tests\TestCase;

class DistributorPricingTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false]);
    }

    private function buyer($role, $region, $legacyRegion = null)
    {
        $user = new User(['role' => $role]);
        $user->region = $legacyRegion;
        $profile = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? new ProvincialDistributor : new AreaDistributor;
        $profile->location_region = $region;
        $user->setRelation('ad', $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? null : $profile);
        $user->setRelation('provincialDistributor', $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? $profile : null);
        return $user;
    }

    public function regionCases()
    {
        return [
            ['Region VI (Western Visayas)', true], ['Region VII (Central Visayas)', true],
            ['Region VIII (Eastern Visayas)', true], ['Region IX (Zamboanga Peninsula)', true],
            ['Region X (Northern Mindanao)', true], ['Region XI (Davao Region)', true],
            ['Region XII (SOCCSKSARGEN)', true], ['Region XIII (Caraga)', true],
            ['Bangsamoro Autonomous Region in Muslim Mindanao', true], ['BARMM', true], ['ARMM', true],
            ['Negros Island Region (NIR)', true], ['NIR', true], ['SOCCSKSARGEN', true], ['Caraga', true],
            ['  region-vii  ', true], ['Region 6', true], ['Region 13', true], ['XII', true],
            ['Visayas', true], ['Mindanao', true],
            ['Region I (Ilocos Region)', false], ['Region II (Cagayan Valley)', false],
            ['Region III (Central Luzon)', false], ['Region IV-A (CALABARZON)', false],
            ['MIMAROPA Region', false], ['Region V (Bicol Region)', false],
            ['Cordillera Administrative Region (CAR)', false], ['National Capital Region (NCR)', false],
            ['', false], [null, false], ['Unknown region', false], ['130000000', false],
        ];
    }

    /** @dataProvider regionCases */
    public function test_saved_region_selects_the_right_price_for_both_distributor_roles($region, $expected)
    {
        foreach ([User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR] as $role) {
            $buyer = $this->buyer($role, $region);
            $this->assertSame($expected, DistributorPricing::usesVisminPrice($buyer));
            $product = new Item(['dprice' => 60, 'price' => 100, 'vismin_price' => 110.25, 'vismin_dealer_price' => 95.35, 'vismin_md_price' => 85.45, 'vismin_dprice' => 72.35]);
            $this->assertSame($expected ? 72.35 : 60.0, $product->distributorPurchaseOrderPrice($buyer));
            $product->for_ad = true;
            $this->assertSame($expected ? 110.25 : 100.0, $product->distributorPurchaseOrderPrice($buyer));
        }
    }

    public function test_only_distributor_roles_receive_the_regional_price()
    {
        $product = new Item(['dprice' => 60, 'vismin_dprice' => 72.35]);
        foreach (['Admin', 'Dealer', 'Mega Dealer', 'MFI', 'Center Chief'] as $role) {
            $this->assertSame(60.0, $product->distributorPurchaseOrderPrice($this->buyer($role, 'Region VII')));
        }
        $this->assertSame(60.0, $product->distributorPurchaseOrderPrice());
    }

    public function test_pd_profile_takes_priority_over_legacy_ad_and_user_locations()
    {
        $buyer = $this->buyer(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VII', 'Region III');
        $buyer->setRelation('ad', new AreaDistributor(['location_region' => 'Region III']));
        $this->assertTrue(DistributorPricing::usesVisminPrice($buyer));
        $buyer->provincialDistributor->location_region = 'Region III';
        $buyer->ad->location_region = 'Region VII';
        $buyer->region = 'Region VII';
        $this->assertFalse(DistributorPricing::usesVisminPrice($buyer));
        $buyer->setRelation('provincialDistributor', null);
        $this->assertTrue(DistributorPricing::usesVisminPrice($buyer));
    }

    public function test_legacy_saved_user_region_is_used_only_when_profile_region_is_empty()
    {
        $buyer = $this->buyer(User::ROLE_AREA_DISTRIBUTOR, '', 'Region X');
        $this->assertTrue(DistributorPricing::usesVisminPrice($buyer));
        $buyer->ad->location_region = 'Region V';
        $this->assertFalse(DistributorPricing::usesVisminPrice($buyer));
    }

    public function test_unset_price_uses_existing_rate_but_zero_stays_zero()
    {
        $buyer = $this->buyer(User::ROLE_AREA_DISTRIBUTOR, 'Region VII');
        // A configured price in another tier must not replace the missing target tier.
        $regular = new Item(['for_ad' => false, 'dprice' => 60, 'price' => 100, 'vismin_price' => 110, 'vismin_dprice' => null]);
        $collateral = new Item(['for_ad' => true, 'dprice' => 0, 'price' => 175.50, 'vismin_price' => null, 'vismin_dprice' => 80]);
        $this->assertFalse($regular->usesVisminPriceFor($buyer));
        $this->assertFalse($collateral->usesVisminPriceFor($buyer));
        $this->assertSame(60.0, $regular->distributorPurchaseOrderPrice($buyer));
        $this->assertSame(175.5, $collateral->distributorPurchaseOrderPrice($buyer));
        $regular->vismin_dprice = 0; $collateral->vismin_price = 0;
        $this->assertTrue($regular->usesVisminPriceFor($buyer));
        $this->assertTrue($collateral->usesVisminPriceFor($buyer));
        $this->assertSame(0.0, $regular->distributorPurchaseOrderPrice($buyer));
        $this->assertSame(0.0, $collateral->distributorPurchaseOrderPrice($buyer));
    }
}
