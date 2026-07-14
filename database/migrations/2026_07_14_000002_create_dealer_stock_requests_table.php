<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerStockRequestsTable extends Migration
{
    public function up()
    {
        // Some existing installations created this table before this migration
        // was added. Do not fail deployment when upgrading those databases.
        if (Schema::hasTable('dealer_stock_requests')) {
            return;
        }

        Schema::create('dealer_stock_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dealer_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('Pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedInteger('approved_order_id')->nullable();
            $table->timestamps();
            $table->index(['dealer_id', 'product_id', 'status']);
            $table->index('status');
            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_order_id')->references('id')->on('order_details')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dealer_stock_requests');
    }
}
