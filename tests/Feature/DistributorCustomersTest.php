<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DistributorCustomersTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'customer_scope_test',
            'app.key' => 'base64:' . base64_encode(str_repeat('t', 32)), 'session.driver' => 'array']);
        foreach (['customer_scope_test', 'admin_crms', 'admin_crms2'] as $connection) {
            config(['database.connections.' . $connection => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
            DB::purge($connection);
            $db = DB::connection($connection);
            $db->statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT, area TEXT,
                client_reference TEXT, email_address TEXT, number TEXT, facebook TEXT, address TEXT,
                location_region TEXT, location_province TEXT, location_city TEXT, location_barangay TEXT,
                postal_code TEXT, street_address TEXT, spo TEXT, center TEXT, status TEXT, serial_number INTEGER,
                latitude NUMERIC, longitude NUMERIC, created_at TEXT, updated_at TEXT)');
            $db->statement('CREATE TABLE stoves (id INTEGER PRIMARY KEY, serial_number TEXT, client_id INTEGER, remarks TEXT, updated_at TEXT)');
            $db->statement('CREATE TABLE transaction_details (id INTEGER PRIMARY KEY, client_id INTEGER, user_id INTEGER,
                item TEXT, qty NUMERIC, price NUMERIC, points_client NUMERIC, created_at TEXT, deleted_at TEXT)');
            $db->table('clients')->insert([
                ['id' => 1, 'user_id' => 51, 'name' => 'Matched ' . $connection, 'area' => ' north ', 'client_reference' => 'PRC00001', 'status' => 'Active'],
                ['id' => 2, 'user_id' => 52, 'name' => 'Outside ' . $connection, 'area' => 'South', 'client_reference' => 'PRC00002', 'status' => 'Active'],
                ['id' => 3, 'user_id' => 53, 'name' => 'Disengaged ' . $connection, 'area' => 'Old area', 'client_reference' => 'PRC00003', 'status' => 'Inactive'],
                ['id' => 4, 'user_id' => 54, 'name' => 'Unassigned ' . $connection, 'area' => null, 'client_reference' => 'PRC00004', 'status' => 'Inactive'],
            ]);
            $db->table('transaction_details')->insert([
                ['id' => 1, 'client_id' => 1, 'user_id' => 500, 'item' => 'Matched purchase', 'points_client' => 5],
                // Another customer ID collides with the matched customer's user ID.
                ['id' => 2, 'client_id' => 51, 'user_id' => 51, 'item' => 'Unrelated purchase', 'points_client' => 900],
            ]);
        }
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT, name TEXT, first_name TEXT,
            middle_name TEXT, last_name TEXT, email TEXT, password TEXT, birthdate TEXT, age INTEGER,
            created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE centers (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER)');
        DB::statement('CREATE TABLE provincial_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributor_areas (id INTEGER PRIMARY KEY, provincial_distributor_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::table('users')->insert(['id' => 51, 'role' => 'Client', 'first_name' => 'Matched', 'last_name' => 'Customer', 'email' => 'existing@example.test']);
        DB::table('stoves')->insert([
            ['id' => 1, 'serial_number' => 'AVAILABLE', 'client_id' => null],
            ['id' => 2, 'serial_number' => 'TAKEN', 'client_id' => 2],
        ]);

        $this->viewRoot = storage_path('framework/testing/distributor-customers-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">@yield("css")</head><body>@yield("content")@yield("javascript")</body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    public function roles()
    {
        return [['Provincial Distributor'], ['Area Distributor']];
    }

    private function distributor($role)
    {
        DB::table('users')->insert(['id' => 100, 'role' => $role, 'name' => 'Distributor']);
        // Same profile ID in both tables, with deliberately different assignments.
        DB::table('area_distributors')->insert(['id' => 10, 'user_id' => 100]);
        DB::table('provincial_distributors')->insert(['id' => 10, 'user_id' => 100]);
        foreach (['ad_areas' => 'ad_id', 'provincial_distributor_areas' => 'provincial_distributor_id'] as $table => $foreignKey) {
            $correct = ($role === 'Area Distributor') === ($table === 'ad_areas');
            DB::table($table)->insert([
                ['id' => 1, $foreignKey => 10, 'area_name' => $correct ? 'North' : 'South', 'deleted_at' => null],
                ['id' => 2, $foreignKey => 10, 'area_name' => 'Old area', 'deleted_at' => '2026-01-01'],
            ]);
        }
        $this->actingAs(User::findOrFail(100));
    }

    /** @dataProvider roles */
    public function test_list_matches_only_active_awarded_areas_across_all_projects($role)
    {
        $this->distributor($role);
        $data = (new CustomerController)->myCustomers(Request::create('/my-customers'))->getData();
        $this->assertCount(3, $data['customers']);
        $this->assertSame(['North'], $data['customerAreaNames']);
        $this->assertSame(3, $data['activeCustomers']);
        $this->assertSame(0, $data['inactiveCustomers']);
        $this->assertEquals([5, 5, 5], $data['customers']->pluck('total_points')->all());
        $response = $this->get('/my-customers')->assertOk();
        $response->assertSee('Project Rise')->assertSee('Project Genesis')->assertSee('Add Customer')
            ->assertSee('MATCHED CUSTOMER_SCOPE_TEST')->assertDontSee('OUTSIDE CUSTOMER_SCOPE_TEST')
            ->assertDontSee('DISENGAGED')->assertDontSee('UNASSIGNED')->assertSee('name="area"');
        $this->get('/customers')->assertOk()->assertDontSee('OUTSIDE CUSTOMER_SCOPE_TEST');
    }

    /** @dataProvider roles */
    public function test_local_customer_actions_cannot_escape_awarded_areas($role)
    {
        $this->distributor($role);
        $this->get('/view-client/2')->assertStatus(403);
        $this->post('/customer/update/2', $this->payload())->assertStatus(403);
        $this->post('/change-avatar/2')->assertStatus(403);
        $this->post('/valid-id/2')->assertStatus(403);
        $this->post('/submit-contract/2')->assertStatus(403);
        $this->get('/signature/2')->assertStatus(403);
        $this->assertSame('Outside customer_scope_test', DB::table('clients')->where('id', 2)->value('name'));
    }

    /** @dataProvider roles */
    public function test_project_profile_and_transactions_are_scoped($role)
    {
        $this->distributor($role);
        $this->get('/admin-crm-customer/admin_crms/2')->assertStatus(404);
        $this->get('/admin-crm-customer/admin_crms/3')->assertStatus(404);
        $data = (new CustomerController)->viewAdminCrmCustomer(Request::create('/'), 'admin_crms', 1)->getData();
        $this->assertCount(1, $data['transactions']);
        $this->assertSame('Matched purchase', $data['transactions']->first()->item);
        $this->assertTrue($data['isRemoteCustomer']);
        $this->get('/admin-crm-customer/admin_crms/1')->assertOk()->assertSee('Matched purchase')->assertDontSee('Unrelated purchase')->assertDontSee('Edit Customer');
    }

    public function test_project_without_area_column_is_excluded()
    {
        $this->distributor('Provincial Distributor');
        DB::connection('admin_crms2')->statement('DROP TABLE clients');
        DB::connection('admin_crms2')->statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, name TEXT)');
        DB::connection('admin_crms2')->table('clients')->insert(['id' => 1, 'name' => 'No area']);
        $data = (new CustomerController)->myCustomers(Request::create('/'))->getData();
        $this->assertCount(0, $data['adminCrm2Customers']);
        $this->assertCount(2, $data['customers']);
        $this->get('/admin-crm-customer/admin_crms2/1')->assertStatus(403);
    }

    /** @dataProvider roles */
    public function test_no_profile_or_active_awards_returns_no_customers($role)
    {
        DB::table('users')->insert(['id' => 100, 'role' => $role]);
        $this->actingAs(User::findOrFail(100));
        $this->get('/my-customers')->assertOk()->assertSee('No active awarded areas')->assertDontSee('Add Customer')->assertDontSee('MATCHED');
    }

    private function payload(array $overrides = [])
    {
        return array_merge(['first_name' => 'New', 'last_name' => 'Customer', 'email_address' => 'new@example.test',
            'phone_number' => '09123456789', 'number' => '09123456789', 'area' => 'North', 'status' => 'Active'], $overrides);
    }

    /** @dataProvider roles */
    public function test_create_and_update_customer_in_own_area($role)
    {
        $this->distributor($role);
        $this->post('/new-customer', $this->payload())->assertRedirect('/view-client/5');
        $customer = DB::table('clients')->where('id', 5)->first();
        $this->assertSame('North', $customer->area);
        $this->assertSame('New Customer', $customer->name);
        $this->assertSame('Client', DB::table('users')->where('id', $customer->user_id)->value('role'));
        $this->post('/customer/update/5', $this->payload(['first_name' => 'Updated', 'status' => 'Inactive', 'serial_number' => 1]))->assertSessionHasNoErrors();
        $this->assertSame('Updated Customer', DB::table('clients')->where('id', 5)->value('name'));
        $this->assertSame('Inactive', DB::table('clients')->where('id', 5)->value('status'));
        $this->assertEquals(5, DB::table('stoves')->where('id', 1)->value('client_id'));
        $this->get('/view-client/5')->assertOk()->assertSee('Updated')->assertSee('North');
    }

    /** @dataProvider roles */
    public function test_invalid_area_or_assigned_serial_is_rejected_before_writes($role)
    {
        $this->distributor($role);
        $this->post('/new-customer', $this->payload(['area' => 'South']))->assertSessionHasErrors('area');
        $this->post('/new-customer', $this->payload(['area' => 'Old area']))->assertSessionHasErrors('area');
        $this->post('/new-customer', $this->payload(['serial_number' => 2]))->assertSessionHasErrors('serial_number');
        $this->post('/customer/update/1', $this->payload(['area' => 'South']))->assertSessionHasErrors('area');
        $this->assertSame(4, DB::table('clients')->count());
        $this->assertSame(2, DB::table('users')->count());
        $this->assertSame(' north ', DB::table('clients')->where('id', 1)->value('area'));
    }

    public function test_route_requires_distributor_authentication()
    {
        $this->get('/my-customers')->assertRedirect('/login');
        $this->actingAs(new User(['id' => 200, 'role' => 'Dealer']));
        $this->get('/my-customers')->assertStatus(403);
    }
}
