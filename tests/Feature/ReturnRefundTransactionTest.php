<?php

namespace Tests\Feature;

use App\AreaDistributor;
use App\Http\Controllers\InventoryTransferController;
use App\InventoryTransfer;
use App\Mail\InventoryReturnRefundNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReturnRefundTransactionTest extends TestCase
{
    private $controller;
    private $distributor;
    private $approver;
    private $viewRoot;
    private $uploadRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32)), 'audit.enabled' => false,
            'database.default' => 'return_refund_test',
            'database.connections.return_refund_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Mail::fake();
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, role TEXT, warehouse TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, sku TEXT, product_name TEXT, ad_user_id INTEGER)');
        DB::statement('CREATE TABLE ad_areas (ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_orders (id INTEGER PRIMARY KEY, ad_id INTEGER, ad_user_id INTEGER,
            status TEXT, authorized_territory TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_order_items (ad_purchase_order_id INTEGER, product_id INTEGER, product_name TEXT, qty INTEGER)');
        DB::statement('CREATE TABLE inventory_transfers (id INTEGER PRIMARY KEY, product_id INTEGER, ad_id INTEGER,
            ad_user_id INTEGER, movement_type TEXT, out_type TEXT, approval_status TEXT, qty INTEGER,
            replacement_qty INTEGER, replacement_product_id INTEGER, replacement_sku TEXT, replacement_item_name TEXT,
            replacement_unit_cost NUMERIC, unit_cost NUMERIC, related_movement_id INTEGER, replacement_dr_number TEXT,
            ad_notified_at TEXT, warehouse TEXT, reviewed_by INTEGER, reviewed_at TEXT, warehouse_remarks TEXT,
            sku TEXT, item_name TEXT, from_area TEXT, to_area TEXT, reference_no TEXT, batch_number TEXT,
            ris_number TEXT, ris_date TEXT, pull_out_attachments TEXT, transfer_date TEXT, remarks TEXT,
            return_date TEXT, return_attachments TEXT, warehouse_received_qty INTEGER, warehouse_reference_no TEXT,
            credit_memo_reference_no TEXT, credit_memo_attachments TEXT,
            created_by INTEGER, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        require_once database_path('migrations/2026_09_24_000001_create_inventory_movement_reference_sequences_table.php');
        (new \CreateInventoryMovementReferenceSequencesTable)->up();
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Distributor', 'email' => 'ad@example.test', 'role' => 'Area Distributor', 'warehouse' => null],
            ['id' => 2, 'name' => 'Dennis Villareal', 'email' => 'approver@example.test', 'role' => 'Admin', 'warehouse' => null],
            ['id' => 3, 'name' => 'Warehouse', 'email' => 'warehouse@example.test', 'role' => 'Admin', 'warehouse' => 'lubao'],
        ]);
        $this->distributor = User::findOrFail(1);
        $profile = new AreaDistributor;
        $profile->setRelation('areas', collect([(object) ['area_name' => 'Zone A']]));
        $this->distributor->setRelation('ad', $profile);
        $this->approver = User::findOrFail(2);
        $this->controller = new InventoryTransferController;
        foreach ([1 => 'Cylinder', 2 => 'Refill'] as $id => $name) {
            DB::table('products')->insert(['id' => $id, 'sku' => 'P'.$id, 'product_name' => $name]);
            InventoryTransfer::create(['product_id' => $id, 'ad_user_id' => 1, 'movement_type' => 'in',
                'item_name' => $name, 'to_area' => 'Zone A', 'qty' => 10, 'approval_status' => 'Approved']);
        }
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        if ($this->uploadRoot) File::deleteDirectory($this->uploadRoot);
        parent::tearDown();
    }

    private function submit(array $overrides = [])
    {
        $this->actingAs($this->distributor);
        return $this->controller->store(new Request($overrides + [
            'movement_type' => 'out', 'out_type' => 'Return and Refund',
            'items' => [['product_id' => 1, 'qty' => 4], ['product_id' => 2, 'qty' => 3]],
        ]));
    }

    private function returns()
    {
        return InventoryTransfer::where('out_type', 'Return and Refund');
    }

    private function reportData(array $overrides = [])
    {
        $columns = [
            ['transfer_date', 'return_refund_rows.transfer_date'], ['reference_no', 'return_refund_rows.reference_no'],
            ['distributor', 'distributors.name'], ['products', ''], ['ris_details', ''], ['receiving_details', ''],
            ['credit_memo_details', ''], ['warehouse', 'return_refund_rows.warehouse'],
            ['status_badge', 'return_refund_rows.approval_status'], ['remarks_display', 'return_refund_rows.warehouse_remarks'],
        ];
        $params = $overrides + [
            'draw' => 1, 'start' => 0, 'length' => 25,
            'order' => [['column' => 0, 'dir' => 'desc']],
            'columns' => array_map(function ($column) {
                return ['data' => $column[0], 'name' => $column[1], 'searchable' => 'true', 'orderable' => 'true'];
            }, $columns),
        ];
        $this->app->forgetInstance('datatables.request');
        $data = $this->getJson(route('return-refund-report.data') . '?' . http_build_query($params))->assertStatus(200)->json();
        $this->assertArrayNotHasKey('error', $data);
        return $data;
    }

    private function submitRis($id, array $overrides = [], $files = null)
    {
        if (!$this->uploadRoot) {
            $this->uploadRoot = storage_path('framework/testing/return-refund-uploads-' . uniqid());
            File::makeDirectory($this->uploadRoot, 0755, true);
            $this->app->instance('path.public', $this->uploadRoot);
        }
        $files = $files === null ? [UploadedFile::fake()->create('RIS.pdf', 1)] : $files;
        $response = $this->call('POST', route('return-refunds.documents', $id), $overrides + [
            'ris_number' => ' ris-ad-100 ', 'ris_date' => '2026-11-05',
        ], [], ['return_attachments' => $files],
            ['HTTP_ACCEPT' => 'application/json']);
        // Fake uploads keep an open tmpfile handle. Windows completes an unlink
        // only once that handle closes; real HTTP uploads do not keep it open.
        foreach ($files as $file) if (isset($file->tempFile) && is_resource($file->tempFile)) fclose($file->tempFile);
        return $response;
    }

    public function test_owner_submits_one_ris_for_approved_products_and_keeps_pending_balance_on_same_request()
    {
        $this->withoutExceptionHandling();
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $this->returns()->update(['approval_status' => 'Approved']);
        $pending = $lines[0]->replicate();
        $pending->qty = 1;
        $pending->save();
        $data = $this->reportData();
        $this->assertEquals($lines->pluck('id')->all(), array_column($data['data'][0]['ris_submission']['products'], 'id'));
        $this->assertSame(route('return-refunds.documents', $lines[0]->id), $data['data'][0]['ris_submission']['url']);
        $response = $this->submitRis($lines[0]->id, ['product_ids' => $lines->pluck('id')->all()], [
            UploadedFile::fake()->create('RIS.pdf', 1), UploadedFile::fake()->create('Proof.pdf', 2),
        ])->assertStatus(200)->json();
        foreach ($lines as $line) {
            $saved = $line->fresh();
            $this->assertSame('Documents Submitted', $saved->approval_status);
            $this->assertSame('RIS-AD-100', $saved->ris_number);
            $this->assertSame('2026-11-05', $saved->ris_date->format('Y-m-d'));
            $this->assertSame('2026-11-05', $saved->return_date->format('Y-m-d'));
            $this->assertCount(2, $saved->return_attachments);
            $this->assertEquals($lines[0]->fresh()->return_attachments, $saved->return_attachments);
            foreach ($saved->return_attachments as $file) $this->assertFileExists(public_path($file['path']));
            $this->assertEquals($line->qty, $saved->qty);
            $this->assertSame($line->reference_no, $saved->reference_no);
            $this->assertSame($line->batch_number, $saved->batch_number);
        }
        $this->assertSame('Pending', $pending->fresh()->approval_status);
        $this->assertEquals(1, $response['summary']['total']);
        $this->assertEquals(1, $response['summary']['pending']);
        $this->assertEquals(0, $response['summary']['approved']);
        $this->assertEquals(1, $response['summary']['receiving']);
        $updated = $this->reportData()['data'][0];
        $this->assertNull($updated['ris_submission']);
        $this->assertContains('RIS-AD-100', $updated['ris_details']);
        $this->assertContains('RIS date: Nov 05, 2026', $updated['ris_details']);
        $this->assertCount(2, File::allFiles($this->uploadRoot));
        $this->withExceptionHandling();
        $this->submitRis($lines[0]->id)->assertStatus(422);
        $this->assertCount(2, File::allFiles($this->uploadRoot));
        Mail::assertSent(InventoryReturnRefundNotification::class, 1);
    }

    public function test_ris_upload_rejects_foreign_nonapproved_and_cross_request_products()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $lines[0]->update(['approval_status' => 'Approved']);
        $foreign = $lines[0]->replicate();
        $foreign->ad_user_id = 999;
        $foreign->save();
        $this->submitRis($foreign->id)->assertStatus(403);
        $other = $lines[0]->replicate();
        $other->batch_number = 'ANOTHER-REQUEST';
        $other->save();
        foreach ([$lines[1]->id, $foreign->id, $other->id] as $invalid) {
            $this->submitRis($lines[0]->id, ['product_ids' => [$lines[0]->id, $invalid]])->assertStatus(422);
            $this->assertSame('Approved', $lines[0]->fresh()->approval_status);
        }
        $this->submitRis($lines[1]->id)->assertStatus(422);
        $this->actingAs(User::findOrFail(3));
        $this->submitRis($lines[0]->id)->assertStatus(403);
        $this->actingAs($this->approver);
        $this->assertNull($this->reportData()['data'][0]['ris_submission']);
        $this->assertCount(0, File::allFiles($this->uploadRoot));
    }

    public function test_ris_fields_files_and_product_ids_are_validated_before_any_changes()
    {
        $this->submit();
        $line = $this->returns()->first();
        $line->update(['approval_status' => 'Approved']);
        foreach ([['ris_number' => '   '], ['ris_number' => str_repeat('A', 256)], ['ris_date' => ''],
            ['ris_date' => '2026-02-31'], ['product_ids' => []], ['product_ids' => [$line->id, $line->id]]] as $invalid) {
            $this->submitRis($line->id, $invalid)->assertStatus(422);
        }
        foreach ([[], [UploadedFile::fake()->create('virus.exe', 1)], [UploadedFile::fake()->create('big.pdf', 5121)],
            array_map(function ($i) { return UploadedFile::fake()->create('file'.$i.'.pdf', 1); }, range(1, 6))] as $files) {
            $this->submitRis($line->id, [], $files)->assertStatus(422);
        }
        $this->assertSame('Approved', $line->fresh()->approval_status);
        $this->assertNull($line->fresh()->ris_number);
        $this->assertCount(0, File::allFiles($this->uploadRoot));
    }

    public function test_legacy_approver_ris_form_and_provincial_distributor_ownership_still_work()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $this->returns()->update(['approval_status' => 'Approved']);
        $this->actingAs($this->approver);
        $this->submitRis($lines[0]->id, ['ris_date' => null, 'return_date' => '2026-11-04'])->assertStatus(422);
        // The original form sends return_date and omits ris_date completely.
        $this->call('POST', route('return-refunds.documents', $lines[0]->id), [
            'ris_number' => 'LEGACY-RIS', 'return_date' => '2026-11-04',
        ], [], ['return_attachments' => [UploadedFile::fake()->create('RIS.pdf', 1)]],
            ['HTTP_ACCEPT' => 'application/json'])->assertStatus(200);
        $this->assertSame('2026-11-04', $lines[0]->fresh()->ris_date->format('Y-m-d'));
        $this->distributor->role = 'Provincial Distributor';
        $this->actingAs($this->distributor);
        $this->submitRis($lines[1]->id)->assertStatus(200);
        $this->assertSame('Documents Submitted', $lines[1]->fresh()->approval_status);
    }

    public function test_ris_submission_rolls_back_all_products_and_uploaded_files_if_saving_fails()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $this->returns()->update(['approval_status' => 'Approved']);
        $lastId = $lines->last()->id;
        InventoryTransfer::updating(function ($line) use ($lastId) {
            if ($line->id === $lastId && $line->approval_status === 'Documents Submitted') throw new \RuntimeException('Simulated save failure');
        });
        $this->submitRis($lines[0]->id, ['product_ids' => $lines->pluck('id')->all()])->assertStatus(500);
        $this->assertEquals(2, $this->returns()->where('approval_status', 'Approved')->count());
        $this->assertEquals(0, $this->returns()->whereNotNull('ris_number')->count());
        $this->assertCount(0, File::allFiles($this->uploadRoot));
    }

    public function test_report_keeps_every_product_and_document_when_filtering_mixed_status_requests()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        foreach ($lines as $i => $line) {
            $line->update([
                'approval_status' => $i === 0 ? 'Approved' : 'Pending',
                'ris_number' => 'RIS-' . $i, 'return_date' => '2026-11-05',
                'return_attachments' => [['path' => 'uploads/test-'.$i.'.pdf', 'name' => 'Proof '.$i.'.pdf']],
                'warehouse_reference_no' => 'SIRR-' . $i, 'warehouse_received_qty' => $i + 1,
                'credit_memo_reference_no' => 'CM-' . $i,
                'credit_memo_attachments' => [['path' => 'uploads/cm-'.$i.'.pdf', 'name' => 'Memo '.$i.'.pdf']],
                'warehouse_remarks' => 'Remark ' . $i,
            ]);
        }
        $lines[0]->update(['item_name' => 'Cylinder || kit ~~ <script>alert(1)</script>', 'sku' => null]);
        foreach (['', 'Pending', 'Approved', 'Partial'] as $status) {
            $data = $this->reportData(['status' => $status]);
            $this->assertEquals(1, $data['recordsTotal']);
            $this->assertEquals(1, $data['recordsFiltered']);
            $this->assertCount(1, $data['data']);
            $row = $data['data'][0];
            $this->assertEquals($lines[0]->id, $row['id']);
            $this->assertContains('Refill', $row['products']);
            $this->assertContains('Cylinder || kit ~~ &lt;script&gt;', $row['products']);
            $this->assertNotContains('<script>', $row['products']);
            $this->assertContains('No SKU · 4 pcs', $row['products']);
            $this->assertSame('Partial', $row['approval_status']);
            foreach ([0, 1] as $i) {
                $this->assertContains('RIS-'.$i, $row['ris_details']);
                $this->assertContains('Proof '.$i.'.pdf', $row['ris_details']);
                $this->assertContains('SIRR-'.$i, $row['receiving_details']);
                $this->assertContains('CM-'.$i, $row['credit_memo_details']);
                $this->assertContains('Memo '.$i.'.pdf', $row['credit_memo_details']);
                $this->assertContains('Remark '.$i, $row['remarks_display']);
            }
        }
        $summary = $this->controller->returnRefundReport()->getData()['summary'];
        $this->assertEquals(1, $summary['approved']);
        $this->assertEquals(1, $summary['pending']);
    }

    public function test_report_search_dates_and_pagination_preserve_whole_requests_and_distributor_scope()
    {
        $this->submit();
        $first = $this->returns()->orderBy('id')->get();
        foreach ($first as $line) $line->update(['transfer_date' => '2026-11-05']);
        $first[1]->update(['sku' => 'UNIQUE-SKU', 'from_area' => 'Special Area', 'ris_number' => 'FIND-RIS',
            'credit_memo_reference_no' => 'FIND-MEMO', 'warehouse_remarks' => 'Needle remarks']);
        $this->submit();
        $this->returns()->whereNotIn('id', $first->pluck('id'))->update(['transfer_date' => '2026-10-01']);
        $foreign = $first[0]->replicate();
        $foreign->ad_user_id = 999;
        $foreign->batch_number = 'FOREIGN';
        $foreign->save();
        foreach (['UNIQUE-SKU', 'Special Area', 'FIND-RIS', 'FIND-MEMO', 'Needle remarks', $first[0]->reference_no] as $keyword) {
            $data = $this->reportData(['search' => ['value' => $keyword]]);
            $this->assertEquals(2, $data['recordsTotal']);
            $this->assertEquals(1, $data['recordsFiltered']);
            $this->assertContains('Cylinder', $data['data'][0]['products']);
            $this->assertContains('Refill', $data['data'][0]['products']);
        }
        $dateFilter = ['date_from' => '2026-11-05', 'date_to' => '2026-11-05'];
        $this->assertEquals(1, $this->reportData($dateFilter)['recordsFiltered']);
        $this->assertEquals(0, $this->reportData(['warehouse' => 'guinobatan'])['recordsFiltered']);
        $page = $this->reportData(['start' => 1, 'length' => 1]);
        $this->assertCount(1, $page['data']);
        $this->assertEquals('Oct 01, 2026', $page['data'][0]['transfer_date']);
        $this->assertCount(0, $this->reportData(['start' => 2, 'length' => 1])['data']);
    }

    public function test_report_warehouse_permissions_cannot_expand_warehouse_scope()
    {
        $this->submit();
        $foreign = $this->returns()->first()->replicate();
        $foreign->warehouse = 'guinobatan';
        $foreign->save();
        $warehouse = User::findOrFail(3);
        $warehouse->access_permissions = json_encode(['return_refunds' => ['requests' => ['view']]]);
        $this->actingAs($warehouse);
        $this->assertEquals(1, $this->reportData()['recordsTotal']);
        $this->assertEquals(0, $this->reportData(['warehouse' => 'guinobatan'])['recordsFiltered']);
        $this->assertEquals(1, $this->controller->returnRefundReport()->getData()['summary']['total']);
        $warehouse->access_permissions = json_encode(['dashboard' => ['overview' => ['view']]]);
        $this->getJson(route('return-refund-report.data'))->assertStatus(403);
    }

    public function test_multiple_products_are_one_request_with_one_notification_and_history_entry()
    {
        $response = $this->submit();
        $this->assertContains('one request with 2 product', $response->getSession()->get('success'));
        $lines = $this->returns()->orderBy('id')->get();
        $this->assertCount(2, $lines);
        $this->assertCount(1, $lines->pluck('reference_no')->unique());
        $this->assertCount(1, $lines->pluck('batch_number')->unique());
        $this->assertNotEmpty($lines[0]->batch_number);
        $this->assertEquals([4, 3], $lines->pluck('qty')->all());
        $this->assertSame(['Pending'], $lines->pluck('approval_status')->unique()->values()->all());
        $this->assertEquals(1, InventoryTransfer::countReturnRefundTransactions($this->returns()));
        Mail::assertSent(InventoryReturnRefundNotification::class, 1);
        Mail::assertSent(InventoryReturnRefundNotification::class, function ($mail) {
            $mail->build();
            return $mail->hasTo('approver@example.test') && $mail->viewData['products']->count() === 2;
        });
        $mail = (new InventoryReturnRefundNotification($lines[0], 'approver'))->build();
        $email = view($mail->view, $mail->viewData + ['movement' => $lines[0], 'recipientType' => 'approver'])->render();
        $this->assertContains('Cylinder', $email);
        $this->assertContains('Refill', $email);
        $history = $this->controller->movementHistoryData(new Request)->getData(true);
        $requestRows = collect($history['data'])->where('reference_no', $lines[0]->reference_no);
        $this->assertCount(1, $requestRows);
        $this->assertEquals(1, $this->controller->returnRefundReport()->getData()['summary']['total']);
    }

    public function test_queue_keeps_products_together_after_individual_actions_and_honors_warehouse_scope()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $this->actingAs($this->approver);
        $this->controller->approveReturnRefund(new Request(['decision' => 'Approved']), $lines[0]->id);
        $requests = $this->controller->returnRefundRequests()->getData()['requests'];
        $this->assertCount(1, $requests);
        $this->assertCount(2, $requests[0]->requestLines);
        $this->assertSame(['Approved', 'Pending'], $requests[0]->requestLines->pluck('approval_status')->all());
        $this->assertEquals(1, InventoryTransfer::countReturnRefundTransactions($this->returns()->where('approval_status', 'Pending')));

        $this->viewRoot = sys_get_temp_dir() . '/dms-return-refund-views-' . uniqid();
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', "@yield('content') @yield('javascript')");
        $this->app['view']->getFinder()->prependLocation($this->viewRoot);
        $this->app['events']->forget('composing: layouts.header');
        $html = $this->controller->returnRefundRequests()->render();
        $this->assertEquals(1, substr_count($html, '<tr data-statuses='));
        $this->assertContains('2 products · 7 pcs total', $html);
        $this->assertContains(route('return-refunds.documents', $lines[0]->id), $html);
        $this->assertContains(route('return-refunds.approve', $lines[1]->id), $html);
        $this->assertNotContains('action="' . route('return-refunds.approve', $lines[0]->id) . '"', $html);
        $this->assertContains(route('return-refunds.approve-products', $requests[0]->id), $html);
        $this->assertEquals(1, substr_count($html, 'class="rr-approval-select"'));
        $this->assertContains('form="rrApproveForm' . $requests[0]->id . '"', $html);

        $foreign = $lines[0]->replicate();
        $foreign->warehouse = 'guinobatan';
        $foreign->save();
        $this->actingAs(User::findOrFail(3));
        $visible = $this->controller->returnRefundRequests()->getData()['requests'];
        $this->assertCount(1, $visible);
        $this->assertCount(2, $visible[0]->requestLines);
        $this->assertNotContains('class="rr-bulk-approval"', $this->controller->returnRefundRequests()->render());
    }

    public function test_separate_submissions_legacy_requests_and_foreign_owners_do_not_merge()
    {
        $this->submit();
        $first = $this->returns()->firstOrFail();
        $this->submit();
        $this->assertEquals(2, InventoryTransfer::countReturnRefundTransactions($this->returns()));
        foreach ([['ad_user_id' => 77], ['warehouse' => 'guinobatan'], ['batch_number' => 'OTHER-BATCH'],
            ['batch_number' => null, 'reference_no' => 'LEGACY'], ['batch_number' => null, 'reference_no' => 'LEGACY'],
            ['batch_number' => null, 'reference_no' => null], ['batch_number' => '', 'reference_no' => '']] as $changes) {
            $copy = $first->replicate();
            $copy->fill($changes)->save();
        }
        $deleted = $first->replicate();
        $deleted->batch_number = 'DELETED';
        $deleted->save();
        $deleted->delete();
        $this->assertEquals(8, InventoryTransfer::countReturnRefundTransactions($this->returns()));
        $this->assertCount(2, InventoryTransfer::sameReturnRefund($first)->get());
        $this->actingAs($this->approver);
        $requests = $this->controller->returnRefundRequests()->getData()['requests'];
        $this->assertCount(8, $requests);
        $this->assertCount(2, $requests->firstWhere('reference_no', 'LEGACY')->requestLines);
    }

    public function test_invalid_product_quantity_rolls_back_the_whole_request_and_sends_no_email()
    {
        $response = $this->submit(['items' => [['product_id' => 1, 'qty' => 4], ['product_id' => 2, 'qty' => 11]]]);
        $this->assertNotEmpty($response->getSession()->get('error'));
        $this->assertEquals(0, $this->returns()->count());
        Mail::assertNothingSent();
    }

    public function test_single_product_legacy_form_also_receives_a_batch_identity()
    {
        $this->submit(['items' => null, 'product_id' => 1, 'qty' => 3]);
        $movement = $this->returns()->firstOrFail();
        $this->assertNotEmpty($movement->batch_number);
        $this->assertNotEmpty($movement->reference_no);
        $this->assertEquals(1, InventoryTransfer::countReturnRefundTransactions($this->returns()));
        Mail::assertSent(InventoryReturnRefundNotification::class, 1);
    }

    public function test_bulk_approval_approves_selected_products_and_preserves_one_transaction()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $remaining = $lines[0]->replicate();
        $remaining->fill(['product_id' => 3, 'sku' => 'P3', 'item_name' => 'Crate', 'qty' => 2])->save();
        $this->actingAs($this->approver);
        $this->post(route('return-refunds.approve-products', $remaining->id), [
            'product_ids' => $lines->pluck('id')->all(), 'warehouse_remarks' => 'Checked together',
        ])->assertRedirect(route('return-refunds.index'))->assertSessionHas('success');
        $approved = $this->returns()->where('approval_status', 'Approved')->orderBy('id')->get();
        $this->assertCount(2, $approved);
        $this->assertSame(['Checked together'], $approved->pluck('warehouse_remarks')->unique()->values()->all());
        $this->assertEquals([4, 3], $approved->pluck('qty')->all());
        $this->assertEquals([$this->approver->id], $approved->pluck('reviewed_by')->unique()->values()->all());
        $this->assertCount(1, $approved->pluck('reviewed_at')->unique());
        $this->assertSame('Pending', $remaining->fresh()->approval_status);
        $this->assertCount(3, $this->returns()->get());
        $this->assertEquals([$lines[0]->reference_no], $this->returns()->pluck('reference_no')->unique()->values()->all());
        $this->assertEquals([$lines[0]->batch_number], $this->returns()->pluck('batch_number')->unique()->values()->all());
        $this->assertEquals(1, InventoryTransfer::countReturnRefundTransactions($this->returns()->where('approval_status', 'Pending')));
        $this->assertCount(1, $this->controller->returnRefundRequests()->getData()['requests']);
        // The same request can approve the remaining product later.
        $this->post(route('return-refunds.approve-products', $lines[0]->id), ['product_ids' => [$remaining->id]])->assertRedirect();
        $this->assertEquals(3, $this->returns()->where('approval_status', 'Approved')->count());
        $this->assertEquals(0, InventoryTransfer::countReturnRefundTransactions($this->returns()->where('approval_status', 'Pending')));
        Mail::assertSent(InventoryReturnRefundNotification::class, 1);
    }

    public function test_bulk_approval_rejects_foreign_or_deleted_products_without_approving_any_selection()
    {
        $this->submit();
        $first = $this->returns()->firstOrFail();
        $this->actingAs($this->approver);
        foreach ([['batch_number' => 'ANOTHER-REQUEST'], ['ad_user_id' => 99], ['warehouse' => 'guinobatan'],
            ['out_type' => 'Pull Out'], ['deleted_at' => '2026-11-05 10:00:00']] as $attributes) {
            $foreign = $first->replicate();
            $foreign->forceFill($attributes)->save();
            $this->postJson(route('return-refunds.approve-products', $first->id), [
                'product_ids' => [$first->id, $foreign->id],
            ])->assertStatus(422)->assertJsonValidationErrors('product_ids');
            $this->assertSame('Pending', $first->fresh()->approval_status);
            $this->assertEquals(0, InventoryTransfer::where('approval_status', 'Approved')->where('movement_type', 'out')->count());
        }
    }

    public function test_bulk_approval_rechecks_status_and_rejects_repeated_submissions()
    {
        $this->submit();
        $lines = $this->returns()->orderBy('id')->get();
        $lines[1]->update(['approval_status' => 'Rejected']);
        $this->actingAs($this->approver);
        $url = route('return-refunds.approve-products', $lines[0]->id);
        $this->postJson($url, ['product_ids' => $lines->pluck('id')->all()])->assertStatus(422);
        $this->assertSame('Pending', $lines[0]->fresh()->approval_status);
        $this->assertSame('Rejected', $lines[1]->fresh()->approval_status);
        $this->post($url, ['product_ids' => [$lines[0]->id]])->assertRedirect();
        $snapshot = $lines[0]->fresh()->getAttributes();
        $this->postJson($url, ['product_ids' => [$lines[0]->id], 'warehouse_remarks' => 'Overwrite'])->assertStatus(422);
        $this->assertEquals($snapshot, $lines[0]->fresh()->getAttributes());
        $this->postJson(route('return-refunds.approve', $lines[0]->id), ['decision' => 'Rejected'])->assertStatus(422);
        $this->assertSame('Approved', $lines[0]->fresh()->approval_status);
    }

    public function test_bulk_approval_requires_selected_unique_ids_and_approver_edit_access()
    {
        $this->submit();
        $first = $this->returns()->firstOrFail();
        $url = route('return-refunds.approve-products', $first->id);
        $this->actingAs($this->approver);
        foreach ([[], [$first->id, $first->id], [0], ['invalid']] as $ids) {
            $this->postJson($url, ['product_ids' => $ids])->assertStatus(422);
        }
        $this->postJson($url, ['product_ids' => [$first->id], 'warehouse_remarks' => str_repeat('x', 1001)])->assertStatus(422);
        $this->actingAs($this->distributor)->postJson($url, ['product_ids' => [$first->id]])->assertStatus(403);
        $this->actingAs(User::findOrFail(3))->postJson($url, ['product_ids' => [$first->id]])->assertStatus(403);
        $this->approver->access_permissions = json_encode(['return_refunds' => ['requests' => ['view']]]);
        $this->actingAs($this->approver)->postJson($url, ['product_ids' => [$first->id]])->assertStatus(403);
        $this->assertSame('Pending', $first->fresh()->approval_status);
    }

    public function test_bulk_approval_preserves_legacy_reference_grouping_without_a_batch()
    {
        $this->submit();
        $this->returns()->update(['batch_number' => null]);
        $lines = $this->returns()->orderBy('id')->get();
        $this->actingAs($this->approver)->post(route('return-refunds.approve-products', $lines[0]->id), [
            'product_ids' => $lines->pluck('id')->all(),
        ])->assertRedirect();
        $this->assertEquals(2, $this->returns()->where('approval_status', 'Approved')->count());
        $this->assertEquals(1, InventoryTransfer::countReturnRefundTransactions($this->returns()));
        $this->assertSame([null], $this->returns()->pluck('batch_number')->unique()->values()->all());
        $this->assertEquals($lines[0]->reference_no, $lines[1]->fresh()->reference_no);
    }
}
