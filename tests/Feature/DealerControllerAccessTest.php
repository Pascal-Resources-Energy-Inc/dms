<?php

namespace Tests\Feature;

use App\AreaDistributor;
use App\Dealer;
use App\Http\Controllers\DealerController;
use App\User;
use Tests\TestCase;

class DealerControllerAccessTest extends TestCase
{
    public function test_area_distributor_can_manage_regular_dealer_in_assigned_area()
    {
        $dealer = new Dealer([
            'dealer_type' => 'Regular',
            'area' => 'Taytay',
        ]);

        $user = new User(['role' => 'Area Distributor']);
        $ad = new AreaDistributor(['id' => 1]);
        $ad->setRelation('areas', collect([
            (object) ['area_name' => 'Taytay'],
        ]));
        $user->setRelation('ad', $ad);

        $controller = new DealerController();

        $this->assertTrue($controller->canManageDealerForAuthUser($dealer, $user));
    }

    public function test_area_distributor_cannot_manage_non_regular_dealer()
    {
        $dealer = new Dealer([
            'dealer_type' => 'Project',
            'area' => 'Taytay',
        ]);

        $user = new User(['role' => 'Area Distributor']);
        $ad = new AreaDistributor(['id' => 2]);
        $ad->setRelation('areas', collect([
            (object) ['area_name' => 'Taytay'],
        ]));
        $user->setRelation('ad', $ad);

        $controller = new DealerController();

        $this->assertFalse($controller->canManageDealerForAuthUser($dealer, $user));
    }
}
