<?php

namespace Tests\Feature;

use App\AreaDistributor;
use App\Http\Controllers\InventoryTransferController;
use App\InventoryTransfer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PullOutPartialTest extends TestCase
{
    private $controller;
    private $distributor;
    private $warehouse;
    private $uploadRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        config(['audit.enabled' => false, 'database.default' => 'pull_out_test',
            'database.connections.pull_out_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $this->uploadRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dms-pull-out-test-' . uniqid();
        File::makeDirectory($this->uploadRoot);
        $this->app->instance('path.public', $this->uploadRoot);
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, warehouse TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, sku TEXT, product_name TEXT, ad_user_id INTEGER)');
        DB::statement('CREATE TABLE ad_areas (ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_orders (id INTEGER PRIMARY KEY, ad_id INTEGER, ad_user_id INTEGER, status TEXT, authorized_territory TEXT,
            order_source TEXT, po_number TEXT, dr_number TEXT, delivery_date TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_order_items (ad_purchase_order_id INTEGER, product_id INTEGER, product_name TEXT, qty INTEGER)');
        DB::statement('CREATE TABLE inventory_transfers (id INTEGER PRIMARY KEY, product_id INTEGER, ad_id INTEGER,
            ad_user_id INTEGER, movement_type TEXT, out_type TEXT, approval_status TEXT, qty INTEGER,
            replacement_qty INTEGER, replacement_product_id INTEGER, replacement_sku TEXT, replacement_item_name TEXT,
            replacement_unit_cost NUMERIC, unit_cost NUMERIC, related_movement_id INTEGER, replacement_dr_number TEXT,
            ad_notified_at TEXT, warehouse TEXT, reviewed_by INTEGER, reviewed_at TEXT, warehouse_remarks TEXT,
            sku TEXT, item_name TEXT, from_area TEXT, to_area TEXT, reference_no TEXT, batch_number TEXT,
            ris_number TEXT, ris_date TEXT, pull_out_attachments TEXT, transfer_date TEXT, remarks TEXT,
            created_by INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        require_once database_path('migrations/2026_09_24_000001_create_inventory_movement_reference_sequences_table.php');
        (new \CreateInventoryMovementReferenceSequencesTable)->up();
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Distributor', 'role' => 'Area Distributor', 'warehouse' => null],
            ['id' => 2, 'name' => 'Warehouse', 'role' => 'Admin', 'warehouse' => 'lubao'],
        ]);
        $this->distributor = User::findOrFail(1);
        $profile = new AreaDistributor;
        $profile->setRelation('areas', collect([(object) ['area_name' => 'Zone A']]));
        $this->distributor->setRelation('ad', $profile);
        $this->warehouse = User::findOrFail(2);
        $this->controller = new InventoryTransferController;
        foreach ([1 => 'Cylinder', 2 => 'Refill'] as $id => $name) {
            DB::table('products')->insert(['id' => $id, 'sku' => 'P'.$id, 'product_name' => $name]);
            InventoryTransfer::create(['product_id' => $id, 'ad_user_id' => 1, 'movement_type' => 'in',
                'item_name' => $name, 'to_area' => 'Zone A', 'qty' => 10, 'approval_status' => 'Approved']);
        }
    }

    protected function tearDown()
    {
        if ($this->uploadRoot && is_dir($this->uploadRoot)) File::deleteDirectory($this->uploadRoot);
        parent::tearDown();
    }

    private function submit($quantity = 10)
    {
        $this->actingAs($this->distributor);
        $request = Request::create('/inventory-transfers', 'POST', [
            'movement_type' => 'out', 'out_type' => 'Pull Out', 'ris_number' => 'SAME-RIS', 'ris_date' => '2026-11-03',
            'items' => [['product_id' => 1, 'qty' => $quantity], ['product_id' => 2, 'qty' => 3]],
        ], [], ['pull_out_attachments' => [UploadedFile::fake()->create('signed.pdf', 1)]]);
        $this->controller->store($request);
        $first = InventoryTransfer::where('out_type', 'Pull Out')->latest('id')->firstOrFail();
        return InventoryTransfer::samePullOut($first)->orderBy('id')->get();
    }

    private function review($id, $decision, array $fields)
    {
        $this->actingAs($this->warehouse);
        return $this->controller->reviewPullOut(new Request(['decision' => $decision] + $fields), $id);
    }

    public function test_two_products_and_successive_partial_releases_keep_one_reference_and_post_each_receipt_once()
    {
        $lines = $this->submit();
        $this->assertCount(2, $lines);
        $this->assertCount(1, $lines->pluck('reference_no')->unique());
        $first = $lines[0];
        $ref = $first->reference_no;
        $this->review($first->id, 'For Processing', ['replacement_qty' => 4, 'warehouse_remarks' => 'First four']);
        $pending = InventoryTransfer::samePullOut($first)->where('product_id', 1)->where('approval_status', 'Pending')->firstOrFail();
        $this->assertEquals(6, $pending->qty);
        $this->assertEquals(4, $first->fresh()->qty);
        $this->review($first->id, 'For Processing', ['replacement_qty' => 4]);
        $this->assertEquals(3, InventoryTransfer::samePullOut($first)->count());
        $this->review($first->id, 'Replacing', ['replacement_dr_number' => 'DR-1', 'release_qty' => 5]);
        $this->assertSame('For Processing', $first->fresh()->approval_status);
        $this->review($first->id, 'Replacing', ['replacement_dr_number' => 'DR-1', 'release_qty' => 2]);
        $this->actingAs($this->distributor);
        $report = $this->controller->pullOutReportData(new Request)->getData(true);
        $this->assertCount(1, $report['data']);
        $this->assertSame('Partial', $report['data'][0]['approval_status']);
        $this->assertContains('DR-1', $report['data'][0]['ad_confirmation']);
        $this->assertEquals(1, $report['data'][0]['confirmation_count']);
        $this->assertEquals([10, 3], array_column($report['data'][0]['products'], 'qty'));
        $this->assertEquals([4, 0], array_column($report['data'][0]['products'], 'approved'));
        $this->assertEquals([8, 3], array_column($report['data'][0]['products'], 'open'));
        $this->controller->confirmPullOutReplacement($first->id);
        $this->controller->confirmPullOutReplacement($first->id);
        $this->assertEquals(2, InventoryTransfer::where('out_type', 'Replacement')->sum('qty'));
        $this->assertSame('Pending', $lines[1]->fresh()->approval_status);
        $this->review($pending->id, 'For Processing', ['replacement_qty' => 6]);
        foreach (InventoryTransfer::samePullOut($first)->where('product_id', 1)->where('approval_status', 'For Processing')->get() as $portion) {
            $this->review($portion->id, 'Replacing', ['replacement_dr_number' => 'DR-2']);
            $this->actingAs($this->distributor);
            $this->controller->confirmPullOutReplacement($portion->id);
        }
        $progress = InventoryTransfer::pullOutProducts(InventoryTransfer::samePullOut($first)->get());
        $this->assertEquals(10, $progress[0]['qty']);
        $this->assertEquals(0, $progress[0]['open']);
        $this->assertEquals(10, $progress[0]['confirmed']);
        $this->assertEquals(3, $progress[1]['open']);
        $this->assertEquals(10, InventoryTransfer::where('out_type', 'Replacement')->where('reference_no', $ref)->sum('qty'));
        $history = $this->controller->movementHistoryData(new Request(['product_id' => 1]))->getData(true);
        $this->assertCount(1, collect($history['data'])->where('reference_no', $ref));
    }

    public function test_report_search_filters_and_pagination_preserve_distributor_scope()
    {
        $lines = $this->submit(4);
        InventoryTransfer::whereIn('id', $lines->pluck('id'))->update([
            'transfer_date' => '2026-11-05', 'warehouse_remarks' => 'Aligned document',
        ]);
        $this->submit(2);
        $foreign = $lines[0]->fresh()->replicate();
        $foreign->ad_user_id = 999;
        $foreign->batch_number = 'FOREIGN-REQUEST';
        $foreign->save();
        $columns = [
            ['reference_no', 'pull_out_rows.reference_no'], ['distributor', 'distributors.name'],
            ['products', ''], [null, 'pull_out_rows.approval_status'], [null, ''],
            ['transfer_date', 'pull_out_rows.transfer_date'], ['from_area', 'pull_out_rows.from_area'],
            ['warehouse', 'pull_out_rows.warehouse'], ['ris_number_display', 'pull_out_rows.ris_number'],
            ['ris_date_display', 'pull_out_rows.ris_date'], ['remarks', 'pull_out_rows.warehouse_remarks'],
        ];
        $params = [
            'draw' => 1, 'start' => 0, 'length' => 25, 'status' => 'Pending', 'warehouse' => 'lubao',
            'date_from' => '2026-11-05', 'date_to' => '2026-11-05', 'search' => ['value' => 'Aligned'],
            'order' => [['column' => 5, 'dir' => 'desc']],
            'columns' => array_map(function ($column) {
                return ['data' => $column[0], 'name' => $column[1], 'searchable' => $column[1] ? 'true' : 'false', 'orderable' => 'true'];
            }, $columns),
        ];
        $url = route('pull-out-report.data') . '?' . http_build_query($params);
        $response = $this->getJson($url)->assertStatus(200)->json();
        $this->assertArrayNotHasKey('error', $response);
        $this->assertEquals(1, $response['recordsFiltered']);
        $this->assertCount(1, $response['data']);
        $this->assertEquals($lines[0]->reference_no, $response['data'][0]['reference_no']);
        $this->assertEquals(0, $response['data'][0]['confirmation_count']);
        $params['start'] = 25;
        // Yajra binds its request once per PHP request; this test shares one application.
        $this->app->forgetInstance('datatables.request');
        $this->assertCount(0, $this->getJson(route('pull-out-report.data') . '?' . http_build_query($params))->assertStatus(200)->json('data'));
    }

    public function test_reused_ris_does_not_merge_separate_transactions()
    {
        $first = $this->submit(4)->first();
        $next = $this->submit(3)->first();
        $this->assertNotEquals($first->reference_no, $next->reference_no);
        $this->actingAs($this->warehouse);
        $queue = $this->controller->warehousePullOuts()->getData();
        $this->assertEquals(2, $queue['summary']['pending']);
        $this->assertEquals(2, $this->controller->pullOutReport()->getData()['summary']['total']);
    }

    public function test_review_approves_several_partial_products_then_processes_the_balances_on_the_same_request()
    {
        $lines = $this->submit();
        InventoryTransfer::whereIn('id', $lines->pluck('id'))->update(['batch_number' => null]);
        $this->actingAs($this->warehouse);
        $row = $this->controller->warehousePullOutsData()->getData(true)['data'][0];
        $payload = ['lines' => [
            ['line_id' => $lines[0]->id, 'qty' => 4, 'unit_cost' => '12.50', 'remarks' => 'Four ready'],
            ['line_id' => $lines[1]->id, 'qty' => 1, 'unit_cost' => 0, 'remarks' => 'One ready'],
        ]];
        $this->post($row['approval_url'], $payload)->assertRedirect(route('warehouse-pull-outs.index'));
        $parts = InventoryTransfer::samePullOut($lines[0]->fresh())->orderBy('id')->get();
        $ready = $parts->where('approval_status', 'For Processing')->keyBy('product_id');
        $pending = $parts->where('approval_status', 'Pending')->keyBy('product_id');
        $this->assertEquals([4, 1], $ready->pluck('qty')->all());
        $this->assertEquals([6, 2], $pending->pluck('qty')->all());
        $this->assertEquals(12.5, (float) $ready[1]->unit_cost);
        $this->assertSame('One ready', $ready[2]->warehouse_remarks);
        $this->assertEquals($this->warehouse->id, $ready[2]->reviewed_by);
        $this->assertSame([$lines[0]->reference_no], $parts->pluck('reference_no')->unique()->values()->all());
        $this->assertCount(1, $parts->pluck('batch_number')->unique());
        $this->postJson($row['approval_url'], $payload)->assertStatus(422);
        $this->assertEquals(5, InventoryTransfer::samePullOut($lines[0]->fresh())->where('approval_status', 'For Processing')->sum('qty'));

        $this->post($row['approval_url'], ['lines' => [
            ['line_id' => $pending[1]->id, 'qty' => ''], ['line_id' => $pending[2]->id, 'qty' => 2],
        ]])->assertRedirect(route('warehouse-pull-outs.index'));
        $this->assertSame('Pending', $pending[1]->fresh()->approval_status);
        $queue = $this->controller->warehousePullOutsData()->getData(true)['data'];
        $this->assertCount(1, $queue);
        $this->assertEquals([4, 3], array_column($queue[0]['release_products'], 'available'));
        $this->assertEquals(13, $queue[0]['requested_qty_total']);
    }

    public function test_bulk_review_rolls_back_all_products_on_invalid_quantity_remark_scope_or_stock()
    {
        $lines = $this->submit();
        $other = $this->submit(2)->first();
        $this->actingAs($this->warehouse);
        $url = route('warehouse-pull-outs.approve', $lines[0]->id);
        $first = ['line_id' => $lines[0]->id, 'qty' => 4, 'remarks' => 'First four'];
        foreach ([
            [$first, ['line_id' => $lines[1]->id, 'qty' => 4]],
            [$first, ['line_id' => $lines[1]->id, 'qty' => 1]],
            [$first, ['line_id' => $other->id, 'qty' => 1, 'remarks' => 'Different request']],
            [$first, $first],
            [['line_id' => $lines[0]->id, 'qty' => 0]],
        ] as $selection) {
            $this->postJson($url, ['lines' => $selection])->assertStatus(422);
        }
        InventoryTransfer::create(['product_id' => 2, 'ad_user_id' => 1, 'movement_type' => 'out',
            'item_name' => 'Refill', 'from_area' => 'Zone A', 'qty' => 9, 'approval_status' => 'Approved']);
        $this->postJson($url, ['lines' => [$first, ['line_id' => $lines[1]->id, 'qty' => 3]]])->assertStatus(422);
        $this->warehouse->warehouse = 'guinobatan';
        $this->postJson($url, ['lines' => [$first]])->assertStatus(404);
        $this->assertCount(2, InventoryTransfer::samePullOut($lines[0])->get());
        $this->assertEquals(0, InventoryTransfer::where('approval_status', 'For Processing')->count());
        $this->assertEquals(10, $lines[0]->fresh()->qty);
    }

    private function deliveryOrder($id, array $overrides = [])
    {
        DB::table('ad_purchase_orders')->insert(array_merge([
            'id' => $id, 'ad_user_id' => $this->distributor->id, 'status' => 'For Delivery',
            'order_source' => 'prei_warehouse', 'po_number' => 'ADPO-' . $id,
            'dr_number' => 'DR-' . $id, 'delivery_date' => '2026-11-05', 'deleted_at' => null,
        ], $overrides));
    }

    public function test_delivery_receipt_choices_only_include_this_distributors_active_warehouse_deliveries()
    {
        $first = $this->submit()->first();
        $this->deliveryOrder(50, ['dr_number' => ' dr-050 ']);
        $this->deliveryOrder(51, ['ad_user_id' => 999]);
        $this->deliveryOrder(52, ['status' => 'Pending']);
        $this->deliveryOrder(53, ['status' => 'Completed']);
        $this->deliveryOrder(54, ['order_source' => 'provincial_distributor']);
        $this->deliveryOrder(55, ['dr_number' => '   ']);
        $this->deliveryOrder(56, ['deleted_at' => '2026-11-05 10:00:00']);
        $this->deliveryOrder(57, ['dr_number' => null]);
        $this->actingAs($this->warehouse);
        $url = route('warehouse-pull-outs.delivery-receipts', $first->id);
        $receipts = $this->getJson($url)->assertStatus(200)->json('delivery_receipts');
        $this->assertCount(1, $receipts);
        $this->assertEquals(['id' => 50, 'dr_number' => 'DR-050', 'po_number' => 'ADPO-50', 'delivery_date' => 'Nov 05, 2026'], $receipts[0]);
        $queue = $this->controller->warehousePullOutsData()->getData(true)['data'];
        $this->assertEquals($url, $queue[0]['delivery_receipts_url']);
        $this->warehouse->warehouse = 'guinobatan';
        $this->getJson($url)->assertStatus(404);
        $this->actingAs($this->distributor)->getJson($url)->assertStatus(403);
    }

    public function test_selected_dpo_dr_releases_multiple_partial_products_and_keeps_the_balance()
    {
        $lines = $this->submit();
        $this->review($lines[0]->id, 'For Processing', ['replacement_qty' => 10]);
        $this->review($lines[1]->id, 'For Processing', ['replacement_qty' => 3]);
        $this->deliveryOrder(50, ['dr_number' => ' dr-050 ']);
        $this->post(route('warehouse-pull-outs.release', $lines[0]->id), [
            'release_ad_purchase_order_id' => 50, 'replacement_dr_number' => 'DR-050',
            'lines' => [['line_id' => $lines[0]->id, 'qty' => 4], ['line_id' => $lines[1]->id, 'qty' => 1]],
        ])->assertRedirect(route('warehouse-pull-outs.index'));
        $parts = InventoryTransfer::samePullOut($lines[0])->get();
        $this->assertEquals(5, $parts->where('approval_status', 'Replacing')->sum('qty'));
        $this->assertSame(['DR-050'], $parts->where('approval_status', 'Replacing')->pluck('replacement_dr_number')->unique()->values()->all());
        $open = $parts->where('approval_status', 'For Processing')->keyBy('product_id');
        $this->assertEquals(6, $open[1]->qty);
        $this->assertEquals(2, $open[2]->qty);
        $this->assertCount(1, $parts->pluck('reference_no')->unique());
        $this->assertEquals('For Delivery', DB::table('ad_purchase_orders')->where('id', 50)->value('status'));
    }

    public function test_selected_dpo_is_rechecked_and_rejects_foreign_stale_deleted_or_changed_receipts_atomically()
    {
        $lines = $this->submit();
        $this->review($lines[0]->id, 'For Processing', ['replacement_qty' => 10]);
        $this->review($lines[1]->id, 'For Processing', ['replacement_qty' => 3]);
        $this->deliveryOrder(50);
        $this->getJson(route('warehouse-pull-outs.delivery-receipts', $lines[0]->id))->assertStatus(200);
        DB::table('ad_purchase_orders')->where('id', 50)->update(['status' => 'Completed']);
        $this->deliveryOrder(51, ['ad_user_id' => 999]);
        $this->deliveryOrder(52, ['order_source' => 'provincial_distributor']);
        $this->deliveryOrder(53, ['deleted_at' => '2026-11-05 10:00:00']);
        $this->deliveryOrder(54, ['dr_number' => 'CHANGED-DR']);
        $this->deliveryOrder(55, ['dr_number' => null]);
        foreach ([50, 51, 52, 53, 54, 55, 999] as $id) {
            $this->postJson(route('warehouse-pull-outs.release', $lines[0]->id), [
                'release_ad_purchase_order_id' => $id, 'replacement_dr_number' => 'DR-' . $id,
                'lines' => [['line_id' => $lines[0]->id, 'qty' => 4], ['line_id' => $lines[1]->id, 'qty' => 1]],
            ])->assertStatus(422)->assertJsonValidationErrors('release_ad_purchase_order_id');
        }
        $parts = InventoryTransfer::samePullOut($lines[0])->get();
        $this->assertCount(2, $parts);
        $this->assertEquals(13, $parts->where('approval_status', 'For Processing')->sum('qty'));
        $this->assertEquals(0, $parts->where('approval_status', 'Replacing')->count());
    }

    public function test_shared_dr_releases_partial_quantities_across_products_and_approved_portions()
    {
        $lines = $this->submit();
        $first = $lines[0];
        $this->review($first->id, 'For Processing', ['replacement_qty' => 4, 'warehouse_remarks' => 'First portion']);
        $otherPortion = InventoryTransfer::samePullOut($first)->where('product_id', 1)->where('approval_status', 'Pending')->firstOrFail();
        $this->review($otherPortion->id, 'For Processing', ['replacement_qty' => 6]);
        $this->review($lines[1]->id, 'For Processing', ['replacement_qty' => 3]);

        $queue = $this->controller->warehousePullOutsData()->getData(true)['data'][0];
        $this->assertEquals([10, 3], array_column($queue['release_products'], 'available'));
        $payload = ['replacement_dr_number' => ' dr-21 ', 'lines' => [
            ['line_id' => $first->id, 'qty' => 7], ['line_id' => $lines[1]->id, 'qty' => 1],
        ]];
        $this->post($queue['release_url'], $payload)->assertRedirect(route('warehouse-pull-outs.index'));
        $parts = InventoryTransfer::samePullOut($first)->get();
        $released = $parts->where('approval_status', 'Replacing');
        $this->assertEquals(7, $released->where('product_id', 1)->sum('qty'));
        $this->assertEquals(1, $released->where('product_id', 2)->sum('qty'));
        $this->assertSame(['DR-21'], $released->pluck('replacement_dr_number')->unique()->values()->all());
        $ready = $parts->where('approval_status', 'For Processing')->keyBy('product_id');
        $this->assertEquals(3, $ready[1]->qty);
        $this->assertEquals(2, $ready[2]->qty);
        $this->assertCount(1, $parts->pluck('reference_no')->unique());
        $this->postJson($queue['release_url'], $payload)->assertStatus(422);
        $this->assertEquals(8, InventoryTransfer::samePullOut($first)->where('approval_status', 'Replacing')->sum('qty'));

        $this->post($queue['release_url'], ['replacement_dr_number' => 'DR-22', 'lines' => [
            ['line_id' => $ready[1]->id, 'qty' => ''], ['line_id' => $ready[2]->id, 'qty' => 2],
        ]])->assertRedirect(route('warehouse-pull-outs.index'));
        $this->assertSame('For Processing', $ready[1]->fresh()->approval_status);
        $this->assertSame('DR-22', $ready[2]->fresh()->replacement_dr_number);
        $this->assertEquals(13, InventoryTransfer::samePullOut($first)->sum('replacement_qty'));
    }

    public function test_shared_dr_rejects_over_release_foreign_lines_empty_selection_and_other_warehouse()
    {
        $lines = $this->submit(4);
        $other = $this->submit(2)->first();
        $this->review($lines[0]->id, 'For Processing', ['replacement_qty' => 4]);
        $this->review($lines[1]->id, 'For Processing', ['replacement_qty' => 3]);
        $this->review($other->id, 'For Processing', ['replacement_qty' => 2]);
        $url = route('warehouse-pull-outs.release', $lines[0]->id);
        foreach ([
            [['line_id' => $lines[0]->id, 'qty' => 1], ['line_id' => $lines[1]->id, 'qty' => 4]],
            [['line_id' => $lines[0]->id, 'qty' => 1], ['line_id' => $other->id, 'qty' => 1]],
            [['line_id' => $lines[0]->id, 'qty' => 0]],
        ] as $selection) {
            $this->postJson($url, ['replacement_dr_number' => 'DR-INVALID', 'lines' => $selection])->assertStatus(422);
        }
        $this->assertCount(2, InventoryTransfer::samePullOut($lines[0])->get());
        $this->assertEquals(0, InventoryTransfer::where('approval_status', 'Replacing')->count());
        $this->warehouse->warehouse = 'guinobatan';
        $this->postJson($url, ['replacement_dr_number' => 'DR-INVALID', 'lines' => [
            ['line_id' => $lines[0]->id, 'qty' => 1],
        ]])->assertStatus(404);
    }

    public function test_invalid_second_product_rolls_back_the_whole_request_and_proof()
    {
        $this->actingAs($this->distributor);
        $request = Request::create('/inventory-transfers', 'POST', [
            'movement_type' => 'out', 'out_type' => 'Pull Out', 'ris_number' => 'TEST', 'ris_date' => '2026-11-03',
            'items' => [['product_id' => 1, 'qty' => 1], ['product_id' => 2, 'qty' => 11]],
        ], [], ['pull_out_attachments' => [UploadedFile::fake()->create('signed.pdf', 1)]]);
        $this->controller->store($request);
        $this->assertEquals(0, InventoryTransfer::where('out_type', 'Pull Out')->count());
        $this->assertEquals(0, DB::table('inventory_movement_reference_sequences')->count());
        $this->assertCount(0, File::allFiles($this->uploadRoot));
    }

    public function test_legacy_lines_stay_together_when_first_processed_partially()
    {
        $lines = $this->submit();
        InventoryTransfer::whereIn('id', $lines->pluck('id'))->update(['batch_number' => null]);
        $this->review($lines[0]->id, 'For Processing', ['replacement_qty' => 4, 'warehouse_remarks' => 'Partial']);
        $siblings = InventoryTransfer::samePullOut($lines[0]->fresh())->get();
        $this->assertCount(3, $siblings);
        $this->assertCount(1, $siblings->pluck('reference_no')->unique());
        $this->assertEquals(13, $siblings->sum('replacement_qty'));
        $this->assertEquals(1, $this->controller->warehousePullOuts()->getData()['summary']['total']);
    }
}
