<?php

namespace Tests\Feature;

use App\AreaDistributor;
use App\ProvincialDistributor;
use App\User;
use App\Services\DistributorProjectAreas;
use App\Http\Controllers\AreaDistributorController;
use App\Http\Controllers\ProvincialDistributorController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DistributorProjectAreasTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'project_area_test', 'database.connections.project_area_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        foreach (['area_distributors', 'provincial_distributors'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id'); $table->unsignedInteger('user_id');
                $table->string('name')->nullable(); $table->string('contract_attachments')->nullable();
                $table->timestamps(); $table->softDeletes();
            });
        }
        foreach (['ad_areas' => 'ad_id', 'provincial_distributor_areas' => 'provincial_distributor_id'] as $tableName => $foreignKey) {
            Schema::create($tableName, function (Blueprint $table) use ($foreignKey) {
                $table->increments('id'); $table->unsignedInteger($foreignKey);
                $table->unsignedInteger('user_id')->nullable(); $table->unsignedInteger('ad_user_id')->nullable();
                $table->string('area_name'); $table->string('project_type')->nullable(); $table->string('user_role')->nullable();
                $table->date('joining_date')->nullable(); $table->decimal('package_amount', 12, 2)->nullable();
                $table->timestamps(); $table->softDeletes();
            });
        }
    }

    public function distributorTypes()
    {
        return [[AreaDistributor::class], [ProvincialDistributor::class]];
    }

    private function profile($class, $id = 1)
    {
        $profile = new $class;
        DB::table($profile->getTable())->insert(['id' => $id, 'user_id' => $id + 10, 'name' => 'QA Distributor']);
        return $class::findOrFail($id);
    }

    private function award($profile, $name, $project = null)
    {
        return $profile->areas()->create(['area_name' => $name, 'project_type' => $project, 'joining_date' => '2026-10-01', 'package_amount' => '1234.50']);
    }

    private function saveAreas($profile, array $input)
    {
        $service = new DistributorProjectAreas;
        $changes = $service->validate(new Request($input), $profile);
        DB::transaction(function () use ($service, $profile, $changes) { $service->sync($profile, $changes); });
    }

    /** @dataProvider distributorTypes */
    public function test_edit_preserves_omitted_areas_and_amounts_and_saves_regular_and_untagged_rows($class)
    {
        $profile = $this->profile($class);
        $untagged = $this->award($profile, 'Area A');
        $regular = $this->award($profile, 'Area B', 'Regular');
        $hidden = $this->award($profile, 'Area C', 'Project Rise');
        $this->saveAreas($profile, ['type' => ['Regular'], 'rows' => [
            ['id' => $untagged->id, 'area_name' => 'Area A edited', 'project_type' => '', 'joining_date' => '2026-10-02'],
            ['id' => $regular->id, 'area_name' => 'Area B', 'project_type' => 'Regular'],
            ['id' => '', 'area_name' => 'Area D', 'project_type' => 'Regular'],
            ['id' => '', 'area_name' => 'Area E', 'project_type' => ''],
        ]]);
        $this->assertSame(5, $profile->areas()->count());
        $this->assertSame('Area A edited', $untagged->fresh()->area_name);
        $this->assertNull($untagged->fresh()->project_type);
        $this->assertSame('1234.50', $untagged->fresh()->package_amount);
        $this->assertNull($hidden->fresh()->deleted_at);
        $this->assertSame('Regular', $profile->areas()->where('area_name', 'Area D')->first()->project_type);
        $this->assertNull($profile->areas()->where('area_name', 'Area E')->first()->project_type);
        $this->saveAreas($profile, ['removed_area_ids' => [$regular->id]]);
        $this->assertSame(4, $profile->areas()->count());
        $this->assertNotNull($regular->fresh()->deleted_at);
        $otherTable = $profile instanceof ProvincialDistributor ? 'ad_areas' : 'provincial_distributor_areas';
        $this->assertSame(0, DB::table($otherTable)->count());
    }

    /** @dataProvider distributorTypes */
    public function test_unchanged_assignment_survives_removing_its_partner_tag($class)
    {
        $profile = $this->profile($class);
        $award = $this->award($profile, 'Area A', 'Project Rise');
        $this->saveAreas($profile, ['type' => [], 'rows' => [['id' => $award->id, 'area_name' => 'Area A', 'project_type' => 'Project Rise']]]);
        $this->assertSame('Project Rise', $award->fresh()->project_type);
        $this->assertNull($award->fresh()->deleted_at);
    }

    /** @dataProvider distributorTypes */
    public function test_package_amount_round_trips_for_existing_and_new_areas($class)
    {
        $profile = $this->profile($class);
        $award = $this->award($profile, 'Area A', 'Project Rise');
        $this->saveAreas($profile, ['rows' => [
            ['id' => $award->id, 'area_name' => 'Area A', 'package_amount' => '2500.75'],
            ['area_name' => 'Area B', 'package_amount' => '4500.25'],
        ]]);
        $this->assertSame('2500.75', $award->fresh()->package_amount);
        $this->assertSame('Project Rise', $award->fresh()->project_type);
        $this->assertSame('4500.25', $profile->areas()->where('area_name', 'Area B')->first()->package_amount);
        $this->saveAreas($profile, ['rows' => [['id' => $award->id, 'area_name' => 'Area A', 'package_amount' => '0']]]);
        $this->assertSame('0.00', $award->fresh()->package_amount);
        $this->saveAreas($profile, ['rows' => [['id' => $award->id, 'area_name' => 'Area A']]]);
        $this->assertSame('0.00', $award->fresh()->package_amount);
        $this->saveAreas($profile, ['rows' => [['id' => $award->id, 'area_name' => 'Area A', 'package_amount' => '']]]);
        $this->assertNull($award->fresh()->package_amount);
        $this->assertSame('Project Rise', $award->fresh()->project_type);
    }

    /** @dataProvider distributorTypes */
    public function test_invalid_package_amount_does_not_change_saved_areas($class)
    {
        $profile = $this->profile($class);
        $award = $this->award($profile, 'Area A');
        foreach (['-1', 'invalid', '10000000000'] as $amount) {
            try {
                $this->saveAreas($profile, ['rows' => [['id' => $award->id, 'area_name' => 'Area A', 'package_amount' => $amount]]]);
                $this->fail('Invalid amount accepted');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('rows.0.package_amount', $error->errors());
            }
        }
        $this->assertSame('1234.50', $award->fresh()->package_amount);
    }

    /** @dataProvider distributorTypes */
    public function test_other_distributors_rows_cannot_be_edited_or_removed($class)
    {
        $profile = $this->profile($class);
        $other = $this->profile($class, 2);
        $award = $this->award($other, 'Other area');
        foreach ([['rows' => [['id' => $award->id, 'area_name' => 'Changed']]], ['removed_area_ids' => [$award->id]]] as $input) {
            try { $this->saveAreas($profile, $input); $this->fail('Foreign area accepted'); }
            catch (ValidationException $error) { $this->assertNotEmpty($error->errors()); }
        }
        $this->assertSame('Other area', $award->fresh()->area_name);
        $this->assertNull($award->fresh()->deleted_at);
    }

    /** @dataProvider distributorTypes */
    public function test_new_assignment_requires_selected_tag_and_valid_date($class)
    {
        $profile = $this->profile($class);
        foreach ([['project_type' => 'Project Rise'], ['joining_date' => '2026-02-31']] as $invalid) {
            try { $this->saveAreas($profile, ['rows' => [array_merge(['area_name' => 'Area A'], $invalid)]]); $this->fail('Invalid area accepted'); }
            catch (ValidationException $error) { $this->assertNotEmpty($error->errors()); }
        }
        $this->assertSame(0, $profile->areas()->count());
    }

    /** @dataProvider distributorTypes */
    public function test_manage_areas_keeps_existing_project_assignment($class)
    {
        $profile = $this->profile($class);
        $award = $this->award($profile, 'Area A', 'Project Rise');
        $controller = $profile instanceof ProvincialDistributor ? new ProvincialDistributorController : new AreaDistributorController;
        $response = $controller->updateAreas(new Request(['rows' => [[
            'id' => $award->id, 'area_name' => 'Area A', 'joining_date' => '2026-10-01', 'package_amount' => '2000.50',
        ]]]), $profile->id);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Project Rise', $award->fresh()->project_type);
        $this->assertSame('2000.50', $award->fresh()->package_amount);
    }

    public function test_project_tags_support_legacy_values_without_restoring_cleared_tags()
    {
        $user = new User;
        $user->project_tag = 'Project Rise, Regular';
        $this->assertSame(['Project Rise', 'Regular'], DistributorProjectAreas::projectTags($user));
        $user->type = '[]';
        $this->assertSame([], DistributorProjectAreas::projectTags($user));
        $user->type = '["Project Genesis", "Regular"]';
        $this->assertSame(['Project Genesis', 'Regular'], DistributorProjectAreas::projectTags($user));
    }

    /** @dataProvider distributorTypes */
    public function test_edit_form_loads_all_active_areas_with_their_selections($class)
    {
        $profile = $this->profile($class);
        $this->award($profile, 'Area A');
        $this->award($profile, 'Area B', 'Regular')->update(['package_amount' => 0]);
        $this->award($profile, 'Area C', 'Project Rise')->update(['package_amount' => null]);
        $user = new User; $user->type = '["Regular"]';
        $profile->setRelation('userAds', $user);
        $profile->load('areas', 'trashedAreas');
        $html = view('area_distributor.edit', [
            'ad' => $profile, 'areas' => collect(), 'centers' => collect(), 'errors' => new ViewErrorBag,
            'updateRoute' => $profile instanceof ProvincialDistributor ? 'pds.update' : 'edit-ads',
        ])->render();
        $dom = new \DOMDocument; @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(3, $xpath->query('//*[@data-project-row]')->length);
        $this->assertSame(1, $xpath->query('//input[contains(@class,"ad-row-project-value") and @value="Regular"]')->length);
        $this->assertSame(1, $xpath->query('//input[contains(@class,"ad-row-project-value") and @value="Project Rise"]')->length);
        $this->assertSame(3, $xpath->query('//select[contains(@class,"ad-row-area-name")]/option[@selected]')->length);
        $amountInputs = $xpath->query('//input[contains(@class,"ad-row-package-amount")]');
        $this->assertSame(3, $amountInputs->length);
        $this->assertSame('1234.50', $amountInputs->item(0)->getAttribute('value'));
        $this->assertSame('0.00', $amountInputs->item(1)->getAttribute('value'));
        $this->assertSame('', $amountInputs->item(2)->getAttribute('value'));
        if (getenv('DISTRIBUTOR_AREAS_UI') === '1') {
            $file = $profile instanceof ProvincialDistributor ? 'pd-project-areas.html' : 'ad-project-areas.html';
            file_put_contents(base_path('tmp/'.$file), '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="../public/design/assets/css/styles.css"></head><body>'.$html.'</body></html>');
        }
    }

    /** @dataProvider distributorTypes */
    public function test_edit_form_restores_submitted_package_amount_after_validation_error($class)
    {
        $profile = $this->profile($class);
        $award = $this->award($profile, 'Area A');
        $profile->setRelation('userAds', new User);
        $profile->load('areas', 'trashedAreas');
        app('request')->setLaravelSession(app('session.store'));
        session()->flashInput([
            'distributor_form' => $class.':'.$profile->id,
            'rows' => [['id' => $award->id, 'area_name' => 'Area A', 'package_amount' => '4321.09']],
        ]);
        $html = view('area_distributor.edit', ['ad' => $profile, 'areas' => collect(), 'centers' => collect(), 'errors' => new ViewErrorBag])->render();
        $dom = new \DOMDocument; @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $amount = (new \DOMXPath($dom))->query('//input[contains(@class,"ad-row-package-amount")]')->item(0);
        $this->assertSame('4321.09', $amount->getAttribute('value'));
        $this->assertSame('1234.50', $award->fresh()->package_amount);
    }
}
