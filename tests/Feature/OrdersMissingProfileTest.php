<?php

namespace Tests\Feature;

use App\Http\Controllers\OrderController;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrdersMissingProfileTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'orders_profile_test']);
        foreach (['orders_profile_test', 'admin_crms', 'admin_crms2'] as $connection) {
            config(['database.connections.' . $connection => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ]]);
            DB::purge($connection);
        }
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT)');
        DB::statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, status TEXT, serial_number INTEGER)');
        DB::statement('CREATE TABLE stoves (id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, deleted_at TEXT)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, ad_user_id INTEGER, status TEXT, product_name TEXT)');
        DB::statement('CREATE TABLE order_details (id INTEGER PRIMARY KEY, ad_id INTEGER, dealer_id INTEGER,
            status TEXT, price NUMERIC, qty NUMERIC, delivery_fee NUMERIC)');
        foreach (['dealers', 'mega_dealers'] as $table) {
            DB::statement("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT, area TEXT, status TEXT)");
            DB::table($table)->insert([
                ['id' => 1, 'name' => 'North seller', 'area' => 'North', 'status' => 'Active'],
                ['id' => 2, 'name' => 'South seller', 'area' => 'South', 'status' => 'Active'],
                ['id' => 3, 'name' => 'Inactive seller', 'area' => 'North', 'status' => 'Inactive'],
            ]);
        }
        DB::table('users')->insert(['id' => 1, 'name' => 'Test user', 'role' => 'Admin']);
        DB::table('order_details')->insert([
            ['id' => 1, 'ad_id' => 7, 'status' => 'Pending', 'price' => 70, 'qty' => 10],
            ['id' => 2, 'ad_id' => 8, 'status' => 'Completed', 'price' => 80, 'qty' => 2],
        ]);
    }

    private function indexData($role)
    {
        DB::table('users')->where('id', 1)->update(['role' => $role]);
        $this->actingAs(User::findOrFail(1));
        $view = (new OrderController)->index();
        $this->assertSame('orders.index', $view->name());
        return $view->getData();
    }

    public function test_admin_without_distributor_profile_can_load_orders()
    {
        $data = $this->indexData('Admin');
        $this->assertCount(4, $data['dealers']);
        $this->assertCount(2, $data['orders']);
        $this->assertEquals(1, $data['pendingOrdersCount']);
        $this->assertEquals(160, $data['totalCompletedSales']);
    }

    public function test_distributor_without_profile_does_not_crash_or_receive_other_distributors_records()
    {
        $data = $this->indexData('Area Distributor');
        $this->assertCount(0, $data['dealers']);
        $this->assertCount(0, $data['orders']);
        $this->assertEquals(0, $data['pendingOrdersCount']);
    }

    public function test_distributor_still_sees_only_active_sellers_in_awarded_areas()
    {
        DB::table('area_distributors')->insert(['id' => 7, 'user_id' => 1]);
        DB::table('ad_areas')->insert([
            ['id' => 1, 'ad_id' => 7, 'area_name' => 'North', 'deleted_at' => null],
            ['id' => 2, 'ad_id' => 7, 'area_name' => 'South', 'deleted_at' => '2026-11-01'],
        ]);
        $data = $this->indexData('Area Distributor');
        $this->assertCount(2, $data['dealers']);
        $this->assertSame(['North'], $data['dealers']->pluck('area')->unique()->values()->all());
        $this->assertEquals([1], $data['orders']->pluck('id')->all());
    }

    public function test_distributor_profile_without_awarded_areas_loads_an_empty_seller_list()
    {
        DB::table('area_distributors')->insert(['id' => 7, 'user_id' => 1]);
        $data = $this->indexData('Area Distributor');
        $this->assertCount(0, $data['dealers']);
    }
}
