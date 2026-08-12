<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationItemsToAdPurchaseOrdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('ad_purchase_orders', 'verification_items')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                $table->text('verification_items')->nullable()->after('verification_proofs');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ad_purchase_orders', 'verification_items')) {
            Schema::table('ad_purchase_orders', function (Blueprint $table) {
                $table->dropColumn('verification_items');
            });
        }
    }
}
