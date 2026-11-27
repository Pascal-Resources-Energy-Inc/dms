<?php

namespace Tests\Feature;

use App\AreaDistributor;
use App\Item;
use App\Product;
use App\ProvincialDistributor;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductVisminPricingTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['audit.enabled' => false, 'app.key' => 'base64:'.base64_encode(str_repeat('t', 32)),
            'database.default' => 'product_price_test', 'database.connections.product_price_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        DB::statement('CREATE TABLE items (id INTEGER PRIMARY KEY, item TEXT, item_description TEXT, item_image TEXT,
            price NUMERIC, dealer_price NUMERIC, md_price NUMERIC, dprice NUMERIC, pdprice NUMERIC, vismin_price NUMERIC,
            vismin_dealer_price NUMERIC, vismin_md_price NUMERIC, vismin_dprice NUMERIC, vismin_pdprice NUMERIC, for_ad INTEGER,
            dealer_points INTEGER, customer_points INTEGER, status TEXT, item_type TEXT,
            created_at TEXT, updated_at TEXT, deleted_at TEXT)');
        DB::statement('CREATE TABLE products (id INTEGER PRIMARY KEY, item_id INTEGER, product_name TEXT,
            description TEXT, sku TEXT, ad_user_id INTEGER, price NUMERIC, client_price NUMERIC,
            dealer_price NUMERIC, mega_dealer_price NUMERIC, deposit NUMERIC, status TEXT,
            item_type TEXT, product_image TEXT, bundle_product_ids TEXT, is_new INTEGER,
            dealer_points INTEGER, customer_points INTEGER, created_at TEXT, updated_at TEXT)');
        $this->signIn(User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)');
        $this->viewRoot = storage_path('framework/testing/products-prices-'.uniqid());
        File::makeDirectory($this->viewRoot.'/layouts', 0755, true);
        File::put($this->viewRoot.'/layouts/header.blade.php', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="/design/assets/css/styles.css">@yield("css")</head><body style="padding:20px;background:#e8f0f5">@yield("content")<script src="/login_css/js/jquery-3.3.1.min.js"></script><script src="/design/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script><script src="/design/assets/libs/sweetalert2/dist/sweetalert2.min.js"></script>@yield("js")</body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function signIn($role, $region)
    {
        $user = new User(['role' => $role, 'name' => 'QA Distributor']);
        $user->id = 1;
        $user->setRelation('ad', $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? null : new AreaDistributor(['location_region' => $region]));
        $user->setRelation('provincialDistributor', $role === User::ROLE_PROVINCIAL_DISTRIBUTOR ? new ProvincialDistributor(['location_region' => $region]) : null);
        $this->actingAs($user);
    }

    private function item(array $attributes = [])
    {
        return Item::create($attributes + ['item' => '330g LPG Refill', 'price' => 100, 'dealer_price' => 85,
            'md_price' => 75, 'dprice' => 60, 'pdprice' => 50.25, 'vismin_price' => 110.25, 'vismin_dealer_price' => 95.35,
            'vismin_md_price' => 85.45, 'vismin_dprice' => 72.35, 'vismin_pdprice' => 65.45, 'status' => 'Activate', 'item_type' => 'product']);
    }

    private function product(Item $item = null, array $attributes = [])
    {
        return Product::create($attributes + ['item_id' => $item ? $item->id : null, 'product_name' => $item ? $item->item : 'Custom product',
            'price' => 140, 'client_price' => 141, 'dealer_price' => 120, 'mega_dealer_price' => 115,
            'ad_user_id' => 1, 'sku' => uniqid('QA-'), 'status' => 'Activate', 'is_new' => 1, 'item_type' => 'product']);
    }

    private function payload(array $attributes = [])
    {
        return $attributes + ['selected_item_type' => 'product', 'product_name' => 'QA product', 'sku' => uniqid('QA-'),
            'price' => '101.00', 'client_price' => '102.00', 'dealer_price' => '86.00', 'mega_dealer_price' => '76.00', 'status' => 'Activate'];
    }

    private function xpath($html)
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        return new \DOMXPath($dom);
    }

    public function buyerCases()
    {
        return [
            [User::ROLE_AREA_DISTRIBUTOR, 'Region VII (Central Visayas)', true],
            [User::ROLE_AREA_DISTRIBUTOR, 'Region X (Northern Mindanao)', true],
            [User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region VIII (Eastern Visayas)', true],
            [User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region XI (Davao Region)', true],
            [User::ROLE_AREA_DISTRIBUTOR, 'Region V (Bicol Region)', false],
            [User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region III (Central Luzon)', false],
            ['Admin', 'Region VII (Central Visayas)', false],
            ['Dealer', 'Region VII (Central Visayas)', false],
        ];
    }

    /** @dataProvider buyerCases */
    public function test_list_matrix_and_saves_use_the_signed_in_distributors_region($role, $region, $regional)
    {
        $this->signIn($role, $region);
        $item = $this->item();
        $product = $this->product($item);
        $catalog = $this->get(route('products'))->assertOk()->getContent();
        $xpath = $this->xpath($catalog);
        foreach (['Price' => $regional ? 110.25 : 140, 'Dealer' => $regional ? 95.35 : 120, 'Mega dealer' => $regional ? 85.45 : 115] as $label => $expected) {
            $this->assertSame('₱'.number_format($expected, 2), trim($xpath->query('//tr[@id="row-'.$product->id.'"]/td[@data-label="'.$label.'"]')->item(0)->textContent));
        }
        $this->assertSame($regional ? '110.25' : '140.00', $xpath->query('//tr[@id="row-'.$product->id.'"]//button[@data-price]')->item(0)->getAttribute('data-price'));
        $this->assertSame('140.00', $product->fresh()->price, 'Viewing the catalog must not write product prices.');
        $matrix = $this->get(route('products.create'))->assertOk()->getContent();
        $xpath = $this->xpath($matrix);
        $isPd = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR;
        $costField = $isPd ? 'pdprice' : 'dprice';
        $expectedCost = $isPd ? ($regional ? 65.45 : 50.25) : ($regional ? 72.35 : 60);
        foreach (['client_price' => $regional ? 110.25 : 100, 'dealer_price' => $regional ? 95.35 : 85, 'mega_dealer_price' => $regional ? 85.45 : 75, $costField => $expectedCost] as $field => $expected) {
            $this->assertEquals($expected, $xpath->query('//input[@name="items[0]['.$field.']"]')->item(0)->getAttribute('value'));
        }
        // Posted prices and region cannot replace configured rates from Items.
        $this->post(route('products.storeBulk'), ['region' => 'Region III', 'items' => [[
            'item_id' => $item->id, 'selected' => 1, 'price' => 1, 'client_price' => 2, 'dealer_price' => 3, 'mega_dealer_price' => 4,
        ]]])->assertRedirect()->assertSessionMissing('error');
        $product = $product->fresh();
        $this->assertSame($regional ? '110.25' : '100.00', $product->price);
        $this->assertSame($regional ? '110.25' : '100.00', $product->client_price);
        $this->assertSame($regional ? '95.35' : '85.00', $product->dealer_price);
        $this->assertSame($regional ? '85.45' : '75.00', $product->mega_dealer_price);
        $this->assertSame(1, Product::count());
        $this->saveFixture($role, $region, 'catalog', $catalog);
        $this->saveFixture($role, $region, 'matrix', $matrix);
    }

    public function test_each_missing_tier_keeps_its_existing_price_while_zero_is_a_real_price()
    {
        $item = $this->item(['vismin_price' => null, 'vismin_dealer_price' => 0, 'vismin_md_price' => null]);
        $product = $this->product($item);
        $this->assertSame(['price' => '140.00', 'client_price' => '141.00', 'dealer_price' => '0.00', 'mega_dealer_price' => '115.00'], $product->sellingPricesFor(auth()->user()));
        $matrix = $this->xpath($this->get(route('products.create'))->assertOk()->getContent());
        foreach (['client_price' => 100, 'dealer_price' => 0, 'mega_dealer_price' => 75] as $field => $expected) {
            $this->assertEquals($expected, $matrix->query('//input[@name="items[0]['.$field.']"]')->item(0)->getAttribute('value'));
        }
        $this->post(route('products.storeBulk'), ['items' => [[
            'item_id' => $item->id, 'selected' => 1, 'client_price' => 100, 'dealer_price' => 85, 'mega_dealer_price' => 75,
        ]]])->assertRedirect()->assertSessionMissing('error');
        $this->assertSame('100.00', $product->fresh()->price);
        $this->assertSame('0.00', $product->fresh()->dealer_price);
        $this->assertSame('75.00', $product->fresh()->mega_dealer_price);
        $item->update(['vismin_price' => 0, 'vismin_dealer_price' => null, 'vismin_md_price' => 0]);
        $product = $product->fresh();
        $this->assertSame('0.00', $product->sellingPricesFor(auth()->user())['price']);
        $this->assertSame('0.00', $product->sellingPricesFor(auth()->user())['mega_dealer_price']);
    }

    public function test_matrix_can_create_products_and_deactivate_without_overwriting_prices()
    {
        $item = $this->item();
        $this->post(route('products.storeBulk'), ['items' => [['item_id' => $item->id, 'selected' => 1]]])->assertRedirect()->assertSessionMissing('error');
        $product = Product::firstOrFail();
        $this->assertSame('110.25', $product->price);
        $this->assertSame('95.35', $product->dealer_price);
        $this->assertSame('85.45', $product->mega_dealer_price);
        $item->update(['vismin_price' => 200]);
        $this->post(route('products.storeBulk'), ['items' => [['item_id' => $item->id]]])->assertRedirect()->assertSessionMissing('error');
        $this->assertSame('Deactivate', $product->fresh()->status);
        $this->assertSame('110.25', $product->fresh()->price);
    }

    public function test_matrix_cost_uses_each_roles_own_tier_and_falls_back_only_when_its_regional_price_is_unset()
    {
        $item = $this->item();
        foreach ([User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR] as $role) {
            $isPd = $role === User::ROLE_PROVINCIAL_DISTRIBUTOR;
            $field = $isPd ? 'pdprice' : 'dprice';
            $basePrice = $isPd ? '50.25' : '60.00';
            foreach (['Region VII (Central Visayas)', 'Region XI (Davao Region)'] as $region) {
                $this->signIn($role, $region);
                foreach ([[null, $basePrice], [0, '0.00'], [$isPd ? 65.45 : 72.35, $isPd ? '65.45' : '72.35']] as [$price, $expected]) {
                    $item->update(['vismin_'.$field => $price]);
                    $matrix = $this->xpath($this->get(route('products.create'))->assertOk()->getContent());
                    $cost = $matrix->query('//input[@name="items[0]['.$field.']"]')->item(0);
                    $this->assertSame($expected, $cost->getAttribute('value'));
                    $this->assertTrue($cost->hasAttribute('readonly'));
                    $this->assertSame($basePrice, $item->fresh()->{$field});
                    $this->assertSame('110.25', $matrix->query('//input[@name="items[0][client_price]"]')->item(0)->getAttribute('value'));
                }
            }
        }
    }

    public function test_matrix_keeps_existing_cost_for_legacy_items_without_a_pd_price()
    {
        $item = $this->item(['pdprice' => null, 'vismin_pdprice' => null]);
        foreach (['Region V (Bicol Region)' => '60.00', 'Region VII (Central Visayas)' => '72.35'] as $region => $expected) {
            $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR, $region);
            $matrix = $this->xpath($this->get(route('products.create'))->assertOk()->getContent());
            $this->assertSame($expected, $matrix->query('//td[@data-label="Cost"]//input')->item(0)->getAttribute('value'));
        }
        $this->assertNull($item->fresh()->pdprice);
        $this->assertNull($item->fresh()->vismin_pdprice);
    }

    public function test_matrix_save_uses_current_item_fallbacks_for_missing_and_stale_browser_prices()
    {
        $item = $this->item(['vismin_price' => null, 'vismin_dealer_price' => 0, 'vismin_md_price' => null]);
        foreach ([User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR] as $role) {
            $this->signIn($role, 'Region XI (Davao Region)');
            // These read-only fields may be omitted or contain an old regional price.
            foreach ([[], ['client_price' => 999, 'price' => 999, 'dealer_price' => 999, 'mega_dealer_price' => 999]] as $postedPrices) {
                $this->post(route('products.storeBulk'), ['items' => [
                    ['item_id' => $item->id, 'selected' => 1] + $postedPrices,
                ]])->assertRedirect()->assertSessionMissing('error');
                $product = Product::where('item_id', $item->id)->firstOrFail();
                $this->assertSame('100.00', $product->price);
                $this->assertSame('100.00', $product->client_price);
                $this->assertSame('0.00', $product->dealer_price);
                $this->assertSame('75.00', $product->mega_dealer_price);
            }
        }
        $this->assertSame(1, Product::count());
    }

    public function test_single_product_create_and_edit_save_each_tier_and_ignore_spoofed_item_links()
    {
        $this->signIn(User::ROLE_PROVINCIAL_DISTRIBUTOR, 'Region XI');
        $item = $this->item(['vismin_dealer_price' => null]);
        $this->post(route('products.store'), $this->payload(['item_id' => $item->id]))->assertRedirect(route('products'));
        $product = Product::firstOrFail();
        $this->assertSame('110.25', $product->price);
        $this->assertSame('86.00', $product->dealer_price);
        $this->assertSame('85.45', $product->mega_dealer_price);
        $item->update(['vismin_price' => 0, 'vismin_md_price' => null]);
        $other = $this->item(['vismin_price' => 999]);
        $this->putJson('/products/'.$product->id, $this->payload(['item_id' => $other->id]))->assertOk()->assertJson(['success' => true]);
        $this->assertSame('0.00', $product->fresh()->price);
        $this->assertSame('0.00', $product->fresh()->client_price);
        $this->assertSame('86.00', $product->fresh()->dealer_price);
        $this->assertSame('76.00', $product->fresh()->mega_dealer_price);
        $this->assertEquals($item->id, $product->fresh()->item_id);
    }

    public function test_unlinked_and_deleted_item_products_keep_their_existing_prices()
    {
        $custom = $this->product();
        $item = $this->item();
        $linked = $this->product($item);
        $item->delete();
        foreach ([$custom, $linked] as $product) {
            $this->assertSame('140.00', $product->sellingPricesFor(auth()->user())['price']);
            $this->assertSame('120.00', $product->sellingPricesFor(auth()->user())['dealer_price']);
            $this->assertSame('115.00', $product->sellingPricesFor(auth()->user())['mega_dealer_price']);
        }
        $this->post(route('products.store'), $this->payload())->assertRedirect(route('products'));
        $created = Product::orderBy('id', 'desc')->firstOrFail();
        $this->assertSame('101.00', $created->price);
        $this->assertSame('86.00', $created->dealer_price);
        $this->assertSame('76.00', $created->mega_dealer_price);
    }

    public function test_distributor_cannot_apply_their_prices_to_another_users_product()
    {
        $product = $this->product($this->item(), ['ad_user_id' => 2]);
        $this->putJson('/products/'.$product->id, $this->payload())->assertNotFound();
        $this->assertSame('140.00', $product->fresh()->price);
        $this->get(route('products'))->assertOk()->assertDontSee('row-'.$product->id.'"');
    }

    private function saveFixture($role, $region, $name, $html)
    {
        if (getenv('PRODUCT_PRICES_UI') !== '1' || !in_array($role, [User::ROLE_AREA_DISTRIBUTOR, User::ROLE_PROVINCIAL_DISTRIBUTOR], true)) return;
        $folder = storage_path('app/qa-product-vismin');
        if (!File::isDirectory($folder)) File::makeDirectory($folder, 0755, true);
        $roleName = $role === User::ROLE_AREA_DISTRIBUTOR ? 'ad' : 'pd';
        $regionName = strpos($region, 'Visayas') !== false ? 'visayas' : (strpos($region, 'Mindanao') !== false || strpos($region, 'Davao') !== false ? 'mindanao' : 'luzon');
        File::put($folder.'/'.$roleName.'-'.$regionName.'-'.$name.'.html', $html);
    }
}
