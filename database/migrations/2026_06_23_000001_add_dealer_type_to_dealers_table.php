<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerTypeToDealersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('dealers', 'dealer_type')) {
            Schema::table('dealers', function (Blueprint $table) {
                $table->string('dealer_type', 20)->default('Project')->after('store_type');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('dealers', 'dealer_type')) {
            Schema::table('dealers', function (Blueprint $table) {
                $table->dropColumn('dealer_type');
            });
        }
    }
}
