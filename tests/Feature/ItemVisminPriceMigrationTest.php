<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ItemVisminPriceMigrationTest extends TestCase
{
    public function test_splitting_tiers_preserves_legacy_dpo_rates_and_does_not_repopulate_cleared_prices()
    {
        config(['database.default' => 'price_migration_test', 'database.connections.price_migration_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('vismin_price', 12, 2)->nullable();
        });
        DB::table('items')->insert([
            ['id' => 1, 'vismin_price' => 72.35], ['id' => 2, 'vismin_price' => 0], ['id' => 3, 'vismin_price' => null],
        ]);
        require_once database_path('migrations/2026_11_23_000002_add_vismin_price_tiers_to_items_table.php');
        $migration = new \AddVisminPriceTiersToItemsTable;
        $migration->up();
        $this->assertEquals(72.35, DB::table('items')->where('id', 1)->value('vismin_dprice'));
        $this->assertEquals(0, DB::table('items')->where('id', 2)->value('vismin_dprice'));
        $this->assertNull(DB::table('items')->where('id', 3)->value('vismin_dprice'));
        foreach (DB::table('items')->get() as $item) {
            $this->assertEquals($item->vismin_price, $item->vismin_dprice);
            $this->assertNull($item->vismin_dealer_price);
            $this->assertNull($item->vismin_md_price);
        }
        DB::table('items')->where('id', 1)->update(['vismin_dprice' => null]);
        $migration->up();
        $this->assertNull(DB::table('items')->where('id', 1)->value('vismin_dprice'));
        $this->assertEquals(72.35, DB::table('items')->where('id', 1)->value('vismin_price'));
    }
}
