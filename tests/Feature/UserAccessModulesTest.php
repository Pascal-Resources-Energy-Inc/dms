<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureModuleAccess;
use App\Support\ModulePermissions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserAccessModulesTest extends TestCase
{
    public function personnelRoles()
    {
        return [[User::ROLE_AREA_DISTRIBUTOR], [User::ROLE_PROVINCIAL_DISTRIBUTOR]];
    }

    private function personnel(array $permissions, $role)
    {
        return $this->user($permissions, $role)->forceFill(['is_distributor_personnel' => true]);
    }

    /** @dataProvider personnelRoles */
    public function test_personnel_saved_permissions_control_module_views_and_mutations($role)
    {
        $user = $this->personnel(['products' => ['catalog' => ['view']],
            'customers' => ['records' => ['view', 'edit']],
            'dealers' => ['records' => [], 'mega' => ['view']], 'purchase_orders' => ['adpo' => []]], $role);
        foreach (['products', 'my-customers', 'client.view', 'customer.update', 'md-ads'] as $route) {
            $this->assertTrue($this->allowed($user, $route), $route);
        }
        foreach (['products.store', 'products.update', 'products.destroy', 'saveCustomer', 'dealer-ads',
            'ad-purchase-orders.index', 'ad-purchase-orders.updateStatus', 'orders', 'ad-dashboard'] as $route) {
            $this->assertFalse($this->allowed($user, $route), $route);
        }
        $this->assertTrue($this->allowed($this->personnel([], $role), 'orders'));
        $this->assertTrue($this->allowed($this->user([], $role), 'products.destroy'));
    }

    /** @dataProvider personnelRoles */
    public function test_personnel_sidebar_uses_distributor_routes_and_hides_unselected_modules($role)
    {
        $user = $this->personnel(['products' => ['catalog' => ['view']], 'customers' => ['records' => ['view']],
            'dealers' => ['mega' => ['view']], 'forms' => ['incident' => ['view']]], $role);
        $html = view('users.partials.personnel-sidebar', ['sidebarUser' => $user])->render();
        foreach (['/products', '/my-customers', '/md-ads', 'Incident Report'] as $value) $this->assertContains($value, $html);
        foreach (['/dealer-ads', '/ad-dashboard', '/ad-purchase-orders', '/users'] as $value) $this->assertNotContains('href="'.url($value).'"', $html);
        $this->assertFalse($user->canManageUsers('edit'));
    }

    /** @dataProvider personnelRoles */
    public function test_personnel_login_uses_permitted_module_or_access_information_page($role)
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\Auth\LoginController::class, 'redirectByRole');
        $method->setAccessible(true);
        $controller = new \App\Http\Controllers\Auth\LoginController;
        $this->assertSame('/products', $method->invoke($controller, $this->personnel(['products' => ['catalog' => ['view']]], $role)));
        $this->assertSame('/ad-dashboard', $method->invoke($controller, $this->personnel([], $role)));
        $this->assertSame('/access-unavailable', $method->invoke($controller, $this->personnel(['products' => ['catalog' => []]], $role)));
        $this->assertFalse($this->allowed($this->personnel(['products' => ['catalog' => []]], $role), 'ad-dashboard'));
    }

    private function user(array $permissions, $role = 'Admin')
    {
        $user = new User(['role' => $role, 'name' => 'Permission Test']);
        $user->forceFill(['access_permissions' => json_encode($permissions)]);
        return $user;
    }

    private function allowed($user, $name, array $input = [], $controller = null)
    {
        $request = Request::create('/permission-test', 'POST', $input);
        $request->setUserResolver(function () use ($user) { return $user; });
        $route = new Route('POST', '/permission-test', ['as' => $name, 'uses' => $controller ?: 'TestController@index', 'controller' => $controller ?: 'TestController@index']);
        $route->bind($request);
        $request->setRouteResolver(function () use ($route) { return $route; });
        try {
            return (new EnsureModuleAccess)->handle($request, function () { return true; });
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            return false;
        }
    }

    public function test_existing_grouped_permissions_continue_to_work()
    {
        $user = $this->user(['distributors' => ['records' => ['view', 'edit']],
            'dealers' => ['records' => ['view']], 'settings' => ['campaigns' => ['view', 'add']],
            'reports' => ['sales' => ['view'], 'operations' => ['view'], 'sedp' => ['view']],
            'return_refunds' => ['requests' => ['view']]]);
        foreach (['ads', 'pds', 'pds.update', 'dealers', 'mds', 'areas', 'mfis.store', 'raffles',
            'monthly-sales', 'dpo', 'aging', 'voucher-history', 'ad-awarded-areas', 'repeat-purchase-incentives',
            'center-chief-incentives', 'return-refund-report.data'] as $route) {
            $this->assertTrue($this->allowed($user, $route), $route);
        }
        $this->assertTrue($this->allowed($this->user([]), 'mfis.destroy'));
        $this->assertTrue($this->allowed($this->user([], 'Area Distributor'), 'orders'));
    }

    public function test_super_admin_retains_access_even_when_all_saved_permissions_are_empty()
    {
        $permissions = [];
        foreach (ModulePermissions::modules() as $key => $row) {
            list($module, $submodule) = explode('.', $key, 2);
            $permissions[$module][$submodule] = [];
        }
        $admin = $this->user($permissions);
        $admin->forceFill(['id' => '1', 'can_add' => 'off', 'can_edit' => 'off']);
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->canManageUsers('add'));
        $this->assertTrue($admin->canManageUsers('edit'));
        foreach (ModulePermissions::modules() as $key => $row) {
            foreach ($row['routes'] as $action => $routes) {
                $this->assertTrue(ModulePermissions::allows($admin, $key, $action), $key);
                foreach ($routes as $route) $this->assertTrue($this->allowed($admin, $route), $route);
            }
        }
    }

    public function test_other_accounts_do_not_receive_super_admin_privileges()
    {
        foreach (['Admin', 'MFI', 'Area Distributor', 'Provincial Distributor'] as $role) {
            $user = $this->user(['users' => ['accounts' => []]], $role);
            $user->id = $role === 'Admin' ? 2 : 1;
            $this->assertFalse($user->isSuperAdmin());
            $this->assertFalse($user->canManageUsers('add'));
            $this->assertFalse(ModulePermissions::allows($user, 'users.accounts', 'add'));
        }
    }

    public function test_separate_rows_do_not_grant_sibling_modules()
    {
        $user = $this->user(['distributors' => ['provincial' => ['view']],
            'dealers' => ['mega' => ['view']], 'settings' => ['mfis' => ['view']],
            'reports' => ['center_chief' => ['view']]]);
        foreach (['pds', 'mds', 'mfis.index', 'center-chief-incentives', 'center-chief-incentives.export', 'center-chief-incentives.transactions'] as $route) {
            $this->assertTrue($this->allowed($user, $route));
        }
        foreach (['ads', 'dealers', 'areas', 'mfis.update', 'vouchers', 'signup-incentives', 'repeat-purchase-incentives'] as $route) {
            $this->assertFalse($this->allowed($user, $route));
        }
    }

    public function test_explicit_empty_rows_override_legacy_inheritance()
    {
        $permissions = ModulePermissions::sanitize(['settings' => ['campaigns' => ['view'], 'mfis' => []],
            'reports' => ['sedp' => ['view'], 'center_chief' => []]]);
        $this->assertSame([], $permissions['settings']['mfis']);
        $this->assertSame([], $permissions['reports']['center_chief']);
        $this->assertSame($permissions, ModulePermissions::sanitize($permissions));
        $user = $this->user($permissions);
        $this->assertTrue($this->allowed($user, 'vouchers'));
        $this->assertFalse($this->allowed($user, 'mfis.index'));
        $this->assertTrue($this->allowed($user, 'signup-incentives'));
        $this->assertFalse($this->allowed($user, 'center-chief-incentives'));
    }

    public function test_plant_permissions_apply_to_shared_fulfillment_endpoints()
    {
        $user = $this->user(['purchase_orders' => ['adpo' => [], 'plant' => ['view']]])->forceFill(['warehouse' => 'lubao']);
        foreach (['warehouse-ad-purchase-orders.plant', 'warehouse-ad-purchase-orders.plant.export', 'ad-purchase-orders.show'] as $route) {
            $this->assertTrue($this->allowed($user, $route), $route);
        }
        foreach (['ad-purchase-orders.updateStatus', 'ad-purchase-orders.verification-complete', 'ad-purchase-orders.verification-incomplete'] as $route) {
            $this->assertFalse($this->allowed($user, $route), $route);
        }
        $user->access_permissions = json_encode(['purchase_orders' => ['adpo' => [], 'plant' => ['view', 'edit']]]);
        $this->assertTrue($this->allowed($user, 'ad-purchase-orders.updateStatus'));
        $this->assertTrue($this->allowed($user, 'ad-purchase-orders.verification-complete'));
        $user->access_permissions = json_encode(['purchase_orders' => ['adpo' => ['view', 'edit']]]);
        $this->assertTrue($this->allowed($user, 'warehouse-ad-purchase-orders.plant'));
        $this->assertTrue($this->allowed($user, 'ad-purchase-orders.updateStatus'));
    }

    public function test_mega_dealer_creation_uses_its_own_permission_on_the_shared_endpoint()
    {
        $user = $this->user(['dealers' => ['mega' => ['view', 'add'], 'records' => []]]);
        $this->assertTrue($this->allowed($user, null, ['partner_role' => 'Mega Dealer'], 'App\\Http\\Controllers\\DealerController@newDealer'));
        $this->assertFalse($this->allowed($user, null, ['partner_role' => 'Dealer'], 'App\\Http\\Controllers\\DealerController@newDealer'));
        $this->assertTrue($this->allowed($user, 'check.dealer.duplicate', ['partner_role' => 'Mega Dealer']));
    }

    public function test_mfi_report_authorization_accepts_individual_report_permissions()
    {
        $this->actingAs($this->user(['reports' => ['center_chief' => ['view']]], 'MFI'));
        $request = Request::create('/reports/center-chief-incentives');
        $request->setRouteResolver(function () { return new Route('GET', '/', ['as' => 'center-chief-incentives']); });
        $this->app->instance('request', $request);
        $method = new \ReflectionMethod(\App\Http\Controllers\ReportController::class, 'authorizeSedpReports');
        $method->setAccessible(true);
        $this->assertNull($method->invoke(new \App\Http\Controllers\ReportController));
        $request->setRouteResolver(function () { return new Route('GET', '/', ['as' => 'signup-incentives']); });
        $this->expectException(HttpException::class);
        $method->invoke(new \App\Http\Controllers\ReportController);
    }

    public function test_invalid_permissions_are_removed_and_legacy_role_rules_remain()
    {
        $permissions = ModulePermissions::sanitize(['settings' => ['mfis' => ['view', 'execute', ['delete'], true]], 'fake' => ['all' => ['view']]]);
        $this->assertSame(['view'], $permissions['settings']['mfis']);
        $this->assertArrayNotHasKey('fake', $permissions);
        $mfi = $this->user([], 'MFI');
        $mfi->can_access_settings = 'on';
        $this->assertTrue($this->allowed($mfi, 'mfis.update'));
        $this->assertFalse($this->allowed($mfi, 'signup-incentives'));
    }

    public function test_catalog_routes_and_rendered_rows_are_complete_without_duplicate_controls()
    {
        $html = view('admin-privillege')->render();
        $seen = [];
        foreach (ModulePermissions::modules() as $key => $row) {
            $this->assertContains(e($row['label']), $html);
            foreach ($row['routes'] as $action => $routes) {
                foreach ($routes as $route) {
                    $this->assertNotNull($this->app['router']->getRoutes()->getByName($route), $route);
                    $this->assertArrayNotHasKey($route, $seen, $route);
                    $seen[$route] = true;
                }
            }
        }
        $this->assertEquals(count(ModulePermissions::modules()), substr_count($html, 'class="access-row"'));
        foreach (['can_add', 'can_edit', 'can_delete', 'can_add_rewards', 'can_edit_rewards', 'can_delete_rewards'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'));
        }
    }

    public function test_save_reload_and_clear_keep_individual_permissions()
    {
        config(['audit.enabled' => false, 'database.default' => 'access_modules_test',
            'database.connections.access_modules_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('access_modules_test');
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, access_permissions TEXT, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE dealers (id INTEGER PRIMARY KEY, user_id INTEGER, status TEXT, address TEXT)');
        DB::statement('CREATE TABLE clients (id INTEGER PRIMARY KEY, user_id INTEGER, status TEXT, address TEXT)');
        DB::table('users')->insert(['id' => 7, 'name' => 'Test MFI', 'role' => 'MFI']);
        $controller = new UserController;
        $manager = new User(['role' => 'Admin', 'name' => 'Access manager']);
        $manager->id = 1;
        $this->actingAs($manager);
        $permissions = ['settings' => ['mfis' => ['view', 'edit'], 'campaigns' => ['view'], 'areas' => []],
            'forms' => ['incident' => ['view']], 'reports' => ['monthly' => ['view'], 'sales' => []]];
        $response = $controller->updateAccess(new Request(['id' => 7, 'access_permissions' => json_encode($permissions)]));
        $this->assertSame(200, $response->getStatusCode());
        $saved = $controller->show(7)->getData(true)['access_permissions'];
        $this->assertSame(['view', 'edit'], $saved['settings']['mfis']);
        $this->assertSame([], $saved['settings']['areas']);
        $this->assertSame(['view'], $saved['forms']['incident']);
        $this->assertFalse($this->allowed($this->user($saved), 'dsr'));
        $this->assertTrue($this->allowed($this->user($saved), 'monthly-sales.export'));
        $empty = [];
        foreach (ModulePermissions::modules() as $key => $definition) {
            list($module, $submodule) = explode('.', $key, 2);
            $empty[$module][$submodule] = [];
        }
        DB::table('users')->where('id', 7)->update(['role' => 'Admin']);
        $this->assertSame(200, $controller->updateAccess(new Request(['id' => 7, 'access_permissions' => json_encode($empty)]))->getStatusCode());
        $this->assertFalse($this->allowed(User::findOrFail(7), 'users'));
        $this->assertFalse(ModulePermissions::allows(User::findOrFail(7), 'forms.incident'));
    }
}
