<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsOnHoldToAdPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ad_purchase_orders')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('ad_purchase_orders', 'is_on_hold')) {
                    $table->boolean('is_on_hold')->default(0)->after('remarks');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('ad_purchase_orders')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('ad_purchase_orders', 'is_on_hold')) {
                    $table->dropColumn('is_on_hold');
                }
            });
        }
    }
}
