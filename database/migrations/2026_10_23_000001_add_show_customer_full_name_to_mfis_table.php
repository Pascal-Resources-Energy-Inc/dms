<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowCustomerFullNameToMfisTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mfis') && ! Schema::hasColumn('mfis', 'show_customer_full_name')) {
            Schema::table('mfis', function (Blueprint $table) {
                $table->boolean('show_customer_full_name')->default(false)->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('mfis') && Schema::hasColumn('mfis', 'show_customer_full_name')) {
            Schema::table('mfis', function (Blueprint $table) {
                $table->dropColumn('show_customer_full_name');
            });
        }
    }
}
