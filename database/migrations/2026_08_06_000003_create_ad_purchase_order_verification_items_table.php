<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdPurchaseOrderVerificationItemsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ad_purchase_order_verification_items')) {
            Schema::create('ad_purchase_order_verification_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('ad_purchase_order_id');
                $table->unsignedBigInteger('ad_purchase_order_item_id');
                $table->string('product_name');
                $table->unsignedInteger('ordered_qty');
                $table->unsignedInteger('submitted_qty');
                $table->timestamps();

                $table->unique(['ad_purchase_order_id', 'ad_purchase_order_item_id'], 'adpo_verification_item_unique');
                $table->index('ad_purchase_order_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ad_purchase_order_verification_items');
    }
}
