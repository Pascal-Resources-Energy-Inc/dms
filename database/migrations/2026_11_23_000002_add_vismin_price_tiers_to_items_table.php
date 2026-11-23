<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVisminPriceTiersToItemsTable extends Migration
{
    public function up()
    {
        $addDistributorPrice = !Schema::hasColumn('items', 'vismin_dprice');
        foreach (['vismin_dealer_price', 'vismin_md_price', 'vismin_dprice'] as $column) {
            if (!Schema::hasColumn('items', $column)) {
                Schema::table('items', function (Blueprint $table) use ($column) {
                    $table->decimal($column, 12, 2)->nullable();
                });
            }
        }

        // The former single rate covered both SRP and distributor DPO prices.
        // Retain those rates when splitting the tiers, including configured zeroes.
        if ($addDistributorPrice) {
            DB::table('items')->whereNotNull('vismin_price')->whereNull('vismin_dprice')
                ->update(['vismin_dprice' => DB::raw('vismin_price')]);
        }
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['vismin_dealer_price', 'vismin_md_price', 'vismin_dprice']);
        });
    }
}
