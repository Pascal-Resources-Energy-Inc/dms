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
        config(['audit.enabled' => false, 'session.driver' => 'array', 'app.key' => 'base64:' . base64_encode(str_repeat('t', 32)),
            'database.default' => 'adpo_pricing_test',
            'database.connections.adpo_pricing_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Mail::fake();
        DB::statement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, email TEXT, region TEXT, warehouse TEXT, withholding_tax INTEGER DEFAULT 0)');
        DB::statement('CREATE TABLE area_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT,
            business_name TEXT, business_type TEXT, contact_number TEXT, email_address TEXT, store_code TEXT,
            address TEXT, delivery_address TEXT, location_region TEXT, withholding_tax INTEGER DEFAULT 0, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributors (id INTEGER PRIMARY KEY, user_id INTEGER, name TEXT,
            business_name TEXT, business_type TEXT, contact_number TEXT, email_address TEXT, store_code TEXT,
            address TEXT, delivery_address TEXT, location_region TEXT, withholding_tax INTEGER DEFAULT 0, deleted_at TEXT)');
        DB::statement('CREATE TABLE provincial_distributor_areas (id INTEGER PRIMARY KEY, provincial_distributor_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE ad_areas (id INTEGER PRIMARY KEY, ad_id INTEGER, area_name TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT, item_description TEXT, item_image TEXT,
            item_type TEXT, status TEXT, price NUMERIC, dprice NUMERIC, pdprice NUMERIC, vismin_price NUMERIC,
            vismin_dealer_price NUMERIC, vismin_md_price NUMERIC, vismin_dprice NUMERIC, vismin_pdprice NUMERIC, for_ad INTEGER,
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
        DB::statement('CREATE TABLE vouchers (id INTEGER PRIMARY KEY, name TEXT, code TEXT, area_names TEXT,
            discount_type TEXT, discount_value NUMERIC, minimum_order_amount NUMERIC, usage_limit INTEGER,
            used_count INTEGER, is_active INTEGER, starts_at TEXT, expires_at TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE adpo_product_favorites (id INTEGER PRIMARY KEY, user_id INTEGER, item_id INTEGER)');
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

    private function submitterProfile($role, $keepLegacy = false, $region = 'Region VII (Central Visayas)')
    {
        DB::table('users')->where('id', 1)->update(['role' => $role]);
        $attributes = ['name' => 'Profile Owner', 'business_name' => 'Saved Distributor Business',
            'business_type' => 'Corporation', 'contact_number' => '09123456789',
            'email_address' => 'profile@example.test', 'store_code' => 'PROFILE-CODE',
            'address' => 'Saved profile address', 'delivery_address' => 'Saved delivery address',
            'location_region' => $region];
        if ($role === User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            DB::table('provincial_distributors')->insert($attributes + ['id' => 42, 'user_id' => 1]);
            DB::table('provincial_distributor_areas')->insert([
                ['provincial_distributor_id' => 42, 'area_name' => 'Saved North', 'deleted_at' => null],
                ['provincial_distributor_id' => 42, 'area_name' => 'Saved North', 'deleted_at' => null],
                ['provincial_distributor_id' => 42, 'area_name' => 'Saved South', 'deleted_at' => null],
                ['provincial_distributor_id' => 42, 'area_name' => 'Disengaged Area', 'deleted_at' => '2026-01-01'],
            ]);
            if ($keepLegacy) {
                DB::table('area_distributors')->where('id', 1)->update(['business_name' => 'Old Business',
                    'delivery_address' => 'Old delivery address', 'business_type' => 'Old business type']);
                DB::table('ad_areas')->insert(['ad_id' => 1, 'area_name' => 'Old Area']);
            } else {
                DB::table('area_distributors')->delete();
            }
        } else {
            DB::table('area_distributors')->where('id', 1)->update($attributes);
            DB::table('ad_areas')->insert([
                ['ad_id' => 1, 'area_name' => 'Saved North'], ['ad_id' => 1, 'area_name' => 'Saved South'],
            ]);
        }
        $this->actingAs(User::findOrFail(1));
        return $attributes;
    }

    public function submitterCases()
    {
        return [[User::ROLE_PROVINCIAL_DISTRIBUTOR, false], [User::ROLE_PROVINCIAL_DISTRIBUTOR, true],
            [User::ROLE_AREA_DISTRIBUTOR, false]];
    }

    /** @dataProvider submitterCases */
    public function test_saved_distributor_profile_populates_the_form_order_and_edit_territories($role, $keepLegacy)
    {
        $profile = $this->submitterProfile($role, $keepLegacy);
        $product = $this->product();
        $this->viewRoot = storage_path('framework/testing/adpo-profile-' . uniqid());
        File::makeDirectory($this->viewRoot . '/layouts', 0755, true);
        File::put($this->viewRoot . '/layouts/header.blade.php', "@yield('content')");
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
        $response = $this->get(route('ad-purchase-orders.create'));
        $response->assertOk();
        $this->assertSame(['Saved North', 'Saved South'], $response->viewData('territoryOptions')->all());
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        foreach (['phone_number' => 'contact_number', 'email_address' => 'email_address',
            'business_type' => 'business_type', 'delivery_address' => 'delivery_address'] as $field => $attribute) {
            $this->assertSame($profile[$attribute], $xpath->query('//input[@name="' . $field . '"]')->item(0)->getAttribute('value'));
        }
        $this->assertSame(1, $xpath->query('//input[@value="Saved Distributor Business"]')->length);
        $response->assertDontSee('No territory assigned');

        $order = $this->store([$product->id => ['selected' => 1, 'qty' => 2]], ['authorized_territory' => 'Saved North']);
        $this->assertSame($profile['business_name'], $order->business_name);
        $this->assertSame($profile['email_address'], $order->email_address);
        $this->assertSame($profile['contact_number'], $order->phone_number);
        $this->assertSame($profile['delivery_address'], $order->delivery_address);
        $this->assertSame('Saved North', $order->authorized_territory);
        $this->assertEquals(1, $order->ad_user_id);
        $this->assertSame('prei_plant', $order->order_source);
        $this->assertNull($order->provincial_distributor_id);
        if ($role === User::ROLE_PROVINCIAL_DISTRIBUTOR && !$keepLegacy) $this->assertNull($order->ad_id);
        else $this->assertEquals(1, $order->ad_id);
        $this->assertSame(['Saved North', 'Saved South'], $this->controller->edit($order->id)->getData()['territoryOptions']->all());
        Mail::assertNothingSent();
    }

    public function test_pd_can_use_vouchers_assigned_to_its_current_profile_and_territory()
    {
        $this->submitterProfile(User::ROLE_PROVINCIAL_DISTRIBUTOR, true);
        $product = $this->product();
        $voucher = \App\Voucher::create(['name' => 'PROFILE-CODE', 'code' => 'PD-VOUCHER',
            'area_names' => ['Saved North'], 'is_active' => true, 'discount_type' => 'fixed',
            'discount_value' => 25, 'used_count' => 0]);
        $this->assertEquals([$voucher->id], $this->controller->create()->getData()['userVouchers']->pluck('id')->all());
        $order = $this->store([$product->id => ['selected' => 1, 'qty' => 2]], [
            'authorized_territory' => 'Saved North', 'use_rebate_voucher' => 'yes', 'voucher_codes' => ['PD-VOUCHER'],
        ]);
        $this->assertEquals(25, $order->rebate_amount);
        $this->assertEquals(1, $voucher->fresh()->used_count);
    }

    public function test_pd_cannot_submit_an_area_from_its_stale_legacy_profile()
    {
        $this->submitterProfile(User::ROLE_PROVINCIAL_DISTRIBUTOR, true);
        $product = $this->product();
        $this->from(route('ad-purchase-orders.create'))->post(route('ad-purchase-orders.store'), [
            'order_source' => 'prei_warehouse', 'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'use_rebate_voucher' => 'no', 'authorized_territory' => 'Old Area',
            'products' => [$product->id => ['selected' => 1, 'qty' => 2]],
        ])->assertSessionHasErrors('authorized_territory');
        $this->assertSame(0, AdPurchaseOrder::count());
    }

    public function test_pd_region_rules_use_its_saved_profile_on_create_and_update()
    {
        $this->submitterProfile(User::ROLE_PROVINCIAL_DISTRIBUTOR, true, 'Region V (Bicol Region)');
        $this->assertFalse($this->controller->create()->getData()['showPickupLubao']);
        $product = $this->product();
        $order = $this->store([$product->id => ['selected' => 1, 'qty' => 2]], ['authorized_territory' => 'Saved North']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->update(new Request(['shipping_type' => 'pickup_lubao', 'payment_method' => 'cash',
            'authorized_territory' => 'Saved North', 'items' => [$order->items->first()->id => ['qty' => 2]],
        ]), $order->id);
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
        $regular = $this->product(['item' => 'Standard product', 'for_ad' => 0, 'price' => 450, 'dprice' => 300.25, 'pdprice' => 225.75]);
        $order = $this->store([
            $collateral->id => ['selected' => 1, 'qty' => 2, 'unit_price' => 1, 'price' => 1],
            $regular->id => ['selected' => 1, 'qty' => 3, 'unit_price' => 1],
        ], ['subtotal' => 1, 'total_amount' => 1]);
        $lines = $order->items->keyBy('product_id');
        $this->assertEquals(175.50, $lines[$collateral->id]->unit_price);
        $this->assertEquals(351, $lines[$collateral->id]->line_total);
        $regularPrice = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 225.75 : 300.25;
        $this->assertEquals($regularPrice, $lines[$regular->id]->unit_price);
        $this->assertEquals($regularPrice * 3, $lines[$regular->id]->line_total);
        $this->assertEquals(351 + $regularPrice * 3, $order->subtotal);
        $this->assertEquals(351 + $regularPrice * 3, $order->total_amount);
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

    private function setBuyerRegion($role, $region)
    {
        DB::table('users')->where('id', 1)->update(['role' => $role]);
        if ($role === User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            DB::table('area_distributors')->delete();
            DB::table('provincial_distributors')->insert(['id' => 1, 'user_id' => 1, 'location_region' => $region]);
        } else {
            DB::table('area_distributors')->where('id', 1)->update(['location_region' => $region]);
        }
        $this->actingAs(User::findOrFail(1));
    }

    public function test_vismin_price_is_used_for_color_and_size_quantities()
    {
        $this->setBuyerRegion(User::ROLE_AREA_DISTRIBUTOR, 'Region X (Northern Mindanao)');
        $uniform = $this->product(['item' => 'Gaz Lite Authorized Retail Partner Uniform', 'vismin_price' => 199.25]);
        $kit = $this->product(['item' => 'Gaz Lite Stove Kit', 'item_type' => 'bundle', 'for_ad' => 0, 'vismin_dprice' => 450]);
        $order = $this->store([
            $uniform->id => ['selected' => 1, 'qty' => 0, 'sizes' => ['small' => 2, 'large' => 3]],
            $kit->id => ['selected' => 1, 'qty' => 0, 'colors' => ['white' => 2, 'green' => 1]],
        ]);
        $this->assertEquals([5, 3], $order->items->pluck('qty')->all());
        $this->assertEquals([199.25, 450], $order->items->pluck('unit_price')->all());
        $this->assertEquals(2346.25, $order->subtotal);
    }

    public function regionalBuyers()
    {
        return [
            ['Area Distributor', 'Region VII (Central Visayas)', true],
            ['Provincial Distributor', 'Region VII (Central Visayas)', true],
            ['Area Distributor', 'Region XIII (Caraga)', true],
            ['Provincial Distributor', 'Region XIII (Caraga)', true],
            ['Area Distributor', 'Region III (Central Luzon)', false],
            ['Provincial Distributor', 'Region III (Central Luzon)', false],
        ];
    }

    /** @dataProvider regionalBuyers */
    public function test_regional_submission_uses_saved_prices_and_ignores_submitted_prices_and_region($role, $region, $vismin)
    {
        $this->setBuyerRegion($role, $region);
        $regular = $this->product(['for_ad' => 0, 'dprice' => 300.25, 'pdprice' => 250.75, 'vismin_price' => 400,
            'vismin_dealer_price' => 350, 'vismin_md_price' => 340, 'vismin_dprice' => 320.50, 'vismin_pdprice' => 275.50]);
        $collateral = $this->product(['vismin_price' => 199.25, 'vismin_dprice' => 150]);
        $unset = $this->product(['for_ad' => 0, 'dprice' => 44, 'pdprice' => 33, 'vismin_price' => 99, 'vismin_dprice' => null]);
        $unsetCollateral = $this->product(['vismin_price' => null, 'vismin_dprice' => 25]);
        $free = $this->product(['for_ad' => 0, 'dprice' => 999, 'pdprice' => 888, 'vismin_price' => 999, 'vismin_dprice' => 0, 'vismin_pdprice' => 0]);
        $order = $this->store([
            $regular->id => ['selected' => 1, 'qty' => 3, 'unit_price' => 1, 'vismin_price' => 1, 'vismin_dprice' => 1],
            $collateral->id => ['selected' => 1, 'qty' => 2, 'unit_price' => 1],
            $unset->id => ['selected' => 1, 'qty' => 4],
            $unsetCollateral->id => ['selected' => 1, 'qty' => 1],
            $free->id => ['selected' => 1, 'qty' => 2],
        ], ['region' => $vismin ? 'Region III' : 'Region VII', 'location_region' => $vismin ? 'Region III' : 'Region VII',
            'use_vismin_price' => !$vismin, 'subtotal' => 1, 'total_amount' => 1]);
        $expected = $vismin ? [320.5, 199.25, 44, 175.5, 0] : [300.25, 175.5, 44, 175.5, 999];
        if ($role === User::ROLE_PROVINCIAL_DISTRIBUTOR) {
            $expected = $vismin ? [275.5, 199.25, 33, 175.5, 0] : [250.75, 175.5, 33, 175.5, 888];
        }
        $this->assertEquals($expected, $order->items->pluck('unit_price')->all());
        $total = $expected[0]*3 + $expected[1]*2 + $expected[2]*4 + $expected[3] + $expected[4]*2;
        $this->assertEquals($total, $order->subtotal);
        $this->assertEquals($total, $order->total_amount);
        $this->assertEquals(12, $order->total_qty);
        Mail::assertNothingSent();
    }

    /** @dataProvider distributorRoles */
    public function test_vismin_previews_and_added_products_match_saved_prices_while_existing_lines_keep_their_price($role)
    {
        $this->setBuyerRegion($role, 'Region XI (Davao Region)');
        $this->viewRoot = storage_path('framework/testing/adpo-vismin-'.uniqid());
        File::makeDirectory($this->viewRoot.'/layouts', 0755, true);
        File::put($this->viewRoot.'/layouts/header.blade.php', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="/design/assets/css/styles.css">@yield("css")</head><body style="padding:20px;background:#e8f0f5">@yield("content")<script src="/design/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>@yield("javascript")</body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
        app('view')->share('errors', new \Illuminate\Support\ViewErrorBag);
        $regular = $this->product(['item' => 'Regular product', 'for_ad' => 0, 'dprice' => 60, 'pdprice' => 50.25,
            'vismin_price' => 110, 'vismin_dprice' => 72.35, 'vismin_pdprice' => 65.45]);
        $regularPrice = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 65.45 : 72.35;
        $collateral = $this->product(['item' => 'Marketing poster', 'vismin_price' => 199.25]);
        $html = view('ad_purchase_orders.create', [
            'ad' => auth()->user()->ad, 'products' => collect([$regular, $collateral]), 'favoriteProductIds' => [],
            'userVouchers' => collect(), 'showPickupLubao' => true, 'isAdmin' => false, 'provincialDistributors' => collect(),
            'selectedOrderSource' => 'prei_warehouse', 'selectedProvincialDistributorId' => null,
            'orderForUser' => auth()->user(), 'isProvincialDistributor' => $role === User::ROLE_PROVINCIAL_DISTRIBUTOR,
            'territoryOptions' => collect(), 'requiresTaxSignature' => false, 'taxProfile' => null,
        ])->render();
        $this->assertContains('data-price="' . $regularPrice . '"', $html);
        $this->assertContains('data-price="199.25"', $html);
        $this->assertContains('PHP ' . number_format($regularPrice, 2), $html);
        $this->assertSame(2, substr_count($html, '>VisMin Price</span>'));
        $order = $this->store([$regular->id => ['selected' => 1, 'qty' => 2]]);
        $regular->update(['vismin_dprice' => 999, 'vismin_pdprice' => 999]);
        $addedProduct = $this->product(['item' => 'Added regular product', 'for_ad' => 0,
            'dprice' => 60, 'pdprice' => 40.25, 'vismin_dprice' => 70, 'vismin_pdprice' => 50.50]);
        $addedPrice = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 50.5 : 70;
        $edit = $this->controller->edit($order->id)->render();
        $this->assertContains('data-price="' . $regularPrice . '"', $edit);
        $this->assertContains('PHP ' . number_format($addedPrice, 2) . ' each', $edit);
        $this->assertContains('PHP 199.25 each', $edit);
        $this->assertContains('VisMin price', $edit);
        $this->controller->update(new Request([
            'shipping_type' => 'delivered', 'payment_method' => 'cash',
            'items' => [$order->items->first()->id => ['qty' => 3, 'unit_price' => 1]],
            'new_products' => [$collateral->id => ['qty' => 2, 'unit_price' => 1], $addedProduct->id => ['qty' => 1, 'unit_price' => 1]],
        ]), $order->id);
        $order = $order->fresh('items');
        $this->assertEquals([$regularPrice, 199.25, $addedPrice], $order->items->pluck('unit_price')->all());
        $this->assertEquals($regularPrice * 3 + 199.25 * 2 + $addedPrice, $order->subtotal);
        $this->assertEquals($regularPrice * 3 + 199.25 * 2 + $addedPrice, $order->total_amount);
        if (getenv('ADPO_VISMIN_UI') === '1') {
            $folder = storage_path('app/qa-adpo-vismin');
            if (!File::isDirectory($folder)) File::makeDirectory($folder, 0755, true);
            $prefix = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? 'pd' : 'ad';
            File::put($folder.'/'.$prefix.'-create.html', $html);
            File::put($folder.'/'.$prefix.'-edit.html', $edit);
        }
        Mail::assertNothingSent();
    }
}
