<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdPurchaseOrderPaymentProofsTable extends Migration
{
    public function up()
    {
        Schema::create('ad_purchase_order_payment_proofs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ad_purchase_order_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('ad_purchase_order_id')
                ->references('id')
                ->on('ad_purchase_orders')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ad_purchase_order_payment_proofs');
    }
}
