<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageAmountToAdAreasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ad_areas') && !Schema::hasColumn('ad_areas', 'package_amount')) {
            Schema::table('ad_areas', function (Blueprint $table) {
                $table->decimal('package_amount', 12, 2)->nullable()->after('joining_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ad_areas') && Schema::hasColumn('ad_areas', 'package_amount')) {
            Schema::table('ad_areas', function (Blueprint $table) {
                $table->dropColumn('package_amount');
            });
        }
    }
}
