<?php

namespace Tests\Feature;

use App\Http\Controllers\TransactionController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomerTransactionOverviewTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'overview_test']);
        foreach (['overview_test', 'admin_crms', 'admin_crms2'] as $connection) {
            config(['database.connections.' . $connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
            DB::purge($connection);
            $db = DB::connection($connection);
            $db->statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT)');
            foreach (['dealers', 'mega_dealers'] as $table) {
                $db->statement("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT,
                    dealer_type TEXT, area TEXT, center TEXT, deleted_at TEXT)");
            }
            $db->statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, name TEXT, status TEXT, center TEXT, serial_number INTEGER)');
            $db->statement('CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, dealer_id INTEGER,
                client_id INTEGER, item TEXT, qty NUMERIC, price NUMERIC, date TEXT, deleted_at TEXT)');
            $db->table('users')->insert([
                ['id' => 10, 'name' => 'Dealer User', 'role' => 'Dealer'],
                ['id' => 20, 'name' => 'Mega Dealer User', 'role' => 'Mega Dealer'],
                ['id' => 30, 'name' => 'Chief User', 'role' => 'Center Chief'],
            ]);
            // The dealer profile ID deliberately collides with the mega dealer user ID.
            $db->table('dealers')->insert(['id' => 20, 'user_id' => 10, 'name' => 'Test Dealer', 'dealer_type' => 'Regular', 'area' => 'North']);
            $db->table('mega_dealers')->insert(['id' => 1, 'user_id' => 20, 'name' => 'Test Mega Dealer', 'dealer_type' => 'Regular', 'area' => 'North']);
            $db->table('clients')->insert([
                ['id' => 101, 'name' => 'Customer One', 'status' => 'Active'],
                ['id' => 102, 'name' => 'Customer Two', 'status' => 'Active'],
            ]);
            $this->sale($connection, 1, 10, 101, '2026-11-09');
            $this->sale($connection, 2, 20, 102, '2026-11-10');
            if ($connection !== 'overview_test') {
                $db->statement('CREATE TABLE center_chiefs (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT, area TEXT, deleted_at TEXT)');
                $db->table('center_chiefs')->insert(['id' => 1, 'user_id' => 30, 'name' => 'Test Chief', 'area' => 'North']);
                $this->sale($connection, 3, 30, 101, '2026-11-10');
            }
        }
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, project_type TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributor_areas (id INTEGER PRIMARY KEY, provincial_distributor_id INTEGER, area_name TEXT, project_type TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE order_details (id INTEGER PRIMARY KEY, ad_id INTEGER, status TEXT)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE stoves (id INTEGER PRIMARY KEY)');
        DB::table('users')->insert(['id' => 1000, 'name' => 'Distributor', 'role' => 'Area Distributor']);
        DB::table('area_distributors')->insert(['id' => 1, 'user_id' => 1000]);
        DB::table('ad_areas')->insert(['id' => 1, 'ad_id' => 1, 'area_name' => 'North', 'project_type' => null]);
        $this->actingAs(User::findOrFail(1000));
    }

    protected function tearDown()
    {
        if ($this->viewRoot) {
            File::deleteDirectory($this->viewRoot);
        }
        parent::tearDown();
    }

    private function sale($connection, $id, $seller, $customer = 101, $date = '2026-11-10')
    {
        DB::connection($connection)->table('transaction_details')->insert([
            'id' => $id, 'dealer_id' => $seller, 'client_id' => $customer,
            'item' => '330g LPG Refill', 'qty' => 2, 'price' => 70, 'date' => $date,
        ]);
    }

    private function overview($project = 'Regular', array $filters = [])
    {
        $request = Request::create('/customer-ads', 'GET', $filters + ['project' => $project]);
        $this->app->instance('request', $request);
        return (new TransactionController)->customerAds($request);
    }

    public function projects()
    {
        return [['Regular', 'overview_test', 2], ['Project Rise', 'admin_crms', 3], ['Project Genesis', 'admin_crms2', 3]];
    }

    /** @dataProvider projects */
    public function test_dealer_and_mega_dealer_sales_are_visible_for_untagged_awarded_areas($project, $connection, $count)
    {
        $data = $this->overview($project)->getData();
        $this->assertCount($count, $data['transactions']);
        $rows = $data['transactions']->keyBy('dealer_id');
        $this->assertSame('Test Dealer', $rows[10]->dealer->name);
        $this->assertSame('Dealer', $rows[10]->dealer->seller_type);
        $this->assertSame('Test Mega Dealer', $rows[20]->dealer->name);
        $this->assertSame('Mega Dealer', $rows[20]->dealer->seller_type);
        $this->assertSame('Customer Two', $rows[20]->customer->name);
        $this->assertEquals(140, $rows[20]->qty * $rows[20]->price);
        $this->assertContains(20, $data['dealers']->pluck('user_id')->all());
        $this->assertContains(102, $data['customers']->pluck('id')->all());
        if ($count === 3) {
            $this->assertSame('Center Chief', $rows[30]->dealer->seller_type);
        }
    }

    /** @dataProvider projects */
    public function test_seller_customer_and_date_filters_work_without_profile_id_collisions($project, $connection, $count)
    {
        $data = $this->overview($project, [
            'dealer' => 20, 'customer' => 102, 'date_from' => '2026-11-10', 'date_to' => '2026-11-10',
        ])->getData();
        $this->assertEquals([2], $data['transactions']->pluck('id')->all());
        $this->assertSame('Test Mega Dealer', $data['transactions']->first()->dealer->name);
        $this->assertCount($count, $data['dealers']);
        $this->assertCount(0, $this->overview($project, ['dealer' => 20, 'customer' => 101])->getData()['transactions']);
        $this->assertCount(0, $this->overview($project, ['dealer' => 20, 'date_to' => '2026-11-09'])->getData()['transactions']);
    }

    public function test_project_scoping_excludes_other_distributors_other_project_tags_and_removed_areas()
    {
        DB::table('ad_areas')->insert([
            ['id' => 2, 'ad_id' => 2, 'area_name' => 'Outside', 'project_type' => null, 'deleted_at' => null],
            ['id' => 3, 'ad_id' => 1, 'area_name' => 'Genesis', 'project_type' => 'Project Genesis', 'deleted_at' => null],
            ['id' => 4, 'ad_id' => 1, 'area_name' => 'Removed', 'project_type' => null, 'deleted_at' => '2026-11-01'],
            ['id' => 5, 'ad_id' => 1, 'area_name' => 'Rise', 'project_type' => 'Project Rise', 'deleted_at' => null],
        ]);
        foreach (['Outside', 'Genesis', 'Removed', 'Rise'] as $index => $area) {
            DB::connection('admin_crms')->table('mega_dealers')->insert([
                'id' => 10 + $index, 'user_id' => 40 + $index, 'name' => $area . ' Seller', 'area' => $area,
            ]);
            $this->sale('admin_crms', 10 + $index, 40 + $index);
        }
        $this->assertEquals([1, 2, 3, 13], $this->overview('Project Rise')->getData()['transactions']->pluck('id')->sort()->values()->all());
        $this->assertCount(0, $this->overview('Project Rise', ['dealer' => 40])->getData()['transactions']);
        DB::table('ad_areas')->where('ad_id', 1)->delete();
        $this->assertCount(0, $this->overview('Project Rise')->getData()['transactions']);
    }

    public function test_legacy_mega_dealers_stored_in_dealers_are_labeled_and_not_duplicated()
    {
        $db = DB::connection('admin_crms');
        $db->table('dealers')->insert(['id' => 2, 'user_id' => 20, 'name' => 'Legacy Mega', 'area' => 'North']);
        $this->assertCount(3, $this->overview('Project Rise')->getData()['transactions']);
        $db->getSchemaBuilder()->drop('mega_dealers');
        $data = $this->overview('Project Rise')->getData();
        $this->assertCount(3, $data['transactions']);
        $this->assertSame('Mega Dealer', $data['transactions']->keyBy('dealer_id')[20]->dealer->seller_type);
    }

    public function test_deleted_remote_sellers_and_transactions_are_excluded()
    {
        DB::connection('admin_crms')->table('dealers')->where('user_id', 10)->update(['deleted_at' => '2026-11-01']);
        DB::connection('admin_crms')->table('transaction_details')->where('id', 2)->update(['deleted_at' => '2026-11-01']);
        $this->assertEquals([3], $this->overview('Project Rise')->getData()['transactions']->pluck('id')->all());
    }

    public function test_provincial_distributor_uses_its_own_awarded_areas()
    {
        DB::table('users')->where('id', 1000)->update(['role' => 'Provincial Distributor']);
        DB::table('area_distributors')->delete();
        DB::table('provincial_distributors')->insert(['id' => 5, 'user_id' => 1000]);
        DB::table('provincial_distributor_areas')->insert(['id' => 1, 'provincial_distributor_id' => 5, 'area_name' => 'North']);
        $this->actingAs(User::findOrFail(1000));
        $this->assertCount(2, $this->overview()->getData()['transactions']);
        $this->assertCount(3, $this->overview('Project Rise')->getData()['transactions']);
    }

    public function test_admin_can_view_legacy_crm_without_area_columns_but_distributors_cannot_bypass_area_scope()
    {
        $schema = DB::connection('admin_crms2')->getSchemaBuilder();
        $schema->drop('dealers');
        DB::connection('admin_crms2')->statement('CREATE TABLE dealers (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT)');
        DB::connection('admin_crms2')->table('dealers')->insert(['id' => 1, 'user_id' => 10, 'name' => 'Legacy Dealer']);
        $data = $this->overview('Project Genesis')->getData();
        $this->assertNotContains(10, $data['transactions']->pluck('dealer_id')->all());
        DB::table('users')->where('id', 1000)->update(['role' => 'Admin']);
        $this->actingAs(User::findOrFail(1000));
        $this->assertCount(3, $this->overview('Project Genesis')->getData()['transactions']);
    }

    public function test_rendered_overview_shows_mega_dealer_labels_and_resets_seller_when_switching_projects()
    {
        $this->viewRoot = storage_path('framework/testing/overview-views-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', "@yield('content')");
        $this->app['view']->getFinder()->prependLocation($this->viewRoot);
        $this->app['events']->forget('composing: layouts.header');
        $html = $this->overview('Project Rise', ['dealer' => 20, 'customer' => 102])->render();
        $this->assertContains('Test Mega Dealer (Mega Dealer)', $html);
        $this->assertContains('Customer Two', $html);
        $this->assertContains('PHP 140.00', $html);
        $this->assertContains('1 transaction', $html);
        $this->assertNotContains('dealer=20', $html);
    }
}
