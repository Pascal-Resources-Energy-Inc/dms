<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTypeToOtherChargesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('other_charges') || Schema::hasColumn('other_charges', 'type')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            $table->string('type')->default('charge')->after('amount');
            $table->index(['type', 'charge_type']);
        });

        DB::table('other_charges')->where('charge_type', 'discount')->update([
            'type' => 'discount',
            'charge_type' => 'fixed',
        ]);
    }

    public function down()
    {
        if (!Schema::hasTable('other_charges') || !Schema::hasColumn('other_charges', 'type')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            $table->dropIndex(['type', 'charge_type']);
            $table->dropColumn('type');
        });
    }
}
