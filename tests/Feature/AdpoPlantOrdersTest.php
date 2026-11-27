<?php

namespace Tests\Feature;

use App\AdPurchaseOrder;
use App\AreaDistributor;
use App\ProvincialDistributor;
use App\Services\AdpoRouting;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdpoPlantOrdersTest extends TestCase
{
    private $temporaryRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'app.key' => 'base64:' . base64_encode(str_repeat('t', 32)),
            'database.default' => 'adpo_plant_test', 'database.connections.adpo_plant_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Mail::fake();
        foreach ([new User, new AreaDistributor, new ProvincialDistributor, new \App\AreaAd,
            new \App\ProvincialDistributorArea, new \App\Item, new AdPurchaseOrder,
            new \App\AdPurchaseOrderItem, new \App\AdPurchaseOrderPartialReceipt,
            new \App\AdPurchaseOrderVerificationItem, new \App\AdPurchaseOrderPaymentProof,
            new \App\AdpoTaxCertificate, new \App\Voucher] as $model) {
            Schema::create($model->getTable(), function (Blueprint $table) use ($model) {
                $table->increments('id');
                foreach (array_diff($model->getFillable(), ['id', 'created_at', 'updated_at', 'deleted_at']) as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::table('users', function (Blueprint $table) {
            foreach (['warehouse', 'access_permissions', 'is_distributor_personnel'] as $column) {
                if (!Schema::hasColumn('users', $column)) $table->string($column)->nullable();
            }
        });
        Schema::table('adpo_tax_certificates', function (Blueprint $table) {
            $table->integer('ad_purchase_order_id')->nullable(); $table->timestamp('superseded_at')->nullable();
        });
        Schema::create('adpo_product_favorites', function (Blueprint $table) {
            $table->increments('id'); $table->integer('user_id'); $table->integer('item_id'); $table->timestamps();
        });
        Schema::create('ad_purchase_order_vouchers', function (Blueprint $table) {
            $table->integer('ad_purchase_order_id'); $table->integer('voucher_id');
            $table->decimal('rebate_amount', 12, 2)->nullable(); $table->timestamps();
        });
        $this->temporaryRoot = storage_path('framework/testing/adpo-plant-' . uniqid());
        File::makeDirectory($this->temporaryRoot . '/layouts', 0755, true);
        File::put($this->temporaryRoot . '/layouts/header.blade.php', '@yield("css") @yield("content") @yield("javascript")');
        app('view')->getFinder()->prependLocation($this->temporaryRoot);
        app('events')->forget('composing: layouts.header');
        $this->app->instance('path.public', $this->temporaryRoot);
    }

    protected function tearDown()
    {
        File::deleteDirectory($this->temporaryRoot);
        parent::tearDown();
    }

    private function distributor($role, $region, array $attributes = [])
    {
        $user = User::create(['role' => $role, 'name' => 'QA Distributor', 'email' => uniqid() . '@example.test']);
        $class = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? ProvincialDistributor::class : AreaDistributor::class;
        $class::create($attributes + ['user_id' => $user->id, 'name' => 'QA Distributor',
            'business_name' => 'QA Store', 'location_region' => $region, 'status' => 'Active',
            'delivery_address' => 'Saved delivery address', 'store_code' => 'QA-' . $user->id]);
        return $user->fresh();
    }

    private function warehouse($warehouse, array $permissions = [])
    {
        $user = (new User(['role' => 'Admin', 'name' => 'QA Warehouse', 'email' => uniqid() . '@example.test']))
            ->forceFill(['warehouse' => $warehouse, 'access_permissions' => json_encode($permissions)]);
        $user->id = max(100, (int) User::max('id') + 1);
        $user->save();
        return $user;
    }

    private function order(User $buyer, array $attributes = [])
    {
        $order = AdPurchaseOrder::create($attributes + ['po_number' => 'QA-' . uniqid(), 'ad_user_id' => $buyer->id,
            'ad_id' => optional($buyer->ad)->id, 'order_source' => 'prei_warehouse', 'status' => 'Pending',
            'business_name' => 'QA Store', 'delivery_address' => 'Delivery address', 'shipping_type' => 'delivered',
            'payment_method' => 'cash', 'proof_of_payment' => 'qa-proof.pdf', 'total_qty' => 10, 'total_amount' => 700,
            'subtotal' => 700, 'submitted_at' => now()]);
        $order->items()->create(['product_id' => 1, 'product_name' => '330g LPG Refill', 'qty' => 10,
            'unit_price' => 70, 'line_total' => 700]);
        return $order;
    }

    public function distributorRegions()
    {
        $cases = [];
        foreach ([User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR] as $role) {
            foreach (['Region III (Central Luzon)', 'Region VI (Western Visayas)', 'Region VII (Central Visayas)',
                'Region VIII (Eastern Visayas)', 'Region XI (Davao Region)'] as $region) $cases[] = [$role, $region];
        }
        return $cases;
    }

    /** @dataProvider distributorRegions */
    public function test_outside_region_v_form_and_submission_are_locked_to_prei_plant($role, $region)
    {
        $buyer = $this->distributor($role, $region, ['delivery_address' => 'Previous address in Albay, Bicol']);
        $this->actingAs($buyer);
        $response = $this->get(route('ad-purchase-orders.create'))->assertOk();
        $response->assertSee('PREI Plant')->assertSee('Lubao, Pampanga');
        $dom = new \DOMDocument; @$dom->loadHTML($response->getContent()); $xpath = new \DOMXPath($dom);
        $this->assertSame('prei_plant', $xpath->query('//input[@name="order_source"]')->item(0)->getAttribute('value'));
        $this->assertSame(0, $xpath->query('//select[@name="order_source"]')->length);
        $this->assertSame(0, $xpath->query('//input[@value="pickup_guinobatan"]')->length);
        $product = \App\Item::create(['item' => '330g LPG Refill', 'status' => 'Activate', 'dprice' => 70, 'pdprice' => 65]);
        // A stale or tampered source must not route this order back to a PD or Region V.
        $this->post(route('ad-purchase-orders.store'), ['order_source' => 'provincial_distributor',
            'provincial_distributor_id' => 999, 'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'use_rebate_voucher' => 'no', 'products' => [$product->id => ['selected' => 1, 'qty' => 10]],
        ])->assertRedirect();
        $order = AdPurchaseOrder::firstOrFail();
        $this->assertSame('prei_plant', $order->order_source);
        $this->assertNull($order->provincial_distributor_id);
        $this->assertSame('lubao', app(AdpoRouting::class)->warehouseFor($order));
        $this->assertSame('PREI Plant', $order->source_label);
    }

    public function test_region_v_detection_and_legacy_queues_use_the_saved_profile_not_stale_addresses()
    {
        $routing = app(AdpoRouting::class);
        $bicol = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region V (Bicol Region)', ['delivery_address' => 'Pampanga']);
        $visayas = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)', ['delivery_address' => 'Albay']);
        $pd = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VI (Western Visayas)');
        AreaDistributor::create(['user_id' => $pd->id, 'location_region' => 'Region V (Bicol Region)']);
        $bicolOrder = $this->order($bicol);
        $visayasOrder = $this->order($visayas, ['delivery_address' => 'Region V, Albay', 'shipping_type' => 'pickup_guinobatan']);
        $pdOrder = $this->order($pd);
        $plantOrder = $this->order($bicol, ['order_source' => 'prei_plant']);
        $this->order($visayas, ['order_source' => 'provincial_distributor']);
        $this->order($visayas)->delete();
        $this->assertSame('prei_warehouse', $routing->sourceFor($bicol));
        $this->assertSame('prei_plant', $routing->sourceFor($visayas));
        $this->assertEquals([$bicolOrder->id], AdPurchaseOrder::forWarehouse('guinobatan')->pluck('id')->all());
        $this->assertEquals([$visayasOrder->id, $pdOrder->id, $plantOrder->id], AdPurchaseOrder::forWarehouse('lubao')->orderBy('id')->pluck('id')->all());
        $this->assertSame('PREI Plant', $pdOrder->source_label);
        $this->assertSame('PREI Warehouse', $bicolOrder->source_label);
        foreach (['Region V', 'region-v', 'Region 5', 'Bicol Region'] as $name) $this->assertTrue($routing->isRegionVText($name));
        foreach (['Region VI', 'Region VII', 'Region VIII', 'Region IV-A'] as $name) $this->assertFalse($routing->isRegionVText($name));
    }

    public function test_plant_desk_filters_exports_and_order_access_are_limited_to_lubao()
    {
        $buyer = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region III (Central Luzon)');
        $plant = $this->order($buyer, ['order_source' => 'prei_plant', 'po_number' => 'PLANT-ONLY']);
        $bicol = $this->order($this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region V (Bicol Region)'), ['po_number' => 'BICOL-ONLY']);
        $this->actingAs($this->warehouse('lubao'));
        $response = $this->get(route('warehouse-ad-purchase-orders.plant'))->assertOk();
        $response->assertSee('Plant Orders')->assertSee('PLANT-ONLY')->assertDontSee('BICOL-ONLY');
        $this->assertSame(1, $response->viewData('summary')['pending']);
        $this->assertSame(0, $this->get(route('warehouse-ad-purchase-orders.plant', ['status' => 'Completed']))->assertOk()->viewData('orders')->count());
        $this->assertSame(0, $this->get(route('warehouse-ad-purchase-orders.plant', ['search' => 'BICOL']))->assertOk()->viewData('orders')->count());
        $this->get(route('warehouse-ad-purchase-orders.plant.export'))->assertOk();
        $this->withoutExceptionHandling();
        $this->get(route('ad-purchase-orders.show', $plant->id))->assertOk()->assertSee('Source:')->assertSee('PREI Plant');
        $this->withExceptionHandling();
        $this->get(route('ad-purchase-orders.show', $bicol->id))->assertStatus(404);
        $this->patch(route('ad-purchase-orders.updateStatus', $bicol->id), ['status' => 'Cancelled'])->assertStatus(404);
        $this->delete(route('ad-purchase-orders.destroy', $bicol->id))->assertStatus(404);
        $this->get(route('warehouse-ad-purchase-orders.region-v'))->assertStatus(403);
        $this->actingAs($this->warehouse('guinobatan'));
        $this->get(route('warehouse-ad-purchase-orders.plant'))->assertStatus(403);
        $this->get(route('warehouse-ad-purchase-orders.plant.export'))->assertStatus(403);
        $this->get(route('ad-purchase-orders.show', $plant->id))->assertStatus(404);
        $this->patch(route('ad-purchase-orders.updateStatus', $plant->id), ['status' => 'Cancelled'])->assertStatus(404);
        $this->actingAs($buyer);
        $this->get(route('warehouse-ad-purchase-orders.plant'))->assertStatus(403);
        $this->get(route('ad-purchase-orders.show', $plant->id))->assertOk();
    }

    public function test_lubao_uses_the_existing_so_delivery_and_verification_workflow()
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $order = $this->order($buyer, ['order_source' => 'prei_plant']);
        $this->actingAs($this->warehouse('lubao'));
        $this->patch(route('ad-purchase-orders.updateStatus', $order->id), [
            'status' => 'SO Created', 'so_number' => 'SO-PLANT-1', 'payment_date' => '2026-11-27',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('SO Created', $order->fresh()->status);
        $this->patch(route('ad-purchase-orders.updateStatus', $order->id), [
            'status' => 'For Delivery', 'delivery_date' => '2026-11-28', 'dr_number' => 'DR-PLANT-1', 'si_number' => 'SI-PLANT-1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('For Delivery', $order->fresh()->status);
        $this->actingAs($buyer);
        $this->patch(route('ad-purchase-orders.updateStatus', $order->id), [
            'status' => 'For Verification', 'verification_items' => [$order->items->first()->id => ['qty' => 10]],
            'verification_attachments' => [UploadedFile::fake()->create('delivery.pdf', 10)],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('For Verification', $order->fresh()->status);
        $this->actingAs($this->warehouse('lubao'));
        $this->post(route('ad-purchase-orders.verification-complete', $order->id))->assertRedirect();
        $this->assertSame('Completed', $order->fresh()->status);
    }

    public function test_lubao_partial_delivery_keeps_the_balance_on_the_same_order()
    {
        $buyer = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region XI (Davao Region)');
        $order = $this->order($buyer, ['order_source' => 'prei_plant', 'status' => 'For Delivery', 'dr_number' => 'DR-PLANT-2']);
        $item = $order->items->first();
        $this->actingAs($this->warehouse('lubao'));
        $this->patch(route('ad-purchase-orders.updateStatus', $order->id), [
            'status' => 'Partial Received', 'delivery_date' => '2026-11-28', 'remarks' => 'First delivery',
            'partial_items' => [$item->id => ['received_qty' => 4]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $receipt = $order->partialReceipts()->firstOrFail();
        $this->assertEquals(4, $receipt->received_qty);
        $this->assertEquals(10, $item->fresh()->qty);
        $this->assertEquals(1, AdPurchaseOrder::count());
        $this->assertSame('Partial Received', $order->fresh()->status);
        $this->actingAs($buyer);
        $this->patch(route('ad-purchase-orders.updateStatus', $order->id), [
            'status' => 'Partial Received', 'remarks' => 'Received four',
            'partial_receipts' => [$receipt->id => ['confirmed_qty' => 4]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals(4, $receipt->fresh()->confirmed_qty);
        $this->assertEquals(6, (int) $item->fresh()->qty - (int) $item->fresh()->partial_received_qty);
        $this->assertSame('Partial Received', $order->fresh()->status);
    }

    public function test_new_plant_orders_notify_only_lubao_and_sidebar_counts_only_its_pending_orders()
    {
        $lubao = $this->warehouse('lubao');
        $guinobatan = $this->warehouse('guinobatan');
        $buyer = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VI (Western Visayas)');
        $product = \App\Item::create(['item' => '330g LPG Refill', 'status' => 'Activate', 'pdprice' => 65]);
        $this->actingAs($buyer);
        $this->post(route('ad-purchase-orders.store'), ['shipping_type' => 'delivered', 'payment_method' => 'cash',
            'use_rebate_voucher' => 'no', 'products' => [$product->id => ['selected' => 1, 'qty' => 10]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        Mail::assertSent(\App\Mail\AdPurchaseOrderWarehouseNotification::class, function ($mail) use ($lubao, $guinobatan) {
            return $mail->hasTo($lubao->email) && !$mail->hasTo($guinobatan->email)
                && strpos(view('emails.adpo_warehouse_notification', ['order' => $mail->order])->render(), 'Lubao plant') !== false;
        });
        $this->order($buyer, ['order_source' => 'prei_plant', 'status' => 'Completed']);
        $this->order($buyer, ['order_source' => 'provincial_distributor']);
        $this->order($this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region V (Bicol Region)'));
        $html = view('ad_purchase_orders.partials.warehouse-sidebar-link', ['sidebarUser' => $lubao])->render();
        $this->assertContains('Plant Orders', $html);
        $this->assertContains('aria-label="1 pending orders"', $html);
        $this->assertContains(route('warehouse-ad-purchase-orders.plant'), $html);
        AdPurchaseOrder::where('order_source', 'prei_plant')->update(['status' => 'Completed']);
        $html = view('ad_purchase_orders.partials.warehouse-sidebar-link', ['sidebarUser' => $lubao])->render();
        $this->assertNotContains('badge rounded-pill', $html);
        $lubao->access_permissions = json_encode(['purchase_orders' => ['plant' => []]]);
        $this->assertNotContains('Plant Orders', view('ad_purchase_orders.partials.warehouse-sidebar-link', ['sidebarUser' => $lubao])->render());
    }

    public function adSupplierRegions()
    {
        return [['Region V (Bicol Region)'], ['Region VII (Central Visayas)'], ['Region XI (Davao Region)']];
    }

    private function award(User $user, $name)
    {
        $profile = $user->role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? $user->provincialDistributor : $user->ad;
        return $profile->areas()->create(['area_name' => $name]);
    }

    private function submitOrder(User $buyer, array $changes = [])
    {
        $this->actingAs($buyer);
        $product = \App\Item::first() ?: \App\Item::create(['item' => '330g LPG Refill', 'status' => 'Activate', 'dprice' => 70, 'pdprice' => 65]);
        return $this->post(route('ad-purchase-orders.store'), $changes + [
            'authorized_territory' => 'Area A', 'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'use_rebate_voucher' => 'no', 'products' => [$product->id => ['selected' => 1, 'qty' => 10]],
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    /** @dataProvider adSupplierRegions */
    public function test_ad_orders_automatically_use_the_pd_in_the_selected_area_before_the_plant($region)
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, $region);
        $this->award($buyer, 'Area A');
        $supplier = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region V (Bicol Region)');
        $this->award($supplier, 'Area A');
        $unrelated = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, $region);
        $this->award($unrelated, 'Area B');
        $this->actingAs($buyer);
        $response = $this->get(route('ad-purchase-orders.create'))->assertOk();
        $this->assertSame([$supplier->id], $response->viewData('provincialDistributors')->pluck('id')->all());
        $this->assertSame('provincial_distributor', $response->viewData('selectedOrderSource'));
        $dom = new \DOMDocument; @$dom->loadHTML($response->getContent()); $xpath = new \DOMXPath($dom);
        $this->assertSame('provincial_distributor', $xpath->query('//input[@name="order_source"]')->item(0)->getAttribute('value'));
        $this->assertSame((string) $supplier->id, $xpath->query('//select[@name="provincial_distributor_id"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->submitOrder($buyer, ['order_source' => 'prei_plant', 'provincial_distributor_id' => $unrelated->id]);
        $order = AdPurchaseOrder::firstOrFail();
        $this->assertSame('provincial_distributor', $order->order_source);
        $this->assertEquals($supplier->id, $order->provincial_distributor_id);
        $this->assertNull(app(AdpoRouting::class)->warehouseFor($order));
        $this->actingAs($supplier);
        $this->get(route('provincial-distributor.purchase-orders'))->assertOk()->assertSee($order->po_number);
    }

    public function test_ad_supplier_changes_with_the_order_territory_and_falls_back_to_plant_when_uncovered()
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)');
        foreach (['Area A', 'Area B', 'Area C'] as $area) $this->award($buyer, $area);
        $north = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $south = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $this->award($north, 'Area A'); $this->award($south, 'Area B');
        $this->actingAs($buyer);
        $response = $this->withSession(['_old_input' => ['authorized_territory' => 'Area B', 'order_source' => 'prei_plant',
            'provincial_distributor_id' => $north->id]])->get(route('ad-purchase-orders.create'))->assertOk();
        $this->assertSame('provincial_distributor', $response->viewData('selectedOrderSource'));
        $this->assertEquals($south->id, $response->viewData('selectedProvincialDistributorId'));
        $this->assertSame(['Area A'], $response->viewData('provincialDistributorAreas')->get($north->id));
        $this->submitOrder($buyer, ['authorized_territory' => 'Area B', 'provincial_distributor_id' => $north->id]);
        $this->assertEquals($south->id, AdPurchaseOrder::orderByDesc('id')->first()->provincial_distributor_id);
        $this->submitOrder($buyer, ['authorized_territory' => 'Area C', 'order_source' => 'provincial_distributor', 'provincial_distributor_id' => $south->id]);
        $order = AdPurchaseOrder::orderByDesc('id')->first();
        $this->assertSame('prei_plant', $order->order_source);
        $this->assertNull($order->provincial_distributor_id);
    }

    public function test_inactive_disengaged_deleted_and_personnel_profiles_cannot_be_automatic_suppliers()
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $this->award($buyer, 'Area A');
        foreach (['inactive', 'disengaged', 'deleted', 'personnel', 'stale_legacy'] as $type) {
            $supplier = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VII (Central Visayas)');
            $award = $this->award($supplier, $type === 'stale_legacy' ? 'Area B' : 'Area A');
            if ($type === 'inactive') $supplier->provincialDistributor->update(['status' => 'Inactive']);
            if ($type === 'disengaged') $award->delete();
            if ($type === 'deleted') $supplier->provincialDistributor->delete();
            if ($type === 'personnel') $supplier->forceFill(['is_distributor_personnel' => true])->save();
            if (in_array($type, ['deleted', 'stale_legacy'], true)) {
                AreaDistributor::create(['user_id' => $supplier->id, 'status' => 'Active'])->areas()->create(['area_name' => 'Area A']);
            }
        }
        $this->actingAs($buyer);
        $response = $this->get(route('ad-purchase-orders.create'))->assertOk();
        $this->assertCount(0, $response->viewData('provincialDistributors'));
        $this->submitOrder($buyer, ['order_source' => 'provincial_distributor', 'provincial_distributor_id' => $supplier->id]);
        $this->assertSame('prei_plant', AdPurchaseOrder::firstOrFail()->order_source);
    }

    public function test_legacy_pd_areas_still_route_orders_and_region_v_without_a_pd_keeps_the_warehouse()
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region V (Bicol Region)');
        $this->award($buyer, 'Area A');
        $this->submitOrder($buyer, ['order_source' => 'prei_plant']);
        $this->assertSame('prei_warehouse', AdPurchaseOrder::firstOrFail()->order_source);
        $legacy = User::create(['role' => User::ROLE_PROVINCIAL_DISTRIBUTOR, 'name' => 'Legacy PD']);
        AreaDistributor::create(['user_id' => $legacy->id, 'status' => 'Active'])->areas()->create(['area_name' => 'Area A']);
        $this->submitOrder($buyer, ['order_source' => 'prei_warehouse']);
        $order = AdPurchaseOrder::orderByDesc('id')->first();
        $this->assertSame('provincial_distributor', $order->order_source);
        $this->assertEquals($legacy->id, $order->provincial_distributor_id);
    }

    public function test_pd_pickup_remains_editable_without_a_plant_pickup_discount()
    {
        $buyer = $this->distributor(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $this->award($buyer, 'Area A');
        $supplier = $this->distributor(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $this->award($supplier, 'Area A');
        $this->submitOrder($buyer, ['shipping_type' => 'pickup_lubao']);
        $order = AdPurchaseOrder::with('items')->firstOrFail();
        $this->assertSame('pickup', $order->shipping_type);
        $this->assertEquals(0, $order->pickup_discount);
        $this->get(route('ad-purchase-orders.edit', $order->id))->assertOk()->assertSee('Pick Up — Provincial Distributor');
        $this->patch(route('ad-purchase-orders.update', $order->id), ['shipping_type' => 'pickup', 'payment_method' => 'cash',
            'authorized_territory' => 'Area A', 'items' => [$order->items->first()->id => ['qty' => 8]],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('pickup', $order->fresh()->shipping_type);
        $this->assertEquals($supplier->id, $order->fresh()->provincial_distributor_id);
    }
}
