<?php

namespace Tests\Feature;

use App\Item;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemVisminPriceTest extends TestCase
{
    private $viewRoot;

    protected function setUp()
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('t', 32)), 'audit.enabled' => false,
            'database.default' => 'items_test', 'database.connections.items_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            ],
        ]);
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('item'); $table->text('item_description')->nullable();
            foreach (['price', 'dealer_price', 'md_price', 'dprice'] as $field) $table->decimal($field, 12, 2)->default(0);
            $table->string('item_image')->nullable();
            $table->integer('dealer_points')->default(0); $table->integer('customer_points')->default(0);
            $table->string('status')->default('Activate'); $table->boolean('for_ad')->default(false);
            $table->string('item_type')->default('product'); $table->text('stove_kit_color_availability')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
        DB::table('items')->insert(['id' => 1, 'item' => 'Existing item', 'price' => 100, 'dprice' => 60]);
        require_once database_path('migrations/2026_11_23_000001_add_vismin_price_to_items_table.php');
        (new \AddVisminPriceToItemsTable)->up();
        require_once database_path('migrations/2026_11_23_000002_add_vismin_price_tiers_to_items_table.php');
        (new \AddVisminPriceTiersToItemsTable)->up();
        $admin = new User(['role' => 'Admin', 'name' => 'QA Admin']); $admin->id = 1;
        $this->actingAs($admin);
        $this->viewRoot = storage_path('framework/testing/items-'.uniqid());
        File::makeDirectory($this->viewRoot.'/layouts', 0755, true);
        File::put($this->viewRoot.'/layouts/header.blade.php', '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="/design/assets/css/styles.css">@yield("css")</head><body style="background:#e8f0f5;padding:20px">@yield("content")<script src="/design/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script></body></html>');
        app('view')->getFinder()->prependLocation($this->viewRoot);
        app('events')->forget('composing: layouts.header');
        if (getenv('ITEMS_UI') === '1' && !File::isDirectory(storage_path('app/qa-items-price'))) {
            File::makeDirectory(storage_path('app/qa-items-price'), 0755, true);
        }
    }

    protected function tearDown()
    {
        if ($this->viewRoot) File::deleteDirectory($this->viewRoot);
        parent::tearDown();
    }

    private function payload(array $changes = [])
    {
        return array_replace(['item' => 'New item', 'price' => '100.00', 'dealer_price' => '85.25',
            'md_price' => '75.00', 'dprice' => '60.00', 'status' => 'Activate', 'item_type' => 'product'], $changes);
    }

    public function test_migration_preserves_existing_items_and_does_not_invent_a_price()
    {
        $item = Item::findOrFail(1);
        foreach (['vismin_price', 'vismin_dealer_price', 'vismin_md_price', 'vismin_dprice'] as $field) {
            $this->assertNull($item->{$field});
        }
        $this->assertSame('100.00', $item->price);
        $this->assertSame('60.00', $item->dprice);
    }

    public function test_create_update_zero_clear_and_omitted_price_are_persisted_correctly()
    {
        $prices = ['vismin_price' => '110.25', 'vismin_dealer_price' => '95.35', 'vismin_md_price' => '85.45', 'vismin_dprice' => '72.35'];
        $this->post(route('items.store'), $this->payload($prices))->assertRedirect(route('items'));
        $item = Item::where('item', 'New item')->firstOrFail();
        foreach ($prices as $field => $value) $this->assertSame($value, $item->{$field});
        // Updating just one tier must not overwrite the other three.
        $this->put(route('items.update', $item), $this->payload(['vismin_dprice' => '80.99']))->assertRedirect(route('items'));
        $prices['vismin_dprice'] = '80.99';
        foreach ($prices as $field => $value) $this->assertSame($value, $item->fresh()->{$field});
        $this->put(route('items.update', $item), $this->payload())->assertRedirect(route('items'));
        foreach ($prices as $field => $value) $this->assertSame($value, $item->fresh()->{$field});
        $this->put(route('items.update', $item), $this->payload(array_fill_keys(array_keys($prices), '0')))->assertRedirect(route('items'));
        foreach ($prices as $field => $value) $this->assertSame('0.00', $item->fresh()->{$field});
        $this->put(route('items.update', $item), $this->payload(array_fill_keys(array_keys($prices), '')))->assertRedirect(route('items'));
        foreach ($prices as $field => $value) $this->assertNull($item->fresh()->{$field});
        $this->assertSame('100.00', $item->fresh()->price);
        $this->assertSame('85.25', $item->fresh()->dealer_price);
        $this->assertSame('75.00', $item->fresh()->md_price);
        $this->assertSame('60.00', $item->fresh()->dprice);
    }

    public function invalidPrices()
    {
        $cases = [];
        foreach (['vismin_price', 'vismin_dealer_price', 'vismin_md_price', 'vismin_dprice'] as $field) {
            foreach (['-1', '12.345', 'not a price', '10000000000', '1e3', ['72.35']] as $price) {
                $cases[] = [$field, $price];
            }
        }
        return $cases;
    }

    /** @dataProvider invalidPrices */
    public function test_invalid_prices_do_not_create_or_update_items($field, $price)
    {
        $this->from(route('items'))->post(route('items.store'), $this->payload([$field => $price]))
            ->assertRedirect(route('items'))->assertSessionHasErrors($field);
        $this->assertSame(1, Item::count());
        $this->from(route('items'))->put(route('items.update', 1), $this->payload([$field => $price]))
            ->assertRedirect(route('items'))->assertSessionHasErrors($field);
        $this->assertNull(Item::find(1)->{$field});
    }

    public function test_list_and_edit_show_saved_price_and_zero_separately_from_unset()
    {
        Item::create($this->payload(['item' => '330g LPG Refill', 'vismin_price' => '110.25', 'vismin_dealer_price' => '95.35', 'vismin_md_price' => '85.45', 'vismin_dprice' => '72.35']));
        Item::create($this->payload(['item' => 'Sample bundle', 'item_type' => 'bundle', 'vismin_price' => '0', 'status' => 'Deactivate']));
        $response = $this->get(route('items'))->assertOk();
        $response->assertSee('VisMin')->assertSee('₱72.35')->assertSee('Not set')->assertSee('value="72.35"')->assertSee('value="0.00"');
        foreach (['110.25', '95.35', '85.45'] as $price) $response->assertSee('₱'.$price)->assertSee('value="'.$price.'"');
        if (getenv('ITEMS_UI') === '1') {
            File::put(storage_path('app/qa-items-price/items.html'), $response->getContent());
        }
    }

    public function test_validation_restores_only_the_submitted_edit_modal()
    {
        $item = Item::create($this->payload(['item' => 'Saved item', 'vismin_price' => '72.35']));
        $this->from(route('items'))->put(route('items.update', $item), $this->payload([
            'item' => 'Unsaved name', 'vismin_price' => '-1', '_item_form' => 'itemEditModal'.$item->id,
        ]))->assertSessionHasErrors('vismin_price');
        $response = $this->get(route('items'))->assertOk();
        $dom = new \DOMDocument; @$dom->loadHTML($response->getContent()); $xpath = new \DOMXPath($dom);
        $this->assertSame('-1', $xpath->query('//div[@id="itemEditModal'.$item->id.'"]//input[@name="vismin_price"]')->item(0)->getAttribute('value'));
        $this->assertSame('', $xpath->query('//div[@id="itemCreateModal"]//input[@name="vismin_price"]')->item(0)->getAttribute('value'));
        $this->assertSame('Existing item', $xpath->query('//div[@id="itemEditModal1"]//input[@name="item"]')->item(0)->getAttribute('value'));
        $this->assertSame('Saved item', $item->fresh()->item);
        if (getenv('ITEMS_UI') === '1') File::put(storage_path('app/qa-items-price/invalid.html'), $response->getContent());
    }

    public function test_filters_and_delete_keep_existing_behavior()
    {
        $item = Item::create($this->payload(['item' => 'Marketing item', 'for_ad' => 1, 'vismin_price' => '72.35']));
        $response = $this->get(route('items', ['search' => 'Marketing', 'type' => 'ad', 'status' => 'Activate']))->assertOk();
        $this->assertSame([$item->id], $response->original->getData()['items']->pluck('id')->all());
        $this->delete(route('items.destroy', $item))->assertRedirect(route('items'));
        $this->assertNull(Item::find($item->id));
        $this->assertSame('72.35', Item::withTrashed()->find($item->id)->vismin_price);
    }
}
