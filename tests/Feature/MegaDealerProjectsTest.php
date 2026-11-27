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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MegaDealerProjectsTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'mega_test']);
        foreach (['mega_test', 'admin_crms', 'admin_crms2'] as $connection) {
            config(['database.connections.' . $connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
            DB::purge($connection);
            $db = DB::connection($connection);
            $table = $connection === 'admin_crms2' ? 'dealers' : 'mega_dealers';
            $db->statement('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT)');
            $db->statement("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, user_id INTEGER,
                dealer_reference TEXT, name TEXT, store_name TEXT, store_type TEXT, number TEXT,
                address TEXT, area TEXT, status TEXT, dealer_type TEXT, deleted_at TEXT)");
            $db->statement('CREATE TABLE order_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, qty NUMERIC, status TEXT)');
            $db->statement('CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, dealer_id INTEGER, qty NUMERIC)');
            $db->table('users')->insert([
                ['id' => 10, 'role' => 'Mega Dealer'], ['id' => 20, 'role' => 'Mega Dealer'],
                ['id' => 30, 'role' => 'Dealer'], ['id' => 40, 'role' => 'Mega Dealer'],
            ]);
            $db->table($table)->insert([
                ['id' => 1, 'user_id' => 10, 'dealer_reference' => 'MD20260001', 'name' => 'North Mega', 'area' => 'North', 'status' => 'Active', 'dealer_type' => 'Regular'],
                ['id' => 2, 'user_id' => 20, 'dealer_reference' => 'MD20260002', 'name' => 'South Mega', 'area' => 'South', 'status' => 'Inactive', 'dealer_type' => 'Regular'],
            ]);
            $db->table('order_details')->insert([
                ['dealer_id' => 10, 'qty' => 5, 'status' => 'Completed'],
                ['dealer_id' => 10, 'qty' => 7, 'status' => 'Pending'],
            ]);
            $db->table('transaction_details')->insert([
                ['id' => 1, 'dealer_id' => 10, 'qty' => 3],
                ['id' => 2, 'dealer_id' => 1, 'qty' => 99],
            ]);
            if ($connection !== 'mega_test') {
                $db->table($table)->insert(['id' => 4, 'user_id' => 40, 'dealer_reference' => 'MD20260004', 'name' => 'Deleted Mega', 'area' => 'North', 'status' => 'Active', 'deleted_at' => '2026-11-01']);
            }
            if ($connection === 'admin_crms2') {
                $db->table($table)->insert(['id' => 3, 'user_id' => 30, 'dealer_reference' => 'DL20260003', 'name' => 'Ordinary Dealer', 'area' => 'North', 'status' => 'Active']);
            }
        }
        DB::statement('CREATE TABLE centers (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, ad_user_id INTEGER, product_name TEXT, status TEXT)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT)');
        DB::statement('CREATE TABLE areas (id INTEGER PRIMARY KEY, name TEXT)');
        $this->signInDistributor();
    }

    protected function tearDown()
    {
        if ($this->viewRoot) {
            File::deleteDirectory($this->viewRoot);
        }
        parent::tearDown();
    }

    private function signInDistributor($role = 'Area Distributor', $area = 'North')
    {
        $user = new User(['role' => $role]);
        $user->id = 1000;
        $pd = $role === 'Provincial Distributor';
        $profile = $pd ? new ProvincialDistributor : new AreaDistributor;
        $award = $pd ? new ProvincialDistributorArea(['area_name' => $area]) : new AreaAd(['area_name' => $area]);
        $award->setRelation('distributor', $profile);
        $profile->setRelation('areas', new Collection($area ? [$award] : []));
        $user->setRelation($pd ? 'provincialDistributor' : 'ad', $profile);
        $this->actingAs($user);
    }

    private function listing()
    {
        return (new AreaDistributorController)->myMegaDealer(Request::create('/md-ads'));
    }

    private function data($source, array $extra = [])
    {
        $query = array_replace_recursive([
            'source' => $source, 'draw' => 1, 'start' => 0, 'length' => 10,
            'columns' => [['data' => 'name', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']]],
            'order' => [['column' => 0, 'dir' => 'asc']], 'search' => ['value' => '', 'regex' => 'false'],
        ], $extra);
        $request = Request::create('/mds/data', 'GET', $query);
        $this->app->instance('request', $request);
        $this->app->instance('datatables.request', new \Yajra\DataTables\Utilities\Request);
        $json = (new DealerController)->megaDealersData($request)->getData(true);
        $this->assertArrayNotHasKey('error', $json);
        return $json;
    }

    public function test_distributor_page_has_three_project_tabs_and_scoped_counts()
    {
        $data = $this->listing()->getData();
        $this->assertSame(['Regular', 'Project Rise', 'Project Genesis'], $data['megaDealerTabs']->pluck('label')->all());
        $this->assertEquals([1, 1, 1], $data['megaDealerTabs']->pluck('count')->all());
        $this->assertSame(3, $data['totalDealers']);
        $this->assertTrue($data['dealerServerSide']);
        $this->assertSame(route('mds.data'), $data['dealerDataUrl']);
        $this->assertSame('md-ads', $data['dealerRouteName']);
    }

    public function sources()
    {
        return [['regular'], ['admin_crms'], ['admin_crms2']];
    }

    private function sourceDatabase($source)
    {
        return DB::connection($source === 'regular' ? 'mega_test' : $source);
    }

    /** @dataProvider sources */
    public function test_stock_sold_and_remaining_use_completed_active_records_for_admin_and_distributor($source)
    {
        $db = $this->sourceDatabase($source);
        $db->statement('ALTER TABLE order_details ADD COLUMN deleted_at TEXT');
        $db->statement('ALTER TABLE transaction_details ADD COLUMN deleted_at TEXT');
        $db->statement('ALTER TABLE transaction_details ADD COLUMN status TEXT');
        $db->table('order_details')->insert([
            ['dealer_id' => 10, 'qty' => 40, 'status' => 'Cancelled', 'deleted_at' => null],
            ['dealer_id' => 10, 'qty' => 50, 'status' => 'Completed', 'deleted_at' => '2026-11-01'],
        ]);
        $db->table('transaction_details')->insert([
            ['dealer_id' => 10, 'qty' => 2, 'status' => 'Completed', 'deleted_at' => null],
            ['dealer_id' => 10, 'qty' => 8, 'status' => 'Pending', 'deleted_at' => null],
            ['dealer_id' => 10, 'qty' => 9, 'status' => 'Cancelled', 'deleted_at' => null],
            ['dealer_id' => 10, 'qty' => 10, 'status' => 'Completed', 'deleted_at' => '2026-11-01'],
        ]);
        foreach (['Area Distributor', 'Admin'] as $role) {
            $this->actingAs($role === 'Admin' ? new User(['role' => 'Admin']) : auth()->user());
            $row = $this->data($source)['data'][0];
            $this->assertContains('>5</span>', $row['stock_qty']);
            $this->assertContains('>5</span>', $row['sold_qty']);
            $this->assertContains('>0</span>', $row['remaining_qty']);
        }
    }

    public function dedicatedSources()
    {
        return [['regular'], ['admin_crms']];
    }

    /** @dataProvider dedicatedSources */
    public function test_supplier_order_link_counts_as_sold_and_buyer_user_link_counts_as_stock($source)
    {
        $db = $this->sourceDatabase($source);
        $db->statement('ALTER TABLE order_details ADD COLUMN mega_dealer_id INTEGER');
        $db->statement('ALTER TABLE order_details ADD COLUMN mega_dealer_source TEXT');
        $db->statement('ALTER TABLE order_details ADD COLUMN deleted_at TEXT');
        $origin = $db->getName();
        $db->table('order_details')->insert([
            ['dealer_id' => 30, 'mega_dealer_id' => 1, 'mega_dealer_source' => null, 'qty' => 2, 'status' => 'Completed', 'deleted_at' => null],
            ['dealer_id' => 40, 'mega_dealer_id' => 1, 'mega_dealer_source' => $origin, 'qty' => 4, 'status' => 'Completed', 'deleted_at' => null],
            ['dealer_id' => 40, 'mega_dealer_id' => 1, 'mega_dealer_source' => 'another_database', 'qty' => 100, 'status' => 'Completed', 'deleted_at' => null],
            ['dealer_id' => 10, 'mega_dealer_id' => 2, 'mega_dealer_source' => $origin, 'qty' => 7, 'status' => 'Completed', 'deleted_at' => null],
            ['dealer_id' => 20, 'mega_dealer_id' => 1, 'mega_dealer_source' => null, 'qty' => 9, 'status' => 'Pending', 'deleted_at' => null],
            ['dealer_id' => 20, 'mega_dealer_id' => 1, 'mega_dealer_source' => null, 'qty' => 50, 'status' => 'Completed', 'deleted_at' => '2026-11-01'],
        ]);
        $this->actingAs(new User(['role' => 'Admin']));
        $row = $this->data($source)['data'][0];
        $this->assertContains('>12</span>', $row['stock_qty']);
        $this->assertContains('>9</span>', $row['sold_qty']);
        $this->assertContains('>3</span>', $row['remaining_qty']);
    }

    public function test_explicit_and_legacy_retail_seller_links_do_not_mix_profile_and_user_ids()
    {
        $db = $this->sourceDatabase('admin_crms');
        $db->statement('ALTER TABLE transaction_details ADD COLUMN mega_dealer_id INTEGER');
        $db->statement('ALTER TABLE transaction_details ADD COLUMN mega_dealer_source TEXT');
        $db->table('transaction_details')->insert([
            ['dealer_id' => 999, 'mega_dealer_id' => 1, 'mega_dealer_source' => 'admin_crms', 'qty' => 2],
            ['dealer_id' => 10, 'mega_dealer_id' => 2, 'mega_dealer_source' => null, 'qty' => 9],
            ['dealer_id' => 10, 'mega_dealer_id' => 1, 'mega_dealer_source' => 'another_database', 'qty' => 500],
        ]);
        $row = $this->data('admin_crms')['data'][0];
        $this->assertContains('>5</span>', $row['stock_qty']);
        $this->assertContains('>5</span>', $row['sold_qty']);
        $this->assertContains('>0</span>', $row['remaining_qty']);
    }

    /** @dataProvider sources */
    public function test_remaining_stock_can_be_sorted_and_paginated_on_the_server($source)
    {
        $this->actingAs(new User(['role' => 'Admin']));
        $row = $this->data($source, ['length' => 1, 'start' => 1,
            'columns' => [['data' => 'remaining_qty', 'name' => 'remaining_qty', 'searchable' => 'false']],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ])['data'][0];
        $this->assertContains('NORTH MEGA', $row['name']);
        $this->assertContains('>2</span>', $row['remaining_qty']);
    }

    public function test_remaining_stock_displays_a_deficit_without_hiding_missing_stock_history()
    {
        $db = $this->sourceDatabase('admin_crms');
        $db->table('order_details')->update(['status' => 'Pending']);
        $row = $this->data('admin_crms')['data'][0];
        $this->assertContains('>0</span>', $row['stock_qty']);
        $this->assertContains('>3</span>', $row['sold_qty']);
        $this->assertContains('is-deficit', $row['remaining_qty']);
        $this->assertContains('>-3</span>', $row['remaining_qty']);
    }

    /** @dataProvider sources */
    public function test_each_source_returns_only_mega_dealers_in_assigned_areas($source)
    {
        $json = $this->data($source);
        $this->assertEquals(1, $json['recordsTotal']);
        $this->assertCount(1, $json['data']);
        $this->assertContains('NORTH MEGA', $json['data'][0]['name']);
        $this->assertNotContains('SOUTH', json_encode($json));
        $this->assertNotContains('Ordinary Dealer', json_encode($json));
        $this->assertContains('>5</span>', $json['data'][0]['stock_qty']);
        $this->assertContains('>3</span>', $json['data'][0]['sold_qty']);
        $this->assertContains('>2</span>', $json['data'][0]['remaining_qty']);
    }

    public function test_search_and_pagination_work_for_project_records()
    {
        $this->actingAs(new User(['role' => 'Admin']));
        $json = $this->data('admin_crms', ['length' => 1, 'start' => 1]);
        $this->assertEquals(2, $json['recordsTotal']);
        $this->assertCount(1, $json['data']);
        $this->assertContains('SOUTH MEGA', $json['data'][0]['name']);
        $json = $this->data('admin_crms', ['search' => ['value' => 'North']]);
        $this->assertEquals(1, $json['recordsFiltered']);
        $this->assertContains('NORTH MEGA', $json['data'][0]['name']);
    }

    public function test_provincial_distributor_uses_its_own_awarded_areas()
    {
        $this->signInDistributor('Provincial Distributor', 'South');
        $this->assertEquals([1, 1, 1], $this->listing()->getData()['megaDealerTabs']->pluck('count')->all());
        foreach (['regular', 'admin_crms', 'admin_crms2'] as $source) {
            $json = $this->data($source);
            $this->assertContains('SOUTH MEGA', $json['data'][0]['name']);
        }
    }

    public function test_empty_awards_keep_all_three_tabs_without_exposing_records()
    {
        $this->signInDistributor('Area Distributor', null);
        $data = $this->listing()->getData();
        $this->assertEquals([0, 0, 0], $data['megaDealerTabs']->pluck('count')->all());
        foreach (['regular', 'admin_crms', 'admin_crms2'] as $source) {
            $this->assertCount(0, $this->data($source)['data']);
        }
    }

    public function test_distributor_can_view_its_project_mega_dealer_without_edit_access()
    {
        $data = (new DealerController)->viewAdminCrmMegaDealer(Request::create('/'), 'admin_crms', 1)->getData();
        $this->assertSame('North Mega', $data['dealer']->name);
        $this->assertFalse($data['canEditDealer']);
        $this->assertFalse($data['canDeleteMegaDealer']);
        $this->assertSame(route('md-ads'), $data['partnerListUrl']);
        $this->assertEquals(3, $data['transactionStats']['qty']);
        $this->assertSame(1, $data['transactionStats']['count']);
    }

    public function test_out_of_area_project_profile_is_not_accessible()
    {
        $this->expectException(HttpException::class);
        (new DealerController)->viewAdminCrmMegaDealer(Request::create('/'), 'admin_crms', 2);
    }

    public function test_unrelated_role_cannot_query_project_records()
    {
        $this->actingAs(new User(['role' => 'Dealer']));
        $this->expectException(HttpException::class);
        $this->data('admin_crms');
    }

    public function test_project_without_area_column_does_not_expose_unscoped_records()
    {
        $db = DB::connection('admin_crms');
        $db->getSchemaBuilder()->drop('mega_dealers');
        $db->statement('CREATE TABLE mega_dealers (id INTEGER PRIMARY KEY, dealer_reference TEXT, user_id INTEGER, name TEXT, status TEXT)');
        $db->table('mega_dealers')->insert(['id' => 1, 'dealer_reference' => 'MD20260001', 'user_id' => 10, 'name' => 'Unscoped Mega', 'status' => 'Active']);
        $this->assertCount(0, $this->data('admin_crms')['data']);
    }

    public function test_invalid_source_is_rejected()
    {
        $this->expectException(HttpException::class);
        $this->data('unknown');
    }

    public function test_rendered_page_keeps_tabs_even_when_empty()
    {
        $this->signInDistributor('Area Distributor', null);
        $this->viewRoot = storage_path('framework/testing/mega-projects-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', "@yield('content') @yield('javascript')");
        $this->app['view']->getFinder()->prependLocation($this->viewRoot);
        $this->app['events']->forget('composing: layouts.header');
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag);
        $html = $this->listing()->render();
        foreach (['regular', 'admin_crms', 'admin_crms2'] as $key) {
            $this->assertContains('data-dealer-tab="' . $key . '"', $html);
        }
        $this->assertContains('serverSide: true', $html);
        $this->assertContains('request.source = activeDealerType', $html);
        $this->assertContains('Remaining Stock</th>', $html);
        $this->assertContains("{data: 'remaining_qty'", $html);
    }
}
