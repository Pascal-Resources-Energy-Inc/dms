<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessTypeToDistributorAreas extends Migration
{
    public function up()
    {
        foreach (['ad_areas', 'provincial_distributor_areas'] as $name) {
            if (!Schema::hasColumn($name, 'business_type')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->string('business_type')->nullable();
                });
            }
        }
    }

    public function down()
    {
        foreach (['ad_areas', 'provincial_distributor_areas'] as $name) {
            Schema::table($name, function (Blueprint $table) { $table->dropColumn('business_type'); });
        }
    }
}
