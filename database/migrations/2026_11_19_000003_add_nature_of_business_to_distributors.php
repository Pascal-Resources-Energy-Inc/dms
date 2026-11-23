<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNatureOfBusinessToDistributors extends Migration
{
    public function up()
    {
        foreach (['area_distributors', 'provincial_distributors'] as $name) {
            if (!Schema::hasColumn($name, 'nature_of_business')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->string('nature_of_business')->nullable();
                });
            }
        }
    }

    public function down()
    {
        foreach (['area_distributors', 'provincial_distributors'] as $name) {
            Schema::table($name, function (Blueprint $table) { $table->dropColumn('nature_of_business'); });
        }
    }
}
