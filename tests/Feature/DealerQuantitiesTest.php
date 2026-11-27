<?php

namespace Tests\Feature;

use App\AreaAd;
use App\AreaDistributor;
use App\Http\Controllers\AreaDistributorController;
use App\Http\Controllers\DealerController;
use App\ProvincialDistributor;
use App\ProvincialDistributorArea;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DealerQuantitiesTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'dealer_quantity_test']);
        foreach (['dealer_quantity_test', 'admin_crms', 'admin_crms2'] as $connection) {
            config(['database.connections.'.$connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
            DB::purge($connection);
            $db = DB::connection($connection);
            $db->statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT)');
            $db->statement('CREATE TABLE dealers (id INTEGER PRIMARY KEY, user_id INTEGER, dealer_reference TEXT,
                name TEXT, dealer_type TEXT, area TEXT, status TEXT, deleted_at TEXT)');
            $quantity = $connection === 'admin_crms2' ? 'quantity' : 'qty';
            $db->statement("CREATE TABLE order_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, user_id INTEGER,
                mega_dealer_id INTEGER, item TEXT, {$quantity} NUMERIC, status TEXT, deleted_at TEXT, ad_id INTEGER)");
            $db->statement("CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, user_id INTEGER,
                mega_dealer_id INTEGER, item TEXT, {$quantity} NUMERIC, status TEXT, deleted_at TEXT)");
            $db->table('users')->insert([['id' => 10, 'name' => 'First seller', 'role' => 'Dealer'],
                ['id' => 20, 'name' => 'Second seller', 'role' => 'Dealer']]);
            $db->table('dealers')->insert([
                ['id' => 1, 'user_id' => 10, 'dealer_reference' => 'DL-001', 'name' => 'North Dealer', 'dealer_type' => 'Regular', 'area' => 'North', 'status' => 'Active'],
                ['id' => 10, 'user_id' => 20, 'dealer_reference' => 'DL-010', 'name' => 'Other North Dealer', 'dealer_type' => 'Regular', 'area' => 'North', 'status' => 'Active'],
                ['id' => 3, 'user_id' => null, 'dealer_reference' => 'DL-003', 'name' => 'Unlinked Dealer', 'dealer_type' => 'Regular', 'area' => 'North', 'status' => 'Active'],
                ['id' => 4, 'user_id' => 40, 'dealer_reference' => 'DL-004', 'name' => 'Outside Dealer', 'dealer_type' => 'Regular', 'area' => 'South', 'status' => 'Active'],
                ['id' => 5, 'user_id' => 50, 'dealer_reference' => 'DL-005', 'name' => 'Disengaged Dealer', 'dealer_type' => 'Regular', 'area' => 'Old area', 'status' => 'Active'],
            ]);
            foreach ([[10, 999, null, 50, 'Completed', null], [10, 999, 2, 10, 'Completed', null],
                [10, 999, null, 7, 'Pending', null], [10, 999, null, 100, 'Cancelled', null],
                [10, 999, null, 150, 'Completed', '2026-11-01'], [1, 10, null, 800, 'Completed', null],
                [20, 10, 1, 8, 'Completed', null]] as $row) {
                $db->table('order_details')->insert(['dealer_id' => $row[0], 'user_id' => $row[1], 'mega_dealer_id' => $row[2],
                    'item' => '330g LPG Refill', $quantity => $row[3], 'status' => $row[4], 'deleted_at' => $row[5], 'ad_id' => 700]);
            }
            foreach ([[10, 999, null, 12, 'Completed', null], [10, 999, null, 2, null, null],
                [10, 999, null, 9, 'Pending', null], [10, 999, null, 11, 'Cancelled', null],
                [10, 999, null, 100, 'Completed', '2026-11-01'], [1, 10, null, 500, 'Completed', null],
                [20, 10, 1, 5, 'Completed', null], [3, 10, null, 10, 'Completed', null]] as $row) {
                $db->table('transaction_details')->insert(['dealer_id' => $row[0], 'user_id' => $row[1], 'mega_dealer_id' => $row[2],
                    'item' => '330g LPG Refill', $quantity => $row[3], 'status' => $row[4], 'deleted_at' => $row[5]]);
            }
        }
        DB::statement('CREATE TABLE centers (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE areas (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, product_name TEXT, ad_user_id INTEGER, status TEXT)');
        $this->actingAs(new User(['role' => 'Admin', 'name' => 'QA Admin']));
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function listing()
    {
        return auth()->user()->role === 'Admin'
            ? (new DealerController)->index(Request::create('/dealers'))
            : (new AreaDistributorController)->myDealer(Request::create('/dealer-ads'));
    }

    private function signInDistributor($role)
    {
        $user = new User(['role' => $role, 'name' => 'QA Distributor']);
        $user->id = 1000;
        foreach (['ad' => AreaDistributor::class, 'provincialDistributor' => ProvincialDistributor::class] as $relation => $profileClass) {
            $profile = new $profileClass; $profile->id = $relation === 'ad' ? 700 : 800;
            $isOwnProfile = ($role === 'Area Distributor') === ($relation === 'ad');
            $award = $relation === 'ad' ? new AreaAd : new ProvincialDistributorArea;
            $award->area_name = $isOwnProfile ? 'North' : 'South';
            $award->setRelation('distributor', $profile);
            $profile->setRelation('areas', new Collection([$award]));
            $user->setRelation($relation, $profile);
        }
        $this->actingAs($user);
    }

    public function test_admin_quantities_match_the_seller_account_and_exclude_incomplete_and_deleted_records()
    {
        $rows = $this->listing()->getData()['dealers'];
        foreach (['Regular', 'admin_crms', 'admin_crms2'] as $source) {
            $sourceRows = $rows->where('source', $source);
            $first = $sourceRows->firstWhere('id', 1);
            $this->assertEquals(60, $first->stock_qty);
            $this->assertEquals(14, $first->sold_qty);
            $this->assertEquals(46, $first->remaining_qty);
            $other = $sourceRows->firstWhere('id', 10);
            $this->assertEquals(8, $other->stock_qty);
            $this->assertEquals(5, $other->sold_qty);
            $this->assertEquals(3, $other->remaining_qty);
            $unlinked = $sourceRows->firstWhere('id', 3);
            $this->assertEquals(0, $unlinked->stock_qty);
            $this->assertEquals(0, $unlinked->sold_qty);
            $this->assertEquals(0, $unlinked->remaining_qty);
        }
    }

    public function distributorRoles()
    {
        return [['Area Distributor'], ['Provincial Distributor']];
    }

    /** @dataProvider distributorRoles */
    public function test_distributor_quantities_match_admin_totals_only_within_its_own_awarded_areas($role)
    {
        $this->signInDistributor($role);
        $rows = $this->listing()->getData()['dealers'];
        $this->assertCount(9, $rows);
        $this->assertSame(['North'], $rows->pluck('area')->unique()->values()->all());
        $firstDealerRows = $rows->where('id', 1);
        $this->assertCount(3, $firstDealerRows);
        foreach ($firstDealerRows as $row) {
            $this->assertEquals(60, $row->stock_qty);
            $this->assertEquals(14, $row->sold_qty);
            $this->assertEquals(46, $row->remaining_qty);
        }
    }

    public function test_legacy_posted_tables_without_status_and_quantity_column_are_supported()
    {
        $db = DB::connection('admin_crms2');
        $db->statement('DROP TABLE order_details');
        $db->statement('DROP TABLE transaction_details');
        $db->statement('CREATE TABLE order_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, quantity NUMERIC)');
        $db->statement('CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, quantity NUMERIC)');
        $db->table('order_details')->insert(['dealer_id' => 10, 'quantity' => 12]);
        $db->table('transaction_details')->insert(['dealer_id' => 10, 'quantity' => 3]);
        $row = $this->listing()->getData()['dealers']->where('source', 'admin_crms2')->firstWhere('id', 1);
        $this->assertEquals(12, $row->stock_qty);
        $this->assertEquals(3, $row->sold_qty);
        $this->assertEquals(9, $row->remaining_qty);
    }

    public function test_missing_project_stock_history_and_negative_remaining_quantities_do_not_hide_dealers()
    {
        DB::connection('admin_crms')->table('order_details')->where('dealer_id', 10)->update(['status' => 'Pending']);
        DB::connection('admin_crms2')->statement('DROP TABLE order_details');
        $rows = $this->listing()->getData()['dealers'];
        $this->assertEquals(-14, $rows->where('source', 'admin_crms')->firstWhere('id', 1)->remaining_qty);
        $this->assertEquals(-14, $rows->where('source', 'admin_crms2')->firstWhere('id', 1)->remaining_qty);
    }

    public function test_rendered_dealer_rows_include_remaining_stock_and_all_project_tabs()
    {
        $this->viewRoot = storage_path('framework/testing/dealer-quantities-'.uniqid());
        File::makeDirectory($this->viewRoot.'/layouts', 0755, true);
        File::put($this->viewRoot.'/layouts/header.blade.php', "@yield('content') @yield('javascript')");
        $this->app['view']->getFinder()->prependLocation($this->viewRoot);
        $this->app['events']->forget('composing: layouts.header');
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag);
        $html = $this->listing()->render();
        foreach (['Regular', 'admin_crms', 'admin_crms2'] as $source) $this->assertContains('data-dealer-tab="'.$source.'"', $html);
        $this->assertContains('Remaining Stock</th>', $html);
        $this->assertSame(15, substr_count($html, 'data-label="Remaining stock"'));
        $this->assertContains('is-remaining">46</span>', $html);
    }
}
