<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReplacementFieldsToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transfers', 'related_movement_id')) {
                $table->unsignedInteger('related_movement_id')->nullable()->after('pull_out_attachments');
                $table->unsignedInteger('replacement_product_id')->nullable()->after('related_movement_id');
                $table->string('replacement_sku')->nullable()->after('replacement_product_id');
                $table->string('replacement_item_name')->nullable()->after('replacement_sku');
                $table->integer('replacement_qty')->nullable()->after('replacement_item_name');
                $table->decimal('replacement_unit_cost', 10, 2)->nullable()->after('replacement_qty');
            }
        });
    }

    public function down()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn(['related_movement_id', 'replacement_product_id', 'replacement_sku', 'replacement_item_name', 'replacement_qty', 'replacement_unit_cost']);
        });
    }
}
