<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MfiUserCreationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32)),
            'audit.enabled' => false, 'database.default' => 'mfi_user_test',
            'database.connections.mfi_user_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            foreach (['first_name', 'middle_name', 'last_name', 'name', 'email', 'password',
                'address', 'warehouse', 'territory', 'delivery_address', 'role', 'mfi_type',
                'type', 'project_tag', 'birthdate', 'age', 'mothers_name', 'designation',
                'employee_number', 'department', 'approval_types'] as $column) {
                $table->string($column)->nullable();
            }
            $table->boolean('is_distributor_personnel')->default(false);
            $table->timestamps();
        });
        Schema::create('centers', function (Blueprint $table) {
            $table->increments('id'); $table->string('name'); $table->string('mfi');
        });
        Schema::create('mfis', function (Blueprint $table) {
            $table->increments('id'); $table->string('name');
        });
        DB::table('mfis')->insert([['name' => 'SEDP'], ['name' => 'ASHI'], ['name' => 'QA MFI']]);
        Schema::create('area_distributors', function (Blueprint $table) {
            $table->increments('id'); $table->string('status')->nullable();
        });
        Schema::create('ad_areas', function (Blueprint $table) {
            $table->increments('id'); $table->unsignedInteger('ad_id');
            $table->string('area_name'); $table->softDeletes();
        });
        DB::table('centers')->insert([
            ['name' => 'QA Center One', 'mfi' => 'SEDP'],
            ['name' => 'QA Center Two', 'mfi' => 'SEDP'],
            ['name' => 'QA ASHI Center', 'mfi' => 'ASHI'],
        ]);
        $this->withoutMiddleware(\App\Http\Middleware\EnsureModuleAccess::class);
        $this->actingAs(new User(['name' => 'QA Admin', 'role' => 'Admin']));
    }

    private function mfiInput(array $changes = [])
    {
        return array_replace([
            'role' => 'MFI', 'first_name' => 'QA', 'middle_name' => '', 'last_name' => 'MFI',
            'email_address' => 'mfi@example.test', 'mfi_type' => 'SEDP',
            'sedp_center' => ['QA Center One', 'QA Center Two'], 'designation' => 'CDW',
            'employee_number' => '', 'department' => '', 'delivery_address' => '',
        ], $changes);
    }

    public function test_creates_mfi_user_with_multiple_centers()
    {
        $response = $this->from('/users')->post('/new-user', $this->mfiInput());
        $response->assertRedirect('/users')->assertSessionHasNoErrors();
        $user = User::where('email', 'mfi@example.test')->firstOrFail();
        $this->assertSame('MFI', $user->role);
        $this->assertSame('SEDP', $user->mfi_type);
        $this->assertSame('QA Center One, QA Center Two', $user->territory);
        $this->assertSame('CDW', $user->designation);
        $this->assertSame(0, DB::table('area_distributors')->count());
    }

    public function test_json_submission_saves_and_returns_success_after_mobile_verification()
    {
        $this->withSession(['mobile_otp' => [
            'number' => '09123456789', 'verified' => true, 'expires_at' => time() + 300,
        ]])->postJson('/new-user', $this->mfiInput(['contact_number' => '09123456789']))
            ->assertStatus(201)->assertJson(['success' => true, 'redirect' => url('/users')])
            ->assertSessionMissing('mobile_otp');
        $this->assertSame(1, User::where('role', 'MFI')->count());
    }

    public function test_registered_mfi_names_and_padded_center_names_can_be_submitted()
    {
        DB::table('centers')->insert(['name' => ' QA Custom Center ', 'mfi' => ' qa mfi ']);
        $this->postJson('/new-user', $this->mfiInput([
            'mfi_type' => 'qa mfi', 'sedp_center' => [' QA Custom Center ', 'QA Custom Center'],
        ]))->assertStatus(201);
        $user = User::where('email', 'mfi@example.test')->firstOrFail();
        $this->assertSame('QA MFI', $user->mfi_type);
        $this->assertSame('QA Custom Center', $user->territory);
    }

    public function test_center_from_another_mfi_returns_an_actionable_error_without_saving()
    {
        $this->postJson('/new-user', $this->mfiInput(['sedp_center' => ['QA ASHI Center']]))
            ->assertStatus(422)->assertJsonValidationErrors(['sedp_center']);
        $this->assertSame(0, User::count());
    }

    public function test_missing_mfi_center_and_designation_are_reported_together()
    {
        $this->postJson('/new-user', $this->mfiInput([
            'mfi_type' => '', 'sedp_center' => [], 'designation' => '',
        ]))->assertStatus(422)->assertJsonValidationErrors(['mfi_type', 'sedp_center', 'designation']);
        $this->assertSame(0, User::count());
    }

    public function test_duplicate_email_does_not_create_another_user()
    {
        $this->postJson('/new-user', $this->mfiInput())->assertStatus(201);
        $this->postJson('/new-user', $this->mfiInput())
            ->assertStatus(422)->assertJsonValidationErrors(['email_address']);
        $this->assertSame(1, User::count());
    }

    public function test_expired_mobile_verification_can_be_corrected_and_resubmitted()
    {
        $input = $this->mfiInput(['contact_number' => '09123456789']);
        $this->withSession(['mobile_otp' => [
            'number' => '09123456789', 'verified' => true, 'expires_at' => time() - 1,
        ]])->postJson('/new-user', $input)
            ->assertStatus(422)->assertJsonValidationErrors(['contact_number']);
        $this->assertSame(0, User::count());
        $this->withSession(['mobile_otp' => [
            'number' => '09123456789', 'verified' => true, 'expires_at' => time() + 300,
        ]])->postJson('/new-user', $input)->assertStatus(201);
        $this->assertSame(1, User::count());
    }

    public function test_regular_form_failure_keeps_mfi_values_for_retry()
    {
        $this->from('/users')->post('/new-user', $this->mfiInput([
            'user_form' => 'new_user', 'designation' => '',
        ]))->assertRedirect('/users')->assertSessionHasErrors('designation');
        $this->assertSame('MFI', session()->getOldInput('role'));
        $this->assertSame(['QA Center One', 'QA Center Two'], session()->getOldInput('sedp_center'));
        $this->assertSame(0, User::count());
    }
}
