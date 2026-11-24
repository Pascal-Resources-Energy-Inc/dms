<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DistributorDashboardTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $sqlite = ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];
        config(['audit.enabled' => false, 'app.key' => 'base64:' . base64_encode(str_repeat('t', 32)),
            'database.default' => 'distributor_dashboard_test',
            'database.connections.distributor_dashboard_test' => $sqlite,
            'database.connections.admin_crms' => $sqlite,
            'database.connections.admin_crms2' => $sqlite,
        ]);
        foreach (['distributor_dashboard_test', 'admin_crms', 'admin_crms2'] as $connection) DB::purge($connection);
        foreach (['YEAR' => 'Y', 'MONTH' => 'n', 'MONTHNAME' => 'F', 'DAY' => 'j'] as $function => $format) {
            DB::connection()->getPdo()->sqliteCreateFunction($function, function ($value) use ($format) {
                return $value ? date($format, strtotime($value)) : null;
            });
        }
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT, name TEXT, type TEXT)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, store_code TEXT)');
        DB::statement('CREATE TABLE provincial_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, store_code TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, project_type TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributor_areas (id INTEGER PRIMARY KEY, provincial_distributor_id INTEGER,
            area_name TEXT, project_type TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, ad_user_id INTEGER, product_name TEXT, status TEXT, price NUMERIC)');
        DB::statement('CREATE TABLE vouchers (id INTEGER PRIMARY KEY, name TEXT, used_count INTEGER, deleted_at TEXT)');
        DB::statement('CREATE TABLE dealers (id INTEGER PRIMARY KEY, user_id INTEGER, area TEXT, name TEXT,
            latitude NUMERIC, longitude NUMERIC, email TEXT, status TEXT, location_region TEXT)');
        DB::statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, user_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE transactions (id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, client_id INTEGER, dealer_id INTEGER,
            qty NUMERIC, price NUMERIC, points_dealer NUMERIC, points_client NUMERIC, date TEXT,
            client_address TEXT, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE inventory_transfers (id INTEGER PRIMARY KEY, ad_id INTEGER, ad_user_id INTEGER,
            item_name TEXT, sku TEXT, qty NUMERIC, movement_type TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE order_details (id INTEGER PRIMARY KEY, ad_id INTEGER, dealer_id INTEGER,
            item TEXT, qty NUMERIC, price NUMERIC, status TEXT, date TEXT, created_at TEXT)');

        $this->viewRoot = storage_path('framework/testing/distributor-dashboard-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', '<html><body>@yield("content")</body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function signIn($role)
    {
        DB::table('users')->insert(['id' => 228, 'role' => $role, 'name' => 'Dashboard Distributor', 'type' => '[]']);
        $this->actingAs(User::findOrFail(228));
    }

    private function pdProfile()
    {
        DB::table('provincial_distributors')->insert(['id' => 7, 'user_id' => 228, 'store_code' => 'PD-2026-0007']);
        DB::table('provincial_distributor_areas')->insert([
            ['provincial_distributor_id' => 7, 'area_name' => 'PD Area', 'project_type' => 'Regular', 'deleted_at' => null],
            ['provincial_distributor_id' => 7, 'area_name' => 'PD Area', 'project_type' => 'Project Rise', 'deleted_at' => null],
            ['provincial_distributor_id' => 7, 'area_name' => 'Disengaged Area', 'project_type' => 'Regular', 'deleted_at' => '2026-01-01'],
        ]);
    }

    private function adProfile($id = 8, $userId = 228, $area = 'AD Area')
    {
        DB::table('area_distributors')->insert(['id' => $id, 'user_id' => $userId, 'store_code' => 'AD-2026-0008']);
        DB::table('ad_areas')->insert(['ad_id' => $id, 'area_name' => $area, 'project_type' => 'Regular']);
    }

    private function movement($owner, $adId, $type, $qty, $item = '330g LPG Refill')
    {
        DB::table('inventory_transfers')->insert(['ad_user_id' => $owner, 'ad_id' => $adId,
            'item_name' => $item, 'sku' => 'REFILL', 'movement_type' => $type, 'qty' => $qty]);
    }

    public function test_pd_without_legacy_ad_profile_loads_its_areas_vouchers_and_own_stock()
    {
        $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR);
        $this->pdProfile();
        // Profile IDs in these separate tables can coincide.
        $this->adProfile(7, 999, 'Other Distributor Area');
        DB::table('order_details')->insert(['ad_id' => 7, 'item' => 'Other distributor product',
            'status' => 'Completed', 'qty' => 99, 'price' => 100, 'date' => now()->toDateString()]);
        DB::table('vouchers')->insert([
            ['name' => 'PD-2026-0007', 'used_count' => 1],
            ['name' => 'PD-2026-0007', 'used_count' => 0],
            ['name' => 'AD-2026-0008', 'used_count' => 1],
        ]);
        $this->movement(228, null, 'in', 20);
        $this->movement(228, null, 'out', 3);
        $this->movement(999, null, 'in', 100, 'Another owner null AD');
        $this->movement(999, 7, 'out', 50, 'Another owner overlapping ID');

        $response = $this->get('/ad-dashboard');
        $response->assertOk()->assertViewIs('area_distributor.home')->assertViewHas('managedAreas', ['PD Area']);
        $response->assertViewHas('usedVoucherCount', 1)->assertViewHas('unusedVoucherCount', 1);
        $this->assertCount(0, $response->viewData('orders'));
        $stock = $response->viewData('stockLevels');
        $this->assertCount(1, $stock);
        $this->assertSame(17.0, $stock->first()->remaining_qty);
        $response->assertSee('PD Area')->assertDontSee('Other distributor product');
    }

    public function test_ad_dashboard_keeps_its_orders_and_inventory()
    {
        $this->signIn(User::ROLE_AREA_DISTRIBUTOR);
        $this->adProfile();
        DB::table('order_details')->insert(['ad_id' => 8, 'item' => '330g LPG Refill',
            'status' => 'Completed', 'qty' => 4, 'price' => 50, 'date' => now()->toDateString()]);
        $this->movement(228, 8, 'in', 20);
        $this->movement(228, 8, 'out', 3);
        $this->movement(999, 9, 'in', 500, 'Another owner');

        $response = $this->get('/ad-dashboard');
        $response->assertOk()->assertViewHas('managedAreas', ['AD Area']);
        $this->assertCount(1, $response->viewData('orders'));
        $this->assertEquals(4, $response->viewData('totalProductsSoldQty'));
        $this->assertSame(13.0, $response->viewData('stockLevels')->first()->remaining_qty);
    }

    public function test_pd_prefers_its_current_areas_but_uses_its_own_legacy_id_for_sales()
    {
        $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR);
        $this->pdProfile();
        $this->adProfile(8, 228, 'Old PD Area');
        DB::table('order_details')->insert(['ad_id' => 8, 'item' => '330g LPG Refill',
            'status' => 'Completed', 'qty' => 4, 'price' => 50, 'date' => now()->toDateString()]);
        $response = $this->get('/ad-dashboard');
        $response->assertOk()->assertViewHas('managedAreas', ['PD Area']);
        $this->assertCount(1, $response->viewData('orders'));
        $this->assertSame(['regular', 'project_rise'], $response->viewData('orderTabs')->pluck('key')->all());
    }

    public function test_legacy_pd_without_a_separate_profile_uses_its_existing_areas()
    {
        $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR);
        $this->adProfile();
        $this->get('/ad-dashboard')->assertOk()->assertViewHas('managedAreas', ['AD Area']);
    }

    public function test_distributors_without_any_profile_have_an_empty_dashboard_without_other_owners_stock()
    {
        $this->movement(999, null, 'in', 30);
        $this->movement(999, null, 'out', 10);
        foreach ([User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR] as $role) {
            $user = new User(['name' => 'Missing Profile', 'role' => $role]);
            $user->id = 228;
            $this->actingAs($user);
            $response = $this->get('/ad-dashboard');
            $response->assertOk()->assertViewHas('managedAreas', [])->assertSee('No area assigned');
            $this->assertCount(0, $response->viewData('orders'));
            $this->assertCount(0, $response->viewData('stockLevels'));
        }
    }

    public function test_remote_pd_stock_uses_user_ownership_and_ignores_unscopable_legacy_rows()
    {
        $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR);
        $this->pdProfile();
        $this->movement(228, null, 'in', 20);
        DB::connection('admin_crms')->statement('CREATE TABLE inventory_transfers (ad_user_id INTEGER, item_name TEXT, qty NUMERIC, movement_type TEXT)');
        DB::connection('admin_crms')->table('inventory_transfers')->insert([
            ['ad_user_id' => 228, 'item_name' => '330g LPG Refill', 'qty' => 2, 'movement_type' => 'out'],
            ['ad_user_id' => 999, 'item_name' => 'Another owner', 'qty' => 50, 'movement_type' => 'out'],
        ]);
        DB::connection('admin_crms2')->statement('CREATE TABLE inventory_transfers (ad_id INTEGER, item_name TEXT, qty NUMERIC, movement_type TEXT)');
        DB::connection('admin_crms2')->table('inventory_transfers')->insert([
            'ad_id' => 7, 'item_name' => 'Wrong profile ID', 'qty' => 40, 'movement_type' => 'out',
        ]);

        $response = $this->get('/ad-dashboard');
        $response->assertOk();
        $this->assertCount(1, $response->viewData('stockLevels'));
        $this->assertSame(18.0, $response->viewData('stockLevels')->first()->remaining_qty);
    }
}
