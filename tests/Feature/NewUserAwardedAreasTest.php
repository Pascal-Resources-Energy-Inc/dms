<?php

namespace Tests\Feature;

use App\AreaAd;
use App\AreaDistributor;
use App\ProvincialDistributor;
use App\ProvincialDistributorArea;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewUserAwardedAreasTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32)), 'audit.enabled' => false,
            'database.default' => 'award_creation_test', 'database.connections.award_creation_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Storage::fake('local');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            foreach (['first_name', 'middle_name', 'last_name', 'name', 'email', 'password', 'address', 'warehouse',
                'territory', 'delivery_address', 'role', 'mfi_type', 'type', 'project_tag', 'birthdate', 'age',
                'mothers_name', 'designation', 'employee_number', 'department', 'approval_types', 'access_permissions', 'can_add', 'can_edit'] as $column) {
                $table->string($column)->nullable();
            }
            $table->boolean('is_distributor_personnel')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
        require_once database_path('migrations/2026_11_26_000001_add_assigned_distributor_to_users.php');
        (new \AddAssignedDistributorToUsers)->up();
        foreach ([new AreaDistributor, new ProvincialDistributor] as $profile) {
            Schema::create($profile->getTable(), function (Blueprint $table) use ($profile) {
                $table->increments('id');
                foreach (array_diff($profile->getFillable(), ['nature_of_business']) as $column) $table->string($column)->nullable();
                $table->timestamps(); $table->softDeletes();
            });
        }
        foreach (['ad_areas' => 'ad_id', 'provincial_distributor_areas' => 'provincial_distributor_id'] as $name => $foreignKey) {
            Schema::create($name, function (Blueprint $table) use ($foreignKey) {
                $table->increments('id'); $table->unsignedInteger($foreignKey);
                $table->unsignedInteger('ad_user_id')->nullable(); $table->unsignedInteger('user_id')->nullable();
                $table->string('area_name'); $table->string('project_type')->nullable(); $table->string('user_role');
                $table->date('joining_date')->nullable(); $table->decimal('package_amount', 12, 2)->nullable();
                $table->timestamps(); $table->softDeletes();
            });
        }
        require_once database_path('migrations/2026_11_19_000001_add_business_and_offer_sheet_to_distributor_areas.php');
        (new \AddBusinessAndOfferSheetToDistributorAreas)->up();
        require_once database_path('migrations/2026_11_19_000002_add_business_type_to_distributor_areas.php');
        (new \AddBusinessTypeToDistributorAreas)->up();
        require_once database_path('migrations/2026_11_19_000003_add_nature_of_business_to_distributors.php');
        (new \AddNatureOfBusinessToDistributors)->up();
        Schema::create('areas', function (Blueprint $table) { $table->increments('id'); $table->string('name'); });
        DB::table('areas')->insert([['name' => 'Area A'], ['name' => 'Area B'], ['name' => 'Area C']]);
        $admin = new User(['role' => 'Admin', 'name' => 'QA Admin']); $admin->id = 999;
        $admin->forceFill(['can_add' => 'on']);
        $this->actingAs($admin);
    }

    public function roles()
    {
        return [['Area Distributor', AreaDistributor::class, AreaAd::class],
            ['Provincial Distributor', ProvincialDistributor::class, ProvincialDistributorArea::class]];
    }

    private function personnelOwner($role, $profileClass, array $attributes = [])
    {
        $owner = new User(['role' => $role, 'name' => 'Existing distributor', 'email' => 'owner-'.User::count().'@example.test']);
        $owner->save();
        return $profileClass::create(array_merge([
            'user_id' => $owner->id, 'name' => 'Existing distributor', 'store_code' => 'EXISTING-001',
            'business_name' => 'Existing store', 'status' => 'Active',
        ], $attributes));
    }

    private function personnelInput($role, $owner, array $changes = [])
    {
        return array_replace([
            'role' => $role, 'is_distributor_personnel' => '1', 'assigned_distributor_user_id' => $owner->user_id,
            'first_name' => 'QA', 'last_name' => 'Personnel', 'email_address' => 'personnel@example.test',
            'facebook' => 'qa-personnel',
        ], $changes);
    }

    private function allowUserEditing()
    {
        auth()->user()->forceFill(['can_edit' => 'on']);
    }

    private function editPersonnelInput($user, array $changes = [])
    {
        return array_replace(['id' => $user->id, 'name' => 'Updated personnel', 'email' => $user->email,
            'is_distributor_personnel' => '1', 'assigned_distributor_user_id' => $user->assigned_distributor_user_id], $changes);
    }

    /** @dataProvider roles */
    public function test_personnel_access_can_be_saved_reloaded_and_cleared_without_changing_the_owner($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $owner->userAds->forceFill(['access_permissions' => json_encode(['products' => ['catalog' => ['view', 'edit']]])])->save();
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $this->postJson('/users/access-update', ['id' => $staff->id, 'access_permissions' => json_encode([
            'products' => ['catalog' => ['view', 'execute']], 'inventory_transfers' => ['transfers' => ['view', 'add']],
        ])])->assertOk()->assertJson(['success' => true]);
        $saved = json_decode($staff->fresh()->access_permissions, true);
        $this->assertSame(['view'], $saved['products']['catalog']);
        $this->assertSame(['view', 'add'], $saved['inventory_transfers']['transfers']);
        $this->assertSame([], $saved['customers']['records']);
        $this->assertSame($role, $staff->fresh()->role);
        $this->assertSame((int) $owner->user_id, (int) $staff->fresh()->assigned_distributor_user_id);
        $this->assertSame(['view', 'edit'], json_decode($owner->userAds->fresh()->access_permissions, true)['products']['catalog']);
        foreach (['dealers', 'clients'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id'); $table->integer('user_id');
                $table->string('address')->nullable(); $table->string('status')->nullable();
            });
        }
        $this->getJson('/users/'.$staff->id.'/show')->assertOk()->assertJson(['access_permissions' => $saved]);
        $this->postJson('/users/access-update', ['id' => $staff->id, 'access_permissions' => '{}'])->assertOk();
        $empty = json_decode($staff->fresh()->access_permissions, true);
        $this->assertNotEmpty($empty);
        foreach ($empty as $rows) foreach ($rows as $actions) $this->assertSame([], $actions);
        $this->assertFalse(\App\Support\ModulePermissions::allows($staff->fresh(), 'products.catalog'));
    }

    /** @dataProvider roles */
    public function test_personnel_access_button_is_available_only_on_supported_accounts_for_authorized_managers($role, $profileClass)
    {
        foreach (['dealers', 'clients'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id'); $table->integer('user_id');
                $table->string('address')->nullable(); $table->string('status')->nullable();
            });
        }
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $response = $this->getJson('/users/data?draw=1')->assertOk();
        $rows = collect($response->json('data'));
        $this->assertContains('btn-access-user', $rows->firstWhere('id', $staff->id)['actions']);
        $this->assertNotContains('btn-access-user', $rows->firstWhere('id', $owner->user_id)['actions']);
        $this->postJson('/users/access-update', ['id' => $owner->user_id, 'access_permissions' => '{}'])->assertStatus(422);
        $this->actingAs($staff);
        $this->postJson('/users/access-update', ['id' => $staff->id, 'access_permissions' => '{"users":{"accounts":["edit"]}}'])->assertStatus(403);
        $this->assertNull($staff->fresh()->access_permissions);
    }

    public function test_pd_personnel_fulfillment_permissions_apply_to_shared_order_detail_and_status_routes()
    {
        Schema::create('ad_purchase_orders', function (Blueprint $table) {
            $table->increments('id'); $table->integer('provincial_distributor_id')->nullable();
            $table->string('order_source'); $table->softDeletes();
        });
        $owner = $this->personnelOwner(User::ROLE_PROVINCIAL_DISTRIBUTOR, ProvincialDistributor::class);
        $this->postJson('/new-user', $this->personnelInput(User::ROLE_PROVINCIAL_DISTRIBUTOR, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $staff->forceFill(['access_permissions' => json_encode(['purchase_orders' => ['provincial' => ['view', 'edit'], 'adpo' => []]])]);
        DB::table('ad_purchase_orders')->insert([
            ['id' => 1, 'provincial_distributor_id' => $owner->id, 'order_source' => 'provincial_distributor'],
            ['id' => 2, 'provincial_distributor_id' => null, 'order_source' => 'prei_warehouse'],
        ]);
        $allowed = function ($id, $name) use ($staff) {
            $request = \Illuminate\Http\Request::create('/ad-purchase-orders/'.$id);
            $request->setUserResolver(function () use ($staff) { return $staff; });
            $route = new \Illuminate\Routing\Route('GET', '/ad-purchase-orders/{id}', ['as' => $name]);
            $route->bind($request);
            $request->setRouteResolver(function () use ($route) { return $route; });
            try {
                return (new \App\Http\Middleware\EnsureModuleAccess)->handle($request, function () { return true; });
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
                return false;
            }
        };
        $this->assertTrue($allowed(1, 'ad-purchase-orders.show'));
        $this->assertTrue($allowed(1, 'ad-purchase-orders.updateStatus'));
        $this->assertFalse($allowed(2, 'ad-purchase-orders.show'));
        $this->assertFalse($allowed(2, 'ad-purchase-orders.updateStatus'));
        $staff->access_permissions = json_encode(['purchase_orders' => ['provincial' => ['view'], 'adpo' => []]]);
        $this->assertFalse($allowed(1, 'ad-purchase-orders.updateStatus'));
    }

    /** @dataProvider roles */
    public function test_edit_loads_saved_personnel_assignment_and_active_options($role, $profileClass)
    {
        foreach (['dealers', 'clients'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id'); $table->integer('user_id');
                $table->string('address')->nullable(); $table->string('status')->nullable();
            });
        }
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $this->getJson('/users/'.$staff->id.'/show')->assertOk()->assertJson([
            'id' => $staff->id, 'role' => $role, 'is_distributor_personnel' => true,
            'assigned_distributor_user_id' => $owner->user_id, 'has_distributor_profile' => false,
        ])->assertJsonCount(1, 'personnel_distributors');
        $owner->update(['status' => 'Inactive']);
        $this->getJson('/users/'.$staff->id.'/show')->assertOk()->assertJson([
            'assigned_distributor_user_id' => $owner->user_id, 'assigned_distributor_name' => 'Existing distributor',
        ])->assertJsonCount(0, 'personnel_distributors');
    }

    /** @dataProvider roles */
    public function test_edit_reassigns_personnel_without_mutating_distributor_profiles_or_areas($role, $profileClass, $areaClass)
    {
        $this->allowUserEditing();
        $owner = $this->personnelOwner($role, $profileClass);
        $secondOwner = $this->personnelOwner($role, $profileClass, ['business_name' => 'Second owner store']);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $this->postJson('/users/update', $this->editPersonnelInput($staff, [
            'assigned_distributor_user_id' => $secondOwner->user_id, 'role' => 'Admin', 'address' => 'Updated address',
        ]))->assertOk()->assertJson(['success' => true]);
        $saved = $staff->fresh();
        $this->assertTrue($saved->is_distributor_personnel);
        $this->assertSame($role, $saved->role);
        $this->assertSame((int) $secondOwner->user_id, (int) $saved->assigned_distributor_user_id);
        $this->assertSame('Updated personnel', $saved->name);
        $this->assertSame('Updated address', $saved->address);
        $this->assertSame('qa-personnel', $saved->personnel_facebook);
        $this->assertSame('Second owner store', $saved->assignedDistributorProfile()->business_name);
        $this->assertSame('Existing store', $owner->fresh()->business_name);
        $this->assertSame(2, $profileClass::count());
        $this->assertSame(0, $areaClass::count());
    }

    /** @dataProvider roles */
    public function test_edit_rejects_invalid_assignments_and_rolls_back_all_user_changes($role, $profileClass)
    {
        $this->allowUserEditing();
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        foreach ([null, '', 'bad', 999999, $staff->id, [$owner->user_id]] as $id) {
            $this->postJson('/users/update', $this->editPersonnelInput($staff, ['assigned_distributor_user_id' => $id]))
                ->assertStatus(422)->assertJsonValidationErrors('assigned_distributor_user_id');
        }
        $otherRole = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? User::ROLE_AREA_DISTRIBUTOR : User::ROLE_PROVINCIAL_DISTRIBUTOR;
        $otherClass = $profileClass === ProvincialDistributor::class ? AreaDistributor::class : ProvincialDistributor::class;
        $wrongOwner = $this->personnelOwner($otherRole, $otherClass);
        $this->postJson('/users/update', $this->editPersonnelInput($staff, ['assigned_distributor_user_id' => $wrongOwner->user_id]))
            ->assertStatus(422)->assertJsonValidationErrors('assigned_distributor_user_id');
        $owner->update(['status' => 'Inactive']);
        $this->postJson('/users/update', $this->editPersonnelInput($staff))->assertStatus(422);
        $this->assertSame('QA Personnel', $staff->fresh()->name);
        $this->assertSame((int) $owner->user_id, (int) $staff->fresh()->assigned_distributor_user_id);
    }

    /** @dataProvider roles */
    public function test_edit_can_toggle_owner_personnel_and_restore_its_preserved_profile($role, $profileClass, $areaClass)
    {
        $this->allowUserEditing();
        $owner = $this->personnelOwner($role, $profileClass);
        $targetProfile = $this->personnelOwner($role, $profileClass, ['business_name' => 'Preserved business']);
        $target = $targetProfile->userAds;
        $this->postJson('/users/update', $this->editPersonnelInput($target, ['assigned_distributor_user_id' => $owner->user_id]))
            ->assertOk();
        $this->assertTrue($target->fresh()->is_distributor_personnel);
        $this->assertFalse($profileClass::withoutPersonnel()->where('id', $targetProfile->id)->exists());
        $this->assertSame('Preserved business', $targetProfile->fresh()->business_name);
        $this->assertSame(2, $profileClass::count());
        $this->postJson('/users/update', $this->editPersonnelInput($target, ['is_distributor_personnel' => '0']))->assertOk();
        $saved = $target->fresh();
        $this->assertFalse($saved->is_distributor_personnel);
        $this->assertNull($saved->assigned_distributor_user_id);
        $this->assertTrue($profileClass::withoutPersonnel()->where('id', $targetProfile->id)->exists());
        $this->assertSame('Preserved business', $targetProfile->fresh()->business_name);
        $this->assertSame(0, $areaClass::count());
    }

    /** @dataProvider roles */
    public function test_edit_cannot_uncheck_personnel_without_own_profile_or_create_a_personnel_chain($role, $profileClass)
    {
        $this->allowUserEditing();
        $owner = $this->personnelOwner($role, $profileClass);
        $other = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $this->postJson('/users/update', $this->editPersonnelInput($staff, ['is_distributor_personnel' => '0']))
            ->assertStatus(422)->assertJsonValidationErrors('is_distributor_personnel');
        $this->postJson('/users/update', $this->editPersonnelInput($owner->userAds, ['assigned_distributor_user_id' => $other->user_id]))
            ->assertStatus(422)->assertJsonValidationErrors('is_distributor_personnel');
        $this->assertTrue($staff->fresh()->is_distributor_personnel);
        $this->assertFalse((bool) $owner->userAds->fresh()->is_distributor_personnel);
        $this->assertSame('QA Personnel', $staff->fresh()->name);
    }

    /** @dataProvider roles */
    public function test_edit_without_personnel_fields_keeps_saved_assignment_and_requires_edit_permission($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $input = ['id' => $staff->id, 'name' => 'Updated only name', 'email' => $staff->email];
        $this->postJson('/users/update', $input)->assertStatus(403);
        $this->allowUserEditing();
        $this->postJson('/users/update', $input)->assertOk();
        $this->assertTrue($staff->fresh()->is_distributor_personnel);
        $this->assertSame((int) $owner->user_id, (int) $staff->fresh()->assigned_distributor_user_id);
    }

    /** @dataProvider roles */
    public function test_personnel_are_linked_to_existing_distributor_without_creating_distributors_or_areas($role, $profileClass, $areaClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $before = [$profileClass::count(), AreaDistributor::count(), ProvincialDistributor::count(), $areaClass::count()];
        $this->postJson('/new-user', $this->personnelInput($role, $owner, [
            'business_name' => 'Forged business', 'store_code' => 'FORGED', 'area_name' => ['Area A'], 'type' => ['Project Rise'],
        ]))->assertStatus(201)->assertJson(['success' => true]);
        $staff = User::where('email', 'personnel@example.test')->firstOrFail();
        $this->assertTrue($staff->is_distributor_personnel);
        $this->assertSame($role, $staff->role);
        $this->assertSame($owner->user_id, (int) $staff->assigned_distributor_user_id);
        $this->assertSame('qa-personnel', $staff->personnel_facebook);
        $this->assertSame($before, [$profileClass::count(), AreaDistributor::count(), ProvincialDistributor::count(), $areaClass::count()]);
        $this->assertSame('Existing store', $owner->fresh()->business_name);
        $this->assertSame($owner->id, $staff->assignedDistributorProfile()->id);
        $relation = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 'provincialDistributor' : 'ad';
        $this->assertSame($owner->id, $staff->{$relation}->id);
        $this->assertSame($owner->id, User::with($relation)->findOrFail($staff->id)->{$relation}->id);
        $this->assertFalse(User::where('id', $staff->id)->has($relation)->exists());
        $this->assertSame($owner->user_id, $owner->fresh()->userAds->id);
        $this->assertSame(0, $profileClass::where('user_id', $staff->id)->count());
        $this->assertSame('[]', $staff->type);
    }

    /** @dataProvider roles */
    public function test_personnel_requires_matching_active_owner_and_rejects_personnel_as_owner($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $input = $this->personnelInput($role, $owner);
        foreach ([null, '', 999999, 'bad', [$owner->user_id]] as $value) {
            $this->postJson('/new-user', array_replace($input, ['assigned_distributor_user_id' => $value]))
                ->assertStatus(422)->assertJsonValidationErrors('assigned_distributor_user_id');
        }
        $otherRole = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? User::ROLE_AREA_DISTRIBUTOR : User::ROLE_PROVINCIAL_DISTRIBUTOR;
        $this->postJson('/new-user', array_replace($input, ['role' => $otherRole]))->assertStatus(422);
        $owner->update(['status' => 'Inactive']);
        $this->postJson('/new-user', $input)->assertStatus(422);
        $owner->update(['status' => 'Active']);
        $owner->userAds->forceFill(['is_distributor_personnel' => true])->save();
        $this->postJson('/new-user', $input)->assertStatus(422);
        $this->assertSame(1, User::count());
        $this->assertSame(1, $profileClass::count());
    }

    /** @dataProvider roles */
    public function test_personnel_login_uses_assigned_distributor_status($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->personnelInput($role, $owner))->assertStatus(201);
        \Auth::guard()->setUser(User::where('email', 'personnel@example.test')->firstOrFail());
        \Auth::logout();
        $this->postJson('/login', ['email' => 'personnel@example.test', 'password' => '12345678', 'selected_role' => $role])
            ->assertOk()->assertJson(['success' => true, 'redirect' => '/ad-dashboard']);
        \Auth::logout();
        $owner->update(['status' => 'Inactive']);
        $this->postJson('/login', ['email' => 'personnel@example.test', 'password' => '12345678', 'selected_role' => $role])
            ->assertStatus(422)->assertJson(['success' => false, 'message' => 'Your account is inactive.']);
        $this->assertGuest();
    }

    /** @dataProvider roles */
    public function test_distributor_creation_does_not_become_personnel_from_stale_assignment($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $this->postJson('/new-user', $this->input($role, [
            'is_distributor_personnel' => '0', 'assigned_distributor_user_id' => $owner->user_id,
        ]))->assertStatus(201);
        $created = User::where('email', 'qa@example.test')->firstOrFail();
        $this->assertFalse($created->is_distributor_personnel);
        $this->assertNull($created->assigned_distributor_user_id);
        $this->assertSame(1, $profileClass::where('user_id', $created->id)->count());
    }

    public function test_new_users_selection_contains_both_owner_types_and_excludes_inactive_deleted_and_personnel_profiles()
    {
        Schema::create('stoves', function (Blueprint $table) {
            $table->increments('id'); $table->string('serial_number'); $table->integer('client_id')->nullable();
        });
        Schema::create('centers', function (Blueprint $table) {
            $table->increments('id'); $table->string('name'); $table->softDeletes();
        });
        foreach (['dealers', 'clients'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id'); $table->integer('user_id');
                $table->string('address')->nullable(); $table->string('status')->nullable();
            });
        }
        $ad = $this->personnelOwner('Area Distributor', AreaDistributor::class);
        $pd = $this->personnelOwner('Provincial Distributor', ProvincialDistributor::class);
        $ad->areas()->create(['area_name' => 'Area A', 'ad_user_id' => $ad->user_id, 'user_role' => 'Area Distributor']);
        $retiredArea = $ad->areas()->create(['area_name' => 'Area B', 'ad_user_id' => $ad->user_id, 'user_role' => 'Area Distributor']);
        $retiredArea->delete();
        $inactive = $this->personnelOwner('Area Distributor', AreaDistributor::class, ['status' => 'Inactive']);
        $deleted = $this->personnelOwner('Provincial Distributor', ProvincialDistributor::class);
        $deleted->delete();
        $staff = $this->personnelOwner('Area Distributor', AreaDistributor::class);
        $staff->userAds->forceFill(['is_distributor_personnel' => true])->save();
        $view = (new \App\Http\Controllers\UserController)->index(\Illuminate\Http\Request::create('/users'));
        $options = $view->getData()['personnelDistributors'];
        $this->assertCount(2, $options);
        $this->assertSame([$pd->user_id, $ad->user_id], $options->pluck('user_id')->all());
        $this->assertSame(['Area A'], $options->last()['areas']);
    }

    /** @dataProvider roles */
    public function test_existing_unassigned_personnel_flags_keep_their_existing_profile($role, $profileClass)
    {
        $owner = $this->personnelOwner($role, $profileClass);
        $user = $owner->userAds;
        $user->forceFill(['is_distributor_personnel' => true])->save();
        $relation = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 'provincialDistributor' : 'ad';
        $this->assertSame($owner->id, User::with($relation)->findOrFail($user->id)->{$relation}->id);
    }

    /** @dataProvider roles */
    public function test_partner_code_preview_uses_the_correct_distributor_table_and_largest_suffix($role, $profileClass)
    {
        $prefix = ($role === 'Provincial Distributor' ? 'PD' : 'AD') . '-' . now()->format('Y') . '-';
        $table = (new $profileClass)->getTable();
        DB::table($table)->insert([
            ['store_code' => $prefix . '0012'],
            ['store_code' => $prefix . '0003'],
            ['store_code' => $prefix . 'invalid'],
            ['store_code' => str_replace(now()->format('Y'), now()->subYear()->format('Y'), $prefix) . '9000'],
        ]);
        $otherTable = $table === 'provincial_distributors' ? 'area_distributors' : 'provincial_distributors';
        DB::table($otherTable)->insert(['store_code' => $prefix . '9999']);

        $this->postJson('/generate-partner-code', ['role' => $role])
            ->assertOk()->assertJson(['success' => true, 'code' => $prefix . '0013']);
        $this->assertSame(0, User::count());
        $this->assertSame(4, DB::table($table)->count());
    }

    /** @dataProvider roles */
    public function test_partner_code_does_not_reuse_deleted_codes_or_truncate_large_numbers($role, $profileClass)
    {
        $prefix = ($role === 'Provincial Distributor' ? 'PD' : 'AD') . '-' . now()->format('Y') . '-';
        $table = (new $profileClass)->getTable();
        $this->postJson('/generate-partner-code', ['role' => $role])
            ->assertOk()->assertJson(['code' => $prefix . '0001']);
        DB::table($table)->insert(['store_code' => $prefix . '9999', 'deleted_at' => now()]);

        $this->postJson('/generate-partner-code', ['role' => $role])
            ->assertOk()->assertJson(['code' => $prefix . '10000']);
    }

    /** @dataProvider roles */
    public function test_distributor_save_allocates_a_fresh_code_instead_of_trusting_the_preview($role, $profileClass)
    {
        $prefix = ($role === 'Provincial Distributor' ? 'PD' : 'AD') . '-' . now()->format('Y') . '-';
        $table = (new $profileClass)->getTable();
        DB::table($table)->insert(['store_code' => $prefix . '0007']);
        $this->postJson('/generate-partner-code', ['role' => $role])->assertJson(['code' => $prefix . '0008']);
        // Another saved profile takes the code while the modal is open.
        DB::table($table)->insert(['store_code' => $prefix . '0008']);

        $this->postJson('/new-user', $this->input($role, ['store_code' => $prefix . '0008']))
            ->assertStatus(201);
        $this->assertSame($prefix . '0009', $profileClass::where('email_address', 'qa@example.test')->firstOrFail()->store_code);

        $this->postJson('/new-user', $this->input($role, [
            'first_name' => 'Second', 'email_address' => 'second@example.test',
            'area_name' => [0 => 'Area C'], 'store_code' => '',
        ]))->assertStatus(201);
        $this->assertSame($prefix . '0010', $profileClass::where('email_address', 'second@example.test')->firstOrFail()->store_code);
    }

    public function test_mega_dealer_preview_keeps_its_prefix_and_invalid_roles_do_not_generate_codes()
    {
        $prefix = 'MD-' . now()->format('Y') . '-';
        DB::table('area_distributors')->insert(['store_code' => $prefix . '0004']);
        $this->postJson('/generate-partner-code', ['role' => 'Mega Dealer'])
            ->assertOk()->assertJson(['success' => true, 'code' => $prefix . '0005']);
        foreach (['Admin', 'MFI', '', ['Provincial Distributor']] as $role) {
            $this->postJson('/generate-partner-code', ['role' => $role])
                ->assertOk()->assertJson(['success' => false]);
        }
    }

    /** @dataProvider roles */
    public function test_super_admin_creates_distributors_with_awarded_areas_despite_saved_restrictions($role, $profileClass)
    {
        $admin = new User(['role' => 'Admin', 'name' => 'Super Administrator']);
        $admin->forceFill(['id' => '1', 'can_add' => 'off',
            'access_permissions' => json_encode(['users' => ['accounts' => []]])]);
        $this->actingAs($admin);

        $this->postJson('/new-user', $this->input($role))
            ->assertStatus(201)->assertJson(['success' => true, 'redirect' => url('/users')]);

        $user = User::where('role', $role)->firstOrFail();
        $profile = $profileClass::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('LPG TRADING', $profile->nature_of_business);
        $this->assertCount(4, $profile->areas);
        $this->assertSame('4500.50', $profile->areas->first()->package_amount);
        $this->assertSame('Main Business', $profile->areas->first()->business_name);
        $this->assertSame('Sole Proprietorship', $profile->areas->first()->business_type);
    }

    /** @dataProvider roles */
    public function test_restricted_admin_cannot_create_a_distributor_without_users_add_access($role, $profileClass)
    {
        $admin = auth()->user();
        $admin->forceFill(['access_permissions' => json_encode(['users' => ['accounts' => ['view']]])]);
        $this->actingAs($admin);

        $this->postJson('/new-user', $this->input($role))->assertStatus(403);
        $this->assertSame(0, User::count());
        $this->assertSame(0, $profileClass::count());
    }

    /** @dataProvider roles */
    public function test_users_add_permission_allows_distributor_creation_without_legacy_flag($role, $profileClass)
    {
        $admin = auth()->user();
        $admin->forceFill(['can_add' => 'off',
            'access_permissions' => json_encode(['users' => ['accounts' => ['view', 'add']]])]);
        $this->actingAs($admin);

        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $this->assertSame(1, $profileClass::count());
    }

    public function test_super_admin_still_receives_validation_errors_without_creating_records()
    {
        $admin = auth()->user();
        $admin->forceFill(['id' => 1, 'can_add' => 'off']);
        $this->actingAs($admin);

        $this->postJson('/new-user', $this->input('Area Distributor', ['area_name' => []]))
            ->assertStatus(422)->assertJsonValidationErrors(['area_name']);
        $this->assertSame(0, User::count());
        $this->assertSame(0, AreaDistributor::count());
    }

    private function manageUrl($profile)
    {
        return route($profile instanceof ProvincialDistributor ? 'pds.areas.update' : 'update.areas', $profile->id);
    }

    /** @dataProvider roles */
    public function test_new_distributor_saves_selected_nature_independently_of_business_type($role, $profileClass)
    {
        $nature = 'GADGET, APPLIANCE & ELECTRONIC SHOP';
        $this->postJson('/new-user', $this->input($role, ['nature_of_business' => $nature]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $this->assertSame($nature, $profile->fresh()->nature_of_business);
        $this->assertSame('Sole Proprietorship', $profile->business_type);
        $this->assertSame('Main Business', $profile->business_name);
        $otherClass = $profileClass === ProvincialDistributor::class ? AreaDistributor::class : ProvincialDistributor::class;
        $this->assertSame(0, $otherClass::count());
    }

    /** @dataProvider roles */
    public function test_new_distributor_rejects_missing_or_invalid_nature_without_creating_records($role, $profileClass)
    {
        foreach ([null, '', 'Unknown business', ['LPG TRADING']] as $nature) {
            $this->postJson('/new-user', $this->input($role, ['nature_of_business' => $nature]))
                ->assertStatus(422)->assertJsonValidationErrors(['nature_of_business']);
        }
        $this->assertSame(0, User::count());
        $this->assertSame(0, $profileClass::count());
        $this->assertSame(0, AreaAd::count());
        $this->assertSame(0, ProvincialDistributorArea::count());
    }

    /** @dataProvider roles */
    public function test_manage_saves_business_details_files_and_preserves_omitted_areas($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role, ['type' => []]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $first = $profile->areas->first();
        $second = $profile->areas->last();
        $this->post($this->manageUrl($profile), [
            'business_name' => 'Forged main name', 'business_type' => 'Nonprofit',
            'rows' => [5 => ['id' => $first->id, 'area_name' => $first->area_name, 'project_type' => 'Project Genesis',
                'package_amount' => '5500.75', 'joining_date' => '2026-01-01',
                'same_business_name' => '1', 'same_business_type' => '1',
                'offer_sheet' => UploadedFile::fake()->create('offer.pdf', 20)],
                9 => ['area_name' => 'Area C', 'business_name' => 'Custom business', 'business_type' => 'Cooperative', 'package_amount' => '0']],
        ], ['Accept' => 'application/json'])->assertOk()->assertJson(['status' => true]);
        $saved = $first->fresh();
        $this->assertSame('Main Business', $saved->business_name);
        $this->assertSame('Sole Proprietorship', $saved->business_type);
        $this->assertSame('5500.75', $saved->package_amount);
        $this->assertNull($saved->project_type);
        Storage::disk('local')->assertExists($saved->offer_sheet_path);
        $this->get(route($profile instanceof ProvincialDistributor ? 'pds.areas.offer-sheet' : 'ad.areas.offer-sheet', [$profile->id, $first->id]))->assertOk();
        $this->assertNull($second->fresh()->deleted_at);
        $this->assertSame('Second Business', $second->fresh()->business_name);
        $added = $profile->areas()->where('area_name', 'Area C')->firstOrFail();
        $this->assertSame('Cooperative', $added->business_type);
        $this->assertSame('Custom business', $added->business_name);
        $this->assertSame('0.00', $added->package_amount);

        $this->postJson($this->manageUrl($profile), ['rows' => [['id' => $first->id, 'area_name' => $first->area_name]]])->assertOk();
        $this->assertSame($saved->offer_sheet_path, $first->fresh()->offer_sheet_path);
        $this->post($this->manageUrl($profile), ['rows' => [['id' => $first->id, 'area_name' => $first->area_name,
            'offer_sheet' => UploadedFile::fake()->create('replacement.pdf', 20)]]], ['Accept' => 'application/json'])->assertOk();
        $this->assertSame('replacement.pdf', $first->fresh()->offer_sheet_name);
        $this->assertNotSame($saved->offer_sheet_path, $first->fresh()->offer_sheet_path);
    }

    /** @dataProvider roles */
    public function test_manage_removal_records_selected_disengagement_date_and_keeps_other_rows($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role, ['type' => []]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $first = $profile->areas->first();
        $first->update(['joining_date' => '2026-01-01']);
        $this->postJson($this->manageUrl($profile), [
            'removed_area_ids' => [$first->id], 'disengagement_dates' => [$first->id => '2026-02-01'],
        ])->assertOk()->assertJson(['status' => true]);
        $this->assertSame('2026-02-01', $first->fresh()->deleted_at->format('Y-m-d'));
        $this->assertSame(1, $profile->areas()->count());
        $this->assertSame('4500.50', $first->fresh()->package_amount);
        if ($profile instanceof ProvincialDistributor) {
            $legacy = AreaDistributor::where('user_id', $profile->user_id)->firstOrFail();
            $this->assertSame('2026-02-01', $legacy->trashedAreas()->firstOrFail()->deleted_at->format('Y-m-d'));
        }
    }

    /** @dataProvider roles */
    public function test_manage_rejects_invalid_dates_foreign_ids_and_duplicate_area_without_changes($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role, ['type' => []]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $first = $profile->areas->first();
        $first->update(['joining_date' => '2026-01-01']);
        foreach ([null, 'bad-date', '2026-02-31', '2025-12-31', now()->addDay()->format('Y-m-d')] as $date) {
            $this->postJson($this->manageUrl($profile), ['removed_area_ids' => [$first->id], 'disengagement_dates' => [$first->id => $date]])
                ->assertStatus(422)->assertJsonValidationErrors(['disengagement_dates.'.$first->id]);
        }
        foreach ([['rows' => [['id' => 99999, 'area_name' => 'Area C']]], ['removed_area_ids' => [99999]],
            ['rows' => [['id' => $first->id, 'area_name' => 'Area B']]], ['rows' => [['area_name' => 'area a']]]] as $invalid) {
            $this->postJson($this->manageUrl($profile), $invalid)->assertStatus(422);
        }
        $this->assertSame(2, $profile->areas()->count());
        $this->assertSame('Area A', $first->fresh()->area_name);
    }

    /** @dataProvider roles */
    public function test_manage_rejects_invalid_upload_and_rolls_back_failed_save($role, $profileClass, $areaClass)
    {
        $this->postJson('/new-user', $this->input($role, ['type' => []]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $first = $profile->areas->first();
        foreach ([UploadedFile::fake()->create('bad.php', 1), UploadedFile::fake()->create('large.pdf', 10241)] as $file) {
            $this->post($this->manageUrl($profile), ['rows' => [['id' => $first->id, 'area_name' => $first->area_name, 'offer_sheet' => $file]]], ['Accept' => 'application/json'])
                ->assertStatus(422)->assertJsonValidationErrors(['rows.0.offer_sheet']);
        }
        $areaClass::updating(function () { throw new \RuntimeException('Simulated failed write'); });
        try {
            $this->post($this->manageUrl($profile), ['rows' => [['id' => $first->id, 'area_name' => $first->area_name, 'package_amount' => '200',
                'offer_sheet' => UploadedFile::fake()->create('offer.pdf', 20)]]], ['Accept' => 'application/json'])->assertStatus(500);
        } finally { $areaClass::flushEventListeners(); }
        $this->assertSame('4500.50', $first->fresh()->package_amount);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    /** @dataProvider roles */
    public function test_manage_allows_existing_same_area_assignments_across_projects($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $rows = $profile->areas->map(function ($area) { return ['id' => $area->id, 'area_name' => $area->area_name, 'package_amount' => '75']; })->all();
        $this->postJson($this->manageUrl($profile), ['rows' => $rows])->assertOk()->assertJson(['status' => true]);
        $this->assertSame(4, $profile->areas()->count());
        $this->assertSame(2, $profile->areas()->where('project_type', 'Project Rise')->count());
    }

    private function input($role = 'Area Distributor', array $changes = [])
    {
        return array_replace([
            'role' => $role, 'first_name' => 'QA', 'last_name' => 'Distributor', 'email_address' => 'qa@example.test',
            'facebook' => 'qa-profile', 'business_name' => 'Main Business', 'business_type' => 'Sole Proprietorship', 'address' => 'Test address',
            'nature_of_business' => 'LPG TRADING',
            'delivery_address' => 'Test address', 'has_service_vehicle' => '0', 'business_years_operation' => '3',
            'type' => ['Regular', 'Project Rise'],
            'area_name' => [0 => 'Area A', 2 => 'Area B'], 'joining_date' => [0 => '2026-11-19', 2 => '2026-11-20'],
            'package_amount' => [0 => '4500.50', 2 => '0'], 'area_same_business_name' => [0 => '1'],
            'area_business_name' => [0 => 'Ignore this stale value', 2 => 'Second Business'],
            'area_business_type' => [0 => 'Sole Proprietorship', 2 => 'Limited Liability Company (LLC)'],
        ], $changes);
    }

    /** @dataProvider roles */
    public function test_creates_each_area_with_its_own_amount_business_and_optional_file($role, $profileClass, $areaClass)
    {
        $this->post('/new-user', $this->input($role, [
            'area_offer_sheet' => [2 => UploadedFile::fake()->create('offer-b.pdf', 20)],
        ]), ['Accept' => 'application/json'])->assertStatus(201)->assertJson(['success' => true]);
        $profile = $profileClass::firstOrFail();
        $this->assertSame($role, User::firstOrFail()->role);
        $this->assertSame('LPG TRADING', $profile->nature_of_business);
        $this->assertCount(4, $profile->areas);
        foreach ($profile->areas as $area) {
            $isFirst = $area->area_name === 'Area A';
            $this->assertSame($isFirst ? 'Main Business' : 'Second Business', $area->business_name);
            $this->assertSame($isFirst ? 'Sole Proprietorship' : 'Limited Liability Company (LLC)', $area->business_type);
            $this->assertSame($isFirst ? '4500.50' : '0.00', $area->package_amount);
            $this->assertSame($isFirst ? '2026-11-19' : '2026-11-20', $area->joining_date);
            if ($isFirst) $this->assertNull($area->offer_sheet_path);
            else {
                Storage::disk('local')->assertExists($area->offer_sheet_path);
                $this->assertSame('offer-b.pdf', $area->offer_sheet_name);
            }
        }
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $other = $role === 'Area Distributor' ? 'provincial_distributor_areas' : 'ad_areas';
        $this->assertSame(0, DB::table($other)->count());
    }

    /** @dataProvider roles */
    public function test_areas_save_without_a_project_tag_and_optional_amount($role, $profileClass)
    {
        $this->from('/users')->post('/new-user', $this->input($role, ['type' => [], 'package_amount' => []]))
            ->assertRedirect('/users')->assertSessionHasNoErrors();
        $this->assertCount(2, $profileClass::firstOrFail()->areas);
        $this->assertNull($profileClass::firstOrFail()->areas->first()->package_amount);
        $this->assertNull($profileClass::firstOrFail()->areas->first()->project_type);
    }

    public function invalidInputs()
    {
        return [
            [['area_name' => []], 'area_name'],
            [['area_name' => ['Area A', 'Area A'], 'joining_date' => ['2026-11-19', '2026-11-19'], 'area_same_business_name' => [1, 1]], 'area_name.1'],
            [['area_name' => ['Missing Area']], 'area_name.0'],
            [['joining_date' => [0 => 'invalid', 2 => '2026-11-19']], 'joining_date.0'],
            [['package_amount' => [0 => '-1']], 'package_amount.0'],
            [['package_amount' => [0 => '10000000000']], 'package_amount.0'],
            [['package_amount' => [0 => '1.123']], 'package_amount.0'],
            [['area_business_name' => [2 => '']], 'area_business_name.2'],
            [['area_same_business_name' => ['0' => 'invalid']], 'area_same_business_name.0'],
            [['area_business_type' => [0 => 'Invalid type']], 'area_business_type.0'],
            [['area_same_business_type' => [0 => 'invalid']], 'area_same_business_type.0'],
            [['business_type' => 'Retail Shop', 'area_same_business_type' => [0 => '1']], 'business_type'],
            [['business_type' => '', 'area_same_business_type' => [0 => '1']], 'business_type'],
        ];
    }

    /** @dataProvider invalidInputs */
    public function test_invalid_area_details_are_rejected_without_creating_a_user($changes, $field)
    {
        $this->postJson('/new-user', $this->input('Area Distributor', $changes))->assertStatus(422)->assertJsonValidationErrors([$field]);
        $this->assertSame(0, User::count());
        $this->assertSame(0, AreaDistributor::count());
    }

    public function test_unsafe_or_oversized_offer_sheets_are_rejected()
    {
        foreach ([UploadedFile::fake()->create('script.php', 1), UploadedFile::fake()->create('large.pdf', 10241)] as $file) {
            $this->post('/new-user', $this->input('Provincial Distributor', ['area_offer_sheet' => [0 => $file]]), ['Accept' => 'application/json'])
                ->assertStatus(422)->assertJsonValidationErrors(['area_offer_sheet.0']);
        }
        $this->assertSame(0, User::count());
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    public function test_area_assignment_failure_rolls_back_user_profile_and_uploaded_offer_sheet()
    {
        AreaAd::creating(function () { throw new \RuntimeException('Simulated area write failure'); });
        try {
            $this->post('/new-user', $this->input('Area Distributor', [
                'area_offer_sheet' => [0 => UploadedFile::fake()->create('offer.pdf', 10)],
            ]), ['Accept' => 'application/json'])->assertStatus(500);
            $this->assertSame(0, User::count());
            $this->assertSame(0, AreaDistributor::count());
            $this->assertSame(0, AreaAd::count());
            $this->assertCount(0, Storage::disk('local')->allFiles());
        } finally { AreaAd::flushEventListeners(); }
    }

    /** @dataProvider roles */
    public function test_offer_sheet_download_requires_module_access_or_ownership($role, $profileClass)
    {
        $this->post('/new-user', $this->input($role, ['area_offer_sheet' => [0 => UploadedFile::fake()->create('offer.pdf', 10)]]), ['Accept' => 'application/json'])->assertStatus(201);
        $profile = $profileClass::firstOrFail(); $area = $profile->areas->first();
        $route = $role === 'Area Distributor' ? 'ad.areas.offer-sheet' : 'pds.areas.offer-sheet';
        $url = route($route, ['id' => $profile->id, 'area' => $area->id]);
        $this->get($url)->assertStatus(200)->assertHeader('content-disposition', 'attachment; filename=offer.pdf');
        $this->actingAs(User::firstOrFail())->get($url)->assertStatus(200);
        $outsider = new User(['role' => $role]); $outsider->id = 777;
        $this->actingAs($outsider)->get($url)->assertStatus(403);
        $limited = new User(['role' => 'Admin']); $limited->id = 999;
        $limited->access_permissions = json_encode(['distributors' => ['records' => [], 'provincial' => []]]);
        $this->actingAs($limited)->get($url)->assertStatus(403);
    }

    /** @dataProvider roles */
    public function test_same_business_type_is_resolved_from_main_type_for_checked_rows_only($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role, [
            'business_type' => 'Corporation', 'area_same_business_type' => [0 => '1', 2 => '0'],
            'area_business_type' => [0 => 'Partnership', 2 => 'Cooperative'],
        ]))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $this->assertSame('Corporation', $profile->business_type);
        foreach ($profile->areas as $area) {
            $this->assertSame($area->area_name === 'Area A' ? 'Corporation' : 'Cooperative', $area->business_type);
            $this->assertSame($area->area_name === 'Area A' ? 'Main Business' : 'Second Business', $area->business_name);
        }
    }

    /** @dataProvider roles */
    public function test_checked_business_type_does_not_require_a_submitted_disabled_select($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role, [
            'business_type' => 'Nonprofit', 'area_same_business_type' => [0 => '1', 2 => '1'], 'area_business_type' => [],
        ]))->assertStatus(201);
        foreach ($profileClass::firstOrFail()->areas as $area) $this->assertSame('Nonprofit', $area->business_type);
    }

    private function editInput($profile, array $rows)
    {
        return [
            '_method' => 'PUT', 'sync_project_areas' => '1', 'store_code' => 'QA001',
            'first_name' => 'QA', 'last_name' => 'Distributor', 'email_address' => 'qa@example.test',
            'contact_number' => '09123456789', 'business_name' => 'Updated Main Business', 'business_type' => 'Retail Shop',
            'nature_of_business' => $profile->nature_of_business,
            'has_service_vehicle' => 0, 'business_years_operation' => 3, 'address' => 'Test address',
            'delivery_address' => 'Test address', 'type' => ['Regular', 'Project Rise'], 'status' => 'Active', 'rows' => $rows,
        ];
    }

    private function editUrl($profile)
    {
        return $profile instanceof ProvincialDistributor ? '/pds/' . $profile->id : '/edit-ads/' . $profile->id;
    }

    /** @dataProvider roles */
    public function test_edit_saves_split_names_without_the_obsolete_full_name_field($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        foreach (['D. ALBIS', ''] as $middleName) {
            $input = array_replace($this->editInput($profile, []), [
                'first_name' => 'NORA', 'middle_name' => $middleName, 'last_name' => 'DE LA CRUZ',
            ]);
            $this->assertArrayNotHasKey('name', $input);
            $this->postJson($this->editUrl($profile), $input)->assertOk()->assertJson(['success' => true]);
            $expectedName = $middleName === '' ? 'NORA DE LA CRUZ' : 'NORA D. ALBIS DE LA CRUZ';
            $saved = $profile->fresh('userAds');
            $this->assertSame($expectedName, $saved->name);
            $this->assertSame($expectedName, $saved->userAds->name);
            $this->assertSame('NORA', $saved->userAds->first_name);
            $this->assertSame($middleName ?: null, $saved->userAds->middle_name);
            $this->assertSame('DE LA CRUZ', $saved->userAds->last_name);
            if ($profile instanceof ProvincialDistributor) {
                $this->assertSame($expectedName, AreaDistributor::where('user_id', $profile->user_id)->firstOrFail()->name);
            }
        }
    }

    public function test_provincial_distributor_edit_rejects_missing_or_invalid_split_names()
    {
        $this->postJson('/new-user', $this->input('Provincial Distributor'))->assertStatus(201);
        $profile = ProvincialDistributor::firstOrFail();
        foreach (['first_name', 'last_name'] as $field) {
            foreach (['', ['invalid']] as $invalid) {
                $input = array_replace($this->editInput($profile, []), [$field => $invalid]);
                $this->postJson($this->editUrl($profile), $input)->assertStatus(422)->assertJsonValidationErrors([$field]);
            }
        }
        $this->assertSame($profile->name, $profile->fresh()->name);
        $this->assertSame($profile->userAds->name, $profile->userAds->fresh()->name);
    }

    /** @dataProvider roles */
    public function test_edit_updates_nature_and_reopens_with_the_saved_choice($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $input = array_replace($this->editInput($profile, []), ['nature_of_business' => 'DISTRIBUTOR/WHOLESALER']);
        $this->post($this->editUrl($profile), $input, ['Accept' => 'application/json'])->assertOk()->assertJson(['success' => true]);
        $profile = $profile->fresh(['areas', 'trashedAreas', 'userAds']);
        $this->assertSame('DISTRIBUTOR/WHOLESALER', $profile->nature_of_business);
        $this->assertSame('Retail Shop', $profile->business_type);
        $this->assertCount(4, $profile->areas);
        if ($profile instanceof ProvincialDistributor) {
            $this->assertSame('DISTRIBUTOR/WHOLESALER', AreaDistributor::where('user_id', $profile->user_id)->firstOrFail()->nature_of_business);
        }
        $html = view('area_distributor.edit', ['ad' => $profile, 'areas' => collect(), 'centers' => collect(),
            'errors' => new \Illuminate\Support\ViewErrorBag])->render();
        $dom = new \DOMDocument; @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $selected = (new \DOMXPath($dom))->query('//select[@name="nature_of_business"]/option[@selected]');
        $this->assertSame(1, $selected->length);
        $this->assertSame('DISTRIBUTOR/WHOLESALER', $selected->item(0)->getAttribute('value'));
    }

    /** @dataProvider roles */
    public function test_edit_rejects_invalid_nature_without_changing_profile_or_areas($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $area = $profile->areas->first();
        foreach ([null, '', 'Not listed', ['BAKERY']] as $invalid) {
            $input = array_replace($this->editInput($profile, [['id' => $area->id, 'area_name' => $area->area_name, 'package_amount' => '900']]), ['nature_of_business' => $invalid]);
            $this->postJson($this->editUrl($profile), $input)->assertStatus(422)->assertJsonValidationErrors(['nature_of_business']);
        }
        $input = $this->editInput($profile, []);
        unset($input['nature_of_business']);
        $this->postJson($this->editUrl($profile), $input)->assertStatus(422)->assertJsonValidationErrors(['nature_of_business']);
        $this->assertSame('LPG TRADING', $profile->fresh()->nature_of_business);
        $this->assertSame('Main Business', $profile->fresh()->business_name);
        $this->assertSame('Sole Proprietorship', $profile->fresh()->business_type);
        $this->assertSame('4500.50', $area->fresh()->package_amount);
    }

    /** @dataProvider roles */
    public function test_edit_can_set_nature_for_existing_distributor_without_a_saved_value($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $profile->update(['nature_of_business' => null]);
        $input = array_replace($this->editInput($profile, []), ['nature_of_business' => 'BAKERY']);
        $this->postJson($this->editUrl($profile), $input)->assertOk()->assertJson(['success' => true]);
        $this->assertSame('BAKERY', $profile->fresh()->nature_of_business);
    }

    /** @dataProvider roles */
    public function test_edit_saves_area_business_details_and_preserves_then_replaces_its_offer_sheet($role, $profileClass)
    {
        $this->post('/new-user', $this->input($role, ['area_offer_sheet' => [0 => UploadedFile::fake()->create('original.pdf', 10)]]), ['Accept' => 'application/json'])->assertStatus(201);
        $profile = $profileClass::firstOrFail(); $area = $profile->areas->first(); $originalPath = $area->offer_sheet_path;
        $untouched = $profile->areas->where('area_name', 'Area B')->first();
        $rows = [4 => ['id' => $area->id, 'area_name' => $area->area_name, 'business_name' => 'Local Branch',
            'business_type' => 'Cooperative', 'package_amount' => '9000.25'],
            9 => ['area_name' => 'Area C', 'business_name' => 'Ignore stale name', 'same_business_name' => '1', 'business_type' => 'Corporation', 'package_amount' => '0']];
        $this->post($this->editUrl($profile), $this->editInput($profile, $rows), ['Accept' => 'application/json'])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame('Local Branch', $area->fresh()->business_name);
        $this->assertSame('Cooperative', $area->fresh()->business_type);
        $this->assertSame('9000.25', $area->fresh()->package_amount);
        $this->assertSame($originalPath, $area->fresh()->offer_sheet_path);
        $this->assertSame('Second Business', $untouched->fresh()->business_name);
        $created = $profile->areas()->where('area_name', 'Area C')->firstOrFail();
        $this->assertSame('Updated Main Business', $created->business_name);
        $this->assertSame('Corporation', $created->business_type);
        $this->assertSame('0.00', $created->package_amount);

        $rows = [7 => ['id' => $area->id, 'area_name' => $area->area_name, 'offer_sheet' => UploadedFile::fake()->create('replacement.pdf', 10)]];
        $this->post($this->editUrl($profile), $this->editInput($profile, $rows), ['Accept' => 'application/json'])->assertStatus(200);
        $this->assertNotSame($originalPath, $area->fresh()->offer_sheet_path);
        $this->assertSame('replacement.pdf', $area->fresh()->offer_sheet_name);
        $this->assertSame('Cooperative', $area->fresh()->business_type);
        Storage::disk('local')->assertExists($area->fresh()->offer_sheet_path);
        // Other project assignments can still share the original document.
        Storage::disk('local')->assertExists($originalPath);
        if ($profile instanceof ProvincialDistributor) {
            $this->assertSame('Cooperative', AreaAd::where('area_name', $area->area_name)->where('project_type', $area->project_type)->firstOrFail()->business_type);
        }
    }

    /** @dataProvider roles */
    public function test_invalid_edit_details_cannot_modify_the_profile_or_areas($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail(); $area = $profile->areas->first();
        foreach ([['business_type' => 'Invalid'], ['business_name' => '', 'same_business_name' => 0],
            ['offer_sheet' => UploadedFile::fake()->create('unsafe.php', 1)], ['id' => 99999]] as $invalid) {
            $row = array_replace(['id' => $area->id, 'area_name' => $area->area_name], $invalid);
            $this->post($this->editUrl($profile), $this->editInput($profile, [0 => $row]), ['Accept' => 'application/json'])->assertStatus(422);
        }
        $this->assertSame('Main Business', $profile->fresh()->business_name);
        $this->assertSame('Main Business', $area->fresh()->business_name);
        $this->assertSame('Sole Proprietorship', $area->fresh()->business_type);
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }

    /** @dataProvider roles */
    public function test_edit_copies_main_business_type_for_checked_existing_and_new_areas($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail();
        $first = $profile->areas->first();
        $second = $profile->areas->where('area_name', 'Area B')->first();
        $rows = [
            0 => ['id' => $first->id, 'area_name' => $first->area_name, 'same_business_type' => '1'],
            2 => ['id' => $second->id, 'area_name' => $second->area_name, 'same_business_type' => '0', 'business_type' => 'Nonprofit'],
            9 => ['area_name' => 'Area C', 'same_business_type' => '1', 'business_type' => 'Partnership'],
        ];
        $input = array_replace($this->editInput($profile, $rows), ['business_type' => 'Corporation']);
        $this->post($this->editUrl($profile), $input, ['Accept' => 'application/json'])->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSame('Corporation', $first->fresh()->business_type);
        $this->assertSame('Nonprofit', $second->fresh()->business_type);
        $this->assertSame('Corporation', $profile->areas()->where('area_name', 'Area C')->firstOrFail()->business_type);
        $this->assertSame('Main Business', $first->fresh()->business_name);
        $this->assertSame('4500.50', $first->fresh()->package_amount);
        $this->assertSame('Sole Proprietorship', $profile->areas()->where('area_name', 'Area A')->where('id', '!=', $first->id)->firstOrFail()->business_type);
    }

    /** @dataProvider roles */
    public function test_edit_rejects_invalid_copy_flag_or_main_type_but_keeps_legacy_profiles_editable($role, $profileClass)
    {
        $this->postJson('/new-user', $this->input($role))->assertStatus(201);
        $profile = $profileClass::firstOrFail(); $area = $profile->areas->first();
        $row = ['id' => $area->id, 'area_name' => $area->area_name, 'same_business_type' => '1'];
        $this->post($this->editUrl($profile), $this->editInput($profile, [0 => $row]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors(['business_type']);
        $row['same_business_type'] = 'invalid';
        $this->post($this->editUrl($profile), $this->editInput($profile, [0 => $row]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors(['rows.0.same_business_type']);
        $this->assertSame('Sole Proprietorship', $area->fresh()->business_type);
        $row['same_business_type'] = '0'; $row['business_type'] = 'Cooperative';
        $this->post($this->editUrl($profile), $this->editInput($profile, [0 => $row]), ['Accept' => 'application/json'])->assertStatus(200);
        $this->assertSame('Cooperative', $area->fresh()->business_type);
        $this->assertSame('Retail Shop', $profile->fresh()->business_type);
    }

    /** @dataProvider roles */
    public function test_failed_edit_keeps_previous_document_and_rolls_back_new_upload($role, $profileClass, $areaClass)
    {
        $this->post('/new-user', $this->input($role, ['area_offer_sheet' => [0 => UploadedFile::fake()->create('original.pdf', 10)]]), ['Accept' => 'application/json'])->assertStatus(201);
        $profile = $profileClass::firstOrFail(); $area = $profile->areas->first(); $originalPath = $area->offer_sheet_path;
        $areaClass::updating(function () { throw new \RuntimeException('Simulated write failure'); });
        try {
            $rows = [['id' => $area->id, 'area_name' => $area->area_name, 'business_name' => 'Changed', 'offer_sheet' => UploadedFile::fake()->create('replacement.pdf', 10)]];
            $this->post($this->editUrl($profile), $this->editInput($profile, $rows), ['Accept' => 'application/json'])->assertStatus(500);
            $this->assertSame('Main Business', $profile->fresh()->business_name);
            $this->assertSame('Main Business', $area->fresh()->business_name);
            $this->assertSame($originalPath, $area->fresh()->offer_sheet_path);
            $this->assertCount(1, Storage::disk('local')->allFiles());
        } finally { $areaClass::flushEventListeners(); }
    }
}
