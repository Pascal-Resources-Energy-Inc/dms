<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderSourceToAdPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('ad_purchase_orders', function (Blueprint $table) {
            $table->string('order_source', 32)->default('prei_warehouse')->after('ad_user_id');
            $table->unsignedInteger('provincial_distributor_id')->nullable()->after('order_source');
            $table->index('provincial_distributor_id');
        });
    }

    public function down()
    {
        Schema::table('ad_purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['provincial_distributor_id']);
            $table->dropColumn(['order_source', 'provincial_distributor_id']);
        });
    }
}
