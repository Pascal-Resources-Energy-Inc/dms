<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdAwardedAreasReportTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'database.default' => 'award_report_test', 'database.connections.award_report_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, type TEXT, project_tag TEXT, created_at TEXT)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, store_code TEXT, ad_reference TEXT,
            name TEXT, business_name TEXT, delivery_address TEXT, contact_number TEXT, location_barangay TEXT,
            location_city TEXT, location_province TEXT, location_region TEXT, nature_of_business TEXT, business_type TEXT)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, project_type TEXT, business_name TEXT,
            package_amount NUMERIC, joining_date TEXT, deleted_at TEXT, business_type TEXT)');
        DB::statement('CREATE TABLE areas (id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE area_geographic_coverages (id INTEGER PRIMARY KEY, area_id INTEGER, barangay TEXT, city_municipality TEXT, province TEXT, region TEXT)');
        foreach ([1 => 'Area Distributor', 2 => 'Area Distributor', 3 => 'Provincial Distributor', 4 => 'Mega Dealer'] as $id => $role) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Partner '.$id, 'role' => $role, 'type' => $id === 1 ? '["Project Rise", "Regular"]' : null,
                'project_tag' => $id === 3 ? 'PD only tag' : 'Project Genesis', 'created_at' => '2026-01-01']);
            DB::table('area_distributors')->insert(['id' => $id, 'user_id' => $id, 'store_code' => 'SHARED', 'ad_reference' => 'REF-'.$id,
                'name' => 'Partner '.$id, 'business_name' => 'Main business '.$id, 'delivery_address' => 'Delivery address '.$id,
                'nature_of_business' => $id === 1 ? 'LPG TRADING' : null, 'business_type' => $id === 1 ? 'Sole Proprietorship' : null,
                'contact_number' => '09123456789', 'location_barangay' => 'Home barangay '.$id, 'location_city' => 'Home city '.$id,
                'location_province' => 'Home province '.$id, 'location_region' => 'Region V (Bicol Region)']);
        }
        DB::table('areas')->insert([
            ['id' => 1, 'name' => 'North', 'deleted_at' => null], ['id' => 2, 'name' => ' NORTH ', 'deleted_at' => null],
            ['id' => 3, 'name' => 'South', 'deleted_at' => null], ['id' => 4, 'name' => 'north', 'deleted_at' => '2025-01-01'],
        ]);
        DB::table('area_geographic_coverages')->insert([
            ['id' => 1, 'area_id' => 1, 'barangay' => 'Barangay Alpha', 'city_municipality' => 'City One', 'province' => 'Province One', 'region' => 'Region XIII (Caraga)'],
            ['id' => 2, 'area_id' => 2, 'barangay' => 'Barangay Beta', 'city_municipality' => 'City One', 'province' => 'Province One', 'region' => 'Region XIII (Caraga)'],
            ['id' => 3, 'area_id' => 3, 'barangay' => '', 'city_municipality' => 'City South', 'province' => 'Province South', 'region' => 'Region VII'],
            ['id' => 4, 'area_id' => 4, 'barangay' => 'Old coverage', 'city_municipality' => 'Old city', 'province' => 'Old province', 'region' => 'Region I'],
        ]);
        $this->award(1, 1, 'North', ['business_name' => 'North Branch', 'business_type' => 'Corporation', 'package_amount' => 100, 'project_type' => 'Wrong old tag']);
        $this->award(2, 1, 'South', ['business_name' => 'South Branch', 'business_type' => 'Partnership', 'package_amount' => 0]);
        $this->award(3, 1, 'East', ['package_amount' => null, 'deleted_at' => '2026-02-01']);
        $this->award(4, 2, 'West', ['business_name' => 'West Branch']);
        $this->award(5, 1, 'North', ['business_name' => 'North other project', 'project_type' => 'Regular', 'package_amount' => 200]);
        $this->award(6, 3, 'North');
        $this->award(7, 4, 'North');
        $admin = new User(['role' => 'Admin']); $admin->id = 100;
        $this->actingAs($admin);
        $this->viewRoot = storage_path('framework/testing/awarded-report-'.uniqid());
        File::makeDirectory($this->viewRoot.'/layouts', 0755, true);
        File::put($this->viewRoot.'/layouts/header.blade.php', '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">@yield("css")</head><body>@yield("content")</body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        // The report fixture does not render the unrelated navigation counters.
        app('events')->forget('composing: layouts.header');
        app('view')->share('errors', new ViewErrorBag);
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function award($id, $partner, $name, array $changes = [])
    {
        DB::table('ad_areas')->insert(array_replace(['id' => $id, 'ad_id' => $partner, 'area_name' => $name,
            'joining_date' => '2026-01-10', 'package_amount' => 50, 'business_name' => null, 'project_type' => null, 'deleted_at' => null], $changes));
    }

    private function request(array $filters = [])
    {
        $request = Request::create('/reports/ad-awarded-areas', 'GET', $filters);
        $this->app->instance('request', $request);
        Paginator::currentPageResolver(function () use ($request) { return $request->input('page', 1); });
        Paginator::currentPathResolver(function () { return '/reports/ad-awarded-areas'; });
        return $request;
    }

    private function report(array $filters = [])
    {
        return (new ReportController)->adAwardedAreasReport($this->request($filters))->getData();
    }

    public function test_one_row_per_saved_ad_assignment_with_correct_area_details_and_role_scope()
    {
        $data = $this->report();
        $this->assertSame(['assignments' => 5, 'partners' => 2, 'disengaged' => 1], $data['summary']);
        $rows = $data['awards']->getCollection()->keyBy('id');
        $this->assertCount(5, $rows);
        $this->assertSame('North Branch', $rows[1]->business_name);
        $this->assertSame('South Branch', $rows[2]->business_name);
        $this->assertSame('Main business 1', $rows[3]->business_name);
        $this->assertSame('LPG TRADING', $rows[1]->nature_of_business);
        $this->assertSame('Corporation', $rows[1]->business_type);
        $this->assertSame('Partnership', $rows[2]->business_type);
        $this->assertSame('Sole Proprietorship', $rows[3]->business_type);
        $this->assertSame('—', $rows[4]->nature_of_business);
        $this->assertSame('—', $rows[4]->business_type);
        $this->assertSame('PHP 100.00', $rows[1]->package_amount_display);
        $this->assertSame('PHP 0.00', $rows[2]->package_amount_display);
        $this->assertSame('—', $rows[3]->package_amount_display);
        $this->assertSame('Project Rise, Regular', $rows[1]->project_type);
        $this->assertFalse($data['projectTypes']->contains('PD only tag'));
        $this->assertSame('Feb 01, 2026', $rows[3]->disengagement_date_display);
        $this->assertSame('Jan 10, 2026', $rows[1]->awarded_date_display);
    }

    public function test_coverage_is_merged_without_duplicate_awards_and_missing_fields_do_not_use_home_address()
    {
        $rows = $this->report()['awards']->getCollection()->keyBy('id');
        $this->assertStringContainsString('Barangay Alpha', $rows[1]->barangay);
        $this->assertStringContainsString('Barangay Beta', $rows[1]->barangay);
        $this->assertStringNotContainsString('Old coverage', $rows[1]->barangay);
        $this->assertSame('City One', $rows[1]->city);
        $this->assertSame('Area coverage', $rows[1]->location_source);
        $this->assertSame('Mindanao', $rows[1]->ph_area);
        $this->assertSame('—', $rows[2]->barangay);
        $this->assertSame('City South', $rows[2]->city);
        $this->assertSame('Visayas', $rows[2]->ph_area);
        $this->assertSame('Home barangay 1', $rows[3]->barangay);
        $this->assertSame('Distributor address', $rows[3]->location_source);
    }

    public function test_search_and_combined_filters_use_the_values_shown_in_the_report()
    {
        $this->assertSame([1], $this->report(['search' => 'North Branch'])['awards']->pluck('id')->all());
        $this->assertSame([3], $this->report(['search' => 'Home barangay 1'])['awards']->pluck('id')->all());
        $this->assertSame([1, 5], $this->report(['search' => 'Barangay Beta'])['awards']->pluck('id')->all());
        $this->assertSame([2], $this->report(['assignment_status' => 'active', 'project_type' => 'Project Rise', 'package_amount' => '0'])['awards']->pluck('id')->all());
        $this->assertSame(1, $this->report(['assignment_status' => 'disengaged'])['summary']['assignments']);
        $this->assertSame(4, $this->report(['assignment_status' => 'active'])['summary']['assignments']);
        $this->assertSame(0, $this->report(['package_amount' => '999'])['awards']->total());
    }

    public function test_project_filters_match_current_user_tags_in_all_supported_formats()
    {
        $this->assertSame([4], $this->report(['project_type' => 'Project Genesis'])['awards']->pluck('id')->all());
        foreach (['["Project Rise"]', '"Project Rise"', 'Project Rise, Regular'] as $tags) {
            DB::table('users')->where('id', 1)->update(['type' => $tags]);
            $this->assertSame(4, $this->report(['project_type' => 'Project Rise'])['awards']->total());
        }
        DB::table('users')->where('id', 1)->update(['type' => '[]']);
        $this->assertSame(0, $this->report(['project_type' => 'Project Rise'])['awards']->total());
        $this->assertSame([4], $this->report(['project_type' => 'Project Genesis'])['awards']->pluck('id')->all());
    }

    public function test_active_area_counts_are_distinct_and_stable_across_pages_and_status_filters()
    {
        for ($id = 10; $id < 22; $id++) $this->award($id, 1, 'Territory '.$id);
        foreach ([1, 2] as $page) {
            $data = $this->report(['per_page' => 10, 'page' => $page]);
            $this->assertSame(17, $data['awards']->total());
            foreach ($data['awards']->where('partner_id', 1) as $row) $this->assertSame(14, (int) $row->active_award_count);
        }
        $this->assertSame(14, (int) $this->report(['assignment_status' => 'disengaged'])['awards']->first()->active_award_count);
    }

    public function test_grouping_keeps_different_distributors_with_the_same_code_separate()
    {
        $html = (new ReportController)->adAwardedAreasReport($this->request())->render();
        $dom = new \DOMDocument; @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(2, $xpath->query('//td[contains(@class,"aar-partner-code-cell")]')->length);
        $this->assertSame(0, $xpath->query('//td[@rowspan and (text()="North Branch" or text()="South Branch")]')->length);
        $this->assertStringContainsString('2 active areas', $html);
        $this->assertSame(18, $xpath->query('//table/thead/tr/th')->length);
        $this->assertSame('LPG TRADING', $xpath->query('//table/tbody/tr[1]/td[5]')->item(0)->textContent);
        $this->assertSame('Corporation', $xpath->query('//table/tbody/tr[1]/td[6]')->item(0)->textContent);
        $this->assertSame('Partnership', $xpath->query('//table/tbody/tr[2]/td[3]')->item(0)->textContent);
        $this->assertSame('LPG TRADING', $xpath->query('//article[contains(@class,"aar-mobile-card")][1]//div[small="Nature of Business"]/span')->item(0)->textContent);
        $this->assertSame('Corporation', $xpath->query('//article[contains(@class,"aar-mobile-card")][1]//div[small="Business Type"]/span')->item(0)->textContent);
        if (getenv('AWARDED_AREAS_UI') === '1') {
            for ($id = 10; $id < 35; $id++) $this->award($id, 1, 'Territory '.$id);
            File::put(storage_path('app/qa-awarded-areas-report/report.html'), (new ReportController)->adAwardedAreasReport($this->request(['per_page' => 25]))->render());
        }
    }

    public function test_csv_matches_filtered_report_and_includes_rows_beyond_current_page()
    {
        for ($id = 10; $id < 22; $id++) $this->award($id, 1, 'Territory '.$id, ['business_name' => '=1+1']);
        $filters = ['per_page' => 10, 'page' => 2, 'assignment_status' => 'active', 'project_type' => 'Project Rise'];
        $response = (new ReportController)->exportAdAwardedAreas($this->request($filters));
        ob_start(); $response->sendContent(); $csv = ob_get_clean();
        $lines = array_map('str_getcsv', explode("\n", trim(substr($csv, 3))));
        $this->assertCount(16, $lines);
        $first = array_combine($lines[0], $lines[1]);
        $second = array_combine($lines[0], $lines[2]);
        $later = array_combine($lines[0], $lines[4]);
        $this->assertSame('North Branch', $first['Business / Store Name']);
        $this->assertSame('LPG TRADING', $first['Nature of Business']);
        $this->assertSame('Corporation', $first['Business Type']);
        $this->assertSame('Partnership', $second['Business Type']);
        $this->assertSame('Sole Proprietorship', $later['Business Type']);
        $this->assertSame('100.00', $first['Package Amount (PHP)']);
        $this->assertSame('Project Rise, Regular', $first['Project Affiliation']);
        $this->assertSame('Area coverage', $first['Location Source']);
        $this->assertSame("'=1+1", $later['Business / Store Name']);
        $this->assertSame(21, count($lines[4]));
    }

    public function test_invalid_filters_return_validation_errors_for_report_and_export()
    {
        foreach ([['search' => ['bad']], ['project_type' => ['bad']], ['assignment_status' => 'wrong'], ['package_amount' => '-1'], ['per_page' => 'bad'], ['page' => 0]] as $invalid) {
            foreach (['adAwardedAreasReport', 'exportAdAwardedAreas'] as $method) {
                try { (new ReportController)->{$method}($this->request($invalid)); $this->fail('Invalid filters accepted'); }
                catch (ValidationException $exception) { $this->assertArrayHasKey(key($invalid), $exception->errors()); }
            }
        }
    }

    public function test_report_handles_missing_coverage_table_and_legacy_area_fields()
    {
        Schema::drop('area_geographic_coverages');
        DB::statement('ALTER TABLE areas ADD COLUMN province TEXT');
        DB::statement('ALTER TABLE areas ADD COLUMN region TEXT');
        DB::table('areas')->where('id', 2)->update(['province' => 'Legacy province', 'region' => 'Region 12']);
        $rows = $this->report()['awards']->getCollection()->keyBy('id');
        $this->assertSame('Legacy province', $rows[1]->province);
        $this->assertSame('—', $rows[1]->barangay);
        $this->assertSame('Area record', $rows[1]->location_source);
        $this->assertSame('Mindanao', $rows[1]->ph_area);
    }

    public function test_region_classification_does_not_confuse_caraga_with_car_or_numeric_region_thirteen_with_three()
    {
        foreach (['Caraga' => 'Mindanao', 'Region 13' => 'Mindanao', 'Region XI' => 'Mindanao', 'CAR' => 'Luzon', 'MIMAROPA Region' => 'Luzon', 'Region VI' => 'Visayas', 'Region V • Region VII' => 'Luzon • Visayas'] as $region => $expected) {
            DB::table('area_distributors')->where('id', 2)->update(['location_region' => $region]);
            $this->assertSame($expected, $this->report(['search' => 'West Branch'])['awards']->first()->ph_area);
        }
    }
}
