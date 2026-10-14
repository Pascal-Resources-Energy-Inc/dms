<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchNumberToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->string('batch_number')->nullable()->index()->after('reference_no');
        });
    }

    public function down()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropIndex(['batch_number']);
            $table->dropColumn('batch_number');
        });
    }
}
