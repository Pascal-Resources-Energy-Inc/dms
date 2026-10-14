<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRisDateToInventoryTransfersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('inventory_transfers', 'ris_date')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                $table->date('ris_date')->nullable()->after('ris_number');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('inventory_transfers', 'ris_date')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                $table->dropColumn('ris_date');
            });
        }
    }
}
