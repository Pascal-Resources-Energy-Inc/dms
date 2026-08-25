<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReplacementDrNumberToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transfers', 'replacement_dr_number')) {
                $table->string('replacement_dr_number')->nullable()->after('replacement_unit_cost');
            }
        });
    }

    public function down()
    {
        if (Schema::hasColumn('inventory_transfers', 'replacement_dr_number')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                $table->dropColumn('replacement_dr_number');
            });
        }
    }
}
