<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseVerificationProofsToAdPurchaseOrdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('ad_purchase_orders', 'warehouse_verification_proofs')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                $table->text('warehouse_verification_proofs')->nullable()->after('verification_items');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ad_purchase_orders', 'warehouse_verification_proofs')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                $table->dropColumn('warehouse_verification_proofs');
            });
        }
    }
}
