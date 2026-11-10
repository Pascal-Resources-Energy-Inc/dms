<?php

namespace Tests\Feature;

use App\AdPurchaseOrder;
use App\Http\Controllers\AdPurchaseOrderController;
use App\Item;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdpoCollateralPricingTest extends TestCase
{
    private $controller;
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'session.driver' => 'array',
            'database.default' => 'adpo_pricing_test',
            'database.connections.adpo_pricing_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Mail::fake();
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, email TEXT, withholding_tax INTEGER DEFAULT 0)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT,
            business_name TEXT, address TEXT, delivery_address TEXT, location_region TEXT, withholding_tax INTEGER DEFAULT 0, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, withholding_tax INTEGER DEFAULT 0, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT, item_description TEXT, item_image TEXT,
            item_type TEXT, status TEXT, price NUMERIC, dprice NUMERIC, for_ad INTEGER,
            created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_orders (id INTEGER PRIMARY KEY, po_number TEXT, ad_id INTEGER,
            ad_user_id INTEGER, order_source TEXT, provincial_distributor_id INTEGER, business_name TEXT,
            authorized_territory TEXT, phone_number TEXT, email_address TEXT, delivery_address TEXT, shipping_type TEXT,
            payment_method TEXT, bank_name TEXT, voucher_id INTEGER, voucher_code TEXT, rebate_amount NUMERIC,
            pickup_discount NUMERIC, delivery_fee NUMERIC, uniform_size TEXT, status TEXT, remarks TEXT,
            submitted_at TEXT, created_by INTEGER, subtotal NUMERIC, total_qty INTEGER, withholding_tax NUMERIC,
            total_amount NUMERIC, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_order_items (id INTEGER PRIMARY KEY, ad_purchase_order_id INTEGER,
            product_id INTEGER, sku TEXT, product_name TEXT, description TEXT, product_image TEXT, color_breakdown TEXT,
            size_breakdown TEXT, qty INTEGER, unit_price NUMERIC, line_total NUMERIC, dealer_points NUMERIC,
            created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE vouchers (id INTEGER PRIMARY KEY, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_purchase_order_vouchers (ad_purchase_order_id INTEGER, voucher_id INTEGER,
            rebate_amount NUMERIC, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE adpo_tax_certificates (id INTEGER PRIMARY KEY, ad_purchase_order_id INTEGER, superseded_at TEXT)');
        DB::table('users')->insert(['id' => 1, 'name' => 'Test Distributor', 'role' => 'Area Distributor', 'email' => 'ad@example.test']);
        DB::table('area_distributors')->insert(['id' => 1, 'user_id' => 1, 'name' => 'Test Distributor', 'business_name' => 'Test Store']);
        $this->actingAs(User::findOrFail(1));
        $this->controller = new AdPurchaseOrderController;
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function product(array $attributes = [])
    {
        return Item::create($attributes + ['item' => 'Marketing poster', 'price' => 175.50, 'dprice' => 0,
            'for_ad' => 1, 'status' => 'Activate', 'item_type' => 'product']);
    }

    private function store(array $products, array $extra = [])
    {
        $response = $this->controller->store(new Request($extra + [
            'order_source' => 'prei_warehouse', 'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'use_rebate_voucher' => 'no', 'delivery_fee' => 0, 'products' => $products,
        ]));
        $order = AdPurchaseOrder::with('items')->orderBy('id', 'desc')->firstOrFail();
        $this->assertSame(route('ad-purchase-orders.show', $order->id), $response->getTargetUrl());
        return $order;
    }

    public function distributorRoles()
    {
        return [['Area Distributor'], ['Provincial Distributor']];
    }

    /** @dataProvider distributorRoles */
    public function test_submission_saves_catalog_price_for_collateral_and_distributor_price_for_regular_products($role)
    {
        if ($role === 'Provincial Distributor') {
            DB::table('area_distributors')->delete();
            DB::table('provincial_distributors')->insert(['id' => 1, 'user_id' => 1]);
            DB::table('users')->where('id', 1)->update(['role' => $role]);
            $this->actingAs(User::findOrFail(1));
        }
        $collateral = $this->product();
        $regular = $this->product(['item' => 'Standard product', 'for_ad' => 0, 'price' => 450, 'dprice' => 300.25]);
        $order = $this->store([
            $collateral->id => ['selected' => 1, 'qty' => 2, 'unit_price' => 1, 'price' => 1],
            $regular->id => ['selected' => 1, 'qty' => 3, 'unit_price' => 1],
        ], ['subtotal' => 1, 'total_amount' => 1]);
        $lines = $order->items->keyBy('product_id');
        $this->assertEquals(175.50, $lines[$collateral->id]->unit_price);
        $this->assertEquals(351, $lines[$collateral->id]->line_total);
        $this->assertEquals(300.25, $lines[$regular->id]->unit_price);
        $this->assertEquals(900.75, $lines[$regular->id]->line_total);
        $this->assertEquals(1251.75, $order->subtotal);
        $this->assertEquals(1251.75, $order->total_amount);
        $this->assertEquals(5, $order->total_qty);
        Mail::assertNothingSent();
    }

    public function test_null_or_nonzero_distributor_price_does_not_override_marketing_collateral_price()
    {
        $withoutDistributorPrice = $this->product(['dprice' => null]);
        $withDistributorPrice = $this->product(['dprice' => 9]);
        $freeCollateral = $this->product(['price' => 0, 'dprice' => 999]);
        $order = $this->store(collect([$withoutDistributorPrice, $withDistributorPrice, $freeCollateral])
            ->mapWithKeys(function ($item) { return [$item->id => ['selected' => 1, 'qty' => 2]]; })->all());
        $this->assertEquals([175.5, 175.5, 0], $order->items->pluck('unit_price')->all());
        $this->assertEquals(702, $order->subtotal);
    }

    public function test_collateral_size_variants_use_the_same_price_for_every_unit()
    {
        $uniform = $this->product(['item' => 'Gaz Lite Authorized Retail Partner Uniform', 'price' => 225.75]);
        $order = $this->store([$uniform->id => ['selected' => 1, 'qty' => 0, 'sizes' => ['small' => 2, 'large' => 3]]]);
        $this->assertEquals(5, $order->items->first()->qty);
        $this->assertEquals(225.75, $order->items->first()->unit_price);
        $this->assertEquals(1128.75, $order->subtotal);
    }

    public function test_adding_collateral_to_a_pending_order_saves_its_price_and_preserves_existing_line_prices()
    {
        $regular = $this->product(['item' => 'Standard product', 'for_ad' => 0, 'dprice' => 300.25]);
        $collateral = $this->product();
        $order = $this->store([$regular->id => ['selected' => 1, 'qty' => 2]]);
        $regular->update(['dprice' => 999]);
        $response = $this->controller->update(new Request([
            'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'items' => [$order->items->first()->id => ['qty' => 4, 'unit_price' => 1]],
            'new_products' => [$collateral->id => ['qty' => 3, 'unit_price' => 0]],
        ]), $order->id);
        $this->assertSame(route('ad-purchase-orders.show', $order->id), $response->getTargetUrl());
        $order = $order->fresh('items');
        $lines = $order->items->keyBy('product_id');
        $this->assertEquals(300.25, $lines[$regular->id]->unit_price);
        $this->assertEquals(175.50, $lines[$collateral->id]->unit_price);
        $this->assertEquals(526.50, $lines[$collateral->id]->line_total);
        $this->assertEquals(1727.50, $order->subtotal);
        $this->assertEquals(1727.50, $order->total_amount);
    }

    public function test_create_and_edit_previews_use_the_prices_that_will_be_saved()
    {
        $this->viewRoot = storage_path('framework/testing/adpo-pricing-views-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', "@yield('content') @yield('javascript')");
        $this->app['view']->getFinder()->prependLocation($this->viewRoot);
        $this->app['events']->forget('composing: layouts.header');
        $this->app['view']->share('errors', new \Illuminate\Support\ViewErrorBag);
        $regular = $this->product(['item' => 'Standard product', 'for_ad' => 0, 'price' => 450, 'dprice' => 300.25]);
        $collateral = $this->product();
        $html = view('ad_purchase_orders.create', [
            'ad' => auth()->user()->ad, 'products' => collect([$regular, $collateral]), 'favoriteProductIds' => [],
            'userVouchers' => collect(), 'showPickupLubao' => true, 'isAdmin' => false, 'provincialDistributors' => collect(),
            'selectedOrderSource' => 'prei_warehouse', 'selectedProvincialDistributorId' => null,
            'orderForUser' => auth()->user(), 'isProvincialDistributor' => false, 'territoryOptions' => collect(),
            'requiresTaxSignature' => false, 'taxProfile' => auth()->user()->ad,
        ])->render();
        $this->assertContains('data-price="175.5"', $html);
        $this->assertContains('PHP 175.50', $html);
        $this->assertContains('data-price="300.25"', $html);
        $this->assertContains('PHP 300.25', $html);
        $order = $this->store([$regular->id => ['selected' => 1, 'qty' => 1]]);
        $edit = $this->controller->edit($order->id)->render();
        $this->assertContains('data-price="175.5"', $edit);
        $this->assertContains('PHP 175.50 each', $edit);
    }
}
