<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseVerifiedQtyToAdPurchaseOrderVerificationItemsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('ad_purchase_order_verification_items', 'warehouse_verified_qty')) {
            Schema::table('ad_purchase_order_verification_items', function (Blueprint $table) {
                $table->unsignedInteger('warehouse_verified_qty')->nullable()->after('submitted_qty');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ad_purchase_order_verification_items', 'warehouse_verified_qty')) {
            Schema::table('ad_purchase_order_verification_items', function (Blueprint $table) {
                $table->dropColumn('warehouse_verified_qty');
            });
        }
    }
}
