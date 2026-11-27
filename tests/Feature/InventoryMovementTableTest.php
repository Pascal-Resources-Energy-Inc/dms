<?php

namespace Tests\Feature;

use App\InventoryTransfer;
use App\Product;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryMovementTableTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        $sqlite = ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];
        config(['audit.enabled' => false, 'app.key' => 'base64:' . base64_encode(str_repeat('t', 32)),
            'database.default' => 'movement_table_test', 'database.connections.movement_table_test' => $sqlite,
            'database.connections.admin_crms' => $sqlite, 'database.connections.admin_crms2' => $sqlite]);
        foreach (['movement_table_test', 'admin_crms', 'admin_crms2'] as $connection) DB::purge($connection);
        foreach ([new User, new \App\AreaDistributor, new \App\ProvincialDistributor, new \App\AreaAd,
            new \App\ProvincialDistributorArea, new Product, new InventoryTransfer,
            new \App\AdPurchaseOrder, new \App\AdPurchaseOrderItem] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->increments('id');
                foreach (array_diff(array_unique($model->getFillable()), ['id', 'created_at', 'updated_at', 'deleted_at']) as $column) $table->string($column)->nullable();
                $table->timestamps(); $table->softDeletes();
            });
        }
        $this->viewRoot = storage_path('framework/testing/inventory-table-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', '@yield("css") @yield("content") @yield("javascript")');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function distributor($role)
    {
        $user = User::create(['role' => $role, 'name' => 'QA Distributor', 'email' => uniqid() . '@example.test']);
        $class = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? \App\ProvincialDistributor::class : \App\AreaDistributor::class;
        $profile = $class::create(['user_id' => $user->id, 'name' => 'QA Distributor']);
        foreach (['Zone A', 'Zone B'] as $area) $profile->areas()->create(['area_name' => $area]);
        return $user;
    }

    private function movement(User $user, array $attributes = [])
    {
        return InventoryTransfer::create($attributes + ['ad_user_id' => $user->id, 'product_id' => 1,
            'item_name' => '330g LPG Refill', 'sku' => 'REFILL', 'qty' => 10, 'to_area' => 'Zone A',
            'movement_type' => 'in', 'transfer_date' => '2026-11-27', 'approval_status' => 'Approved']);
    }

    public function roles()
    {
        return [[User::ROLE_AREA_DISTRIBUTOR], [User::ROLE_PROVINCIAL_DISTRIBUTOR]];
    }

    private function draw($route, array $parameters = [])
    {
        $this->app->forgetInstance('datatables.request');
        return $this->getJson(route($route) . '?' . http_build_query($parameters + ['draw' => 4, 'start' => 0, 'length' => 10]));
    }

    /** @dataProvider roles */
    public function test_history_paginates_and_sorts_on_the_server_without_showing_other_accounts($role)
    {
        $user = $this->distributor($role);
        for ($i = 1; $i <= 35; $i++) $this->movement($user, ['reference_no' => sprintf('MOV-%03d', $i)]);
        $this->movement($this->distributor($role), ['reference_no' => 'FOREIGN']);
        $this->movement($user, ['reference_no' => 'DELETED'])->delete();
        $this->actingAs($user);
        $params = ['start' => 10, 'order' => [['column' => 5, 'dir' => 'asc']]];
        $json = $this->draw('inventory-transfers.data', $params)->assertOk()->json();
        $this->assertArrayNotHasKey('error', $json);
        $this->assertSame(4, $json['draw']); $this->assertEquals(35, $json['recordsTotal']);
        $this->assertEquals(35, $json['recordsFiltered']); $this->assertCount(10, $json['data']);
        $this->assertSame('MOV-011', $json['data'][0]['reference_no']);
        $this->assertSame('MOV-020', $json['data'][9]['reference_no']);
        $this->assertCount(5, $this->draw('inventory-transfers.data', ['start' => 30])->assertOk()->json('data'));
        $this->assertSame('MOV-035', $this->draw('inventory-transfers.data', ['order' => [['column' => 5, 'dir' => 'desc']]])->json('data.0.reference_no'));
    }

    public function test_search_and_filters_match_any_product_in_a_group_and_details_keep_all_products()
    {
        $user = $this->distributor(User::ROLE_AREA_DISTRIBUTOR);
        $this->actingAs($user);
        $first = $this->movement($user, ['batch_number' => 'ONE', 'reference_no' => 'GROUPED', 'item_name' => '<unsafe product>']);
        $this->movement($user, ['batch_number' => 'ONE', 'reference_no' => 'GROUPED', 'product_id' => 2, 'sku' => 'CHILD-ONLY', 'item_name' => 'Second product']);
        $this->movement($user, ['reference_no' => 'OTHER', 'transfer_date' => '2026-10-01']);
        $json = $this->draw('inventory-transfers.data', ['search' => ['value' => 'CHILD-ONLY']])->assertOk()->json();
        $this->assertEquals(2, $json['recordsTotal']); $this->assertEquals(1, $json['recordsFiltered']);
        $this->assertCount(1, $json['data']);
        $this->assertSame('GROUPED', $json['data'][0]['reference_no']);
        $this->assertContains('&lt;unsafe product&gt;', $json['data'][0]['products']);
        $this->assertContains('Second product', $json['data'][0]['action']);
        $this->assertNotContains('<unsafe product>', $json['data'][0]['products']);
        $this->assertEquals(1, $this->draw('inventory-transfers.data', ['product_id' => 2, 'area' => 'Zone A', 'date_from' => '2026-11-01'])->json('recordsFiltered'));
        $this->assertCount(0, $this->draw('inventory-transfers.data', ['movement_type' => 'out'])->json('data'));
    }

    /** @dataProvider roles */
    public function test_stock_table_filters_orders_numeric_quantities_and_paginates_on_the_server($role)
    {
        $user = $this->distributor($role);
        $this->actingAs($user);
        for ($i = 0; $i < 28; $i++) {
            $this->movement($user, ['product_id' => $i + 1, 'item_name' => sprintf('Product %02d', $i), 'sku' => 'SKU-' . $i, 'qty' => $i]);
        }
        $this->movement($this->distributor($role), ['item_name' => 'Foreign product', 'qty' => 999]);
        $json = $this->draw('inventory-transfers.stock-data', ['order' => [['column' => 4, 'dir' => 'desc']]])->assertOk()->json();
        $this->assertSame(4, $json['draw']); $this->assertEquals(28, $json['recordsTotal']);
        $this->assertCount(10, $json['data']); $this->assertEquals(27, $json['data'][0]['available_qty']);
        $this->assertEquals(18, $json['data'][9]['available_qty']);
        $this->assertCount(8, $this->draw('inventory-transfers.stock-data', ['start' => 20])->json('data'));
        $low = $this->draw('inventory-transfers.stock-data', ['stock_status' => 'low'])->assertOk()->json();
        $this->assertEquals(10, $low['recordsFiltered']); $this->assertEquals(28, $low['recordsTotal']);
        $this->assertEquals(1, $this->draw('inventory-transfers.stock-data', ['search' => ['value' => 'SKU-27']])->json('recordsFiltered'));
        $this->assertEquals(1, $this->draw('inventory-transfers.stock-data', ['stock_status' => 'none'])->json('recordsFiltered'));
    }

    /** @dataProvider roles */
    public function test_page_uses_empty_ajax_tables_and_keeps_new_movement_in_a_scrollable_dialog($role)
    {
        $user = $this->distributor($role);
        if ($role === User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            $legacy = \App\AreaDistributor::create(['user_id' => $user->id]);
            $legacy->areas()->create(['area_name' => 'Wrong legacy area']);
        }
        $this->movement($user, ['reference_no' => 'SERVER-LOADED-REFERENCE']);
        $this->actingAs($user);
        $response = $this->get(route('inventory-transfers.index'))->assertOk();
        $this->assertSame(['Zone A', 'Zone B'], $response->viewData('areas')->all());
        $response->assertDontSee('SERVER-LOADED-REFERENCE');
        $dom = new \DOMDocument; @$dom->loadHTML($response->getContent()); $xpath = new \DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//table/tbody/tr')->length);
        $this->assertSame(1, $xpath->query('//div[@id="newMovementModal"]//form[@id="newMovementForm"]')->length);
        $this->assertSame(2, $xpath->query('//div[contains(@class,"modal-dialog-scrollable")]')->length);
        $this->assertContains('processing: true, serverSide: true', $response->getContent());
        $this->assertContains(route('inventory-transfers.stock-data'), $response->getContent());
        $this->assertContains('dataTables.bootstrap4.css', $response->getContent());
    }
}
