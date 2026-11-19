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
        $request->setRouteResolver(function () use ($name, $controller) {
            return new Route('POST', '/permission-test', ['as' => $name, 'uses' => $controller ?: 'TestController@index', 'controller' => $controller ?: 'TestController@index']);
        });
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
