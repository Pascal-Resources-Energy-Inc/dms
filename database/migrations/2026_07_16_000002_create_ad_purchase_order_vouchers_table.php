<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdPurchaseOrderVouchersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ad_purchase_order_vouchers')) {
            return;
        }

        Schema::create('ad_purchase_order_vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ad_purchase_order_id');
            $table->unsignedInteger('voucher_id');
            $table->decimal('rebate_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['ad_purchase_order_id', 'voucher_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ad_purchase_order_vouchers');
    }
}
