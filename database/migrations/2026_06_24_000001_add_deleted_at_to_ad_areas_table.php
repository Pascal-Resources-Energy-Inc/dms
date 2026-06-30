<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToAdAreasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ad_areas') && !Schema::hasColumn('ad_areas', 'deleted_at')) {
            Schema::table('ad_areas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('ad_areas') && Schema::hasColumn('ad_areas', 'deleted_at')) {
            Schema::table('ad_areas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
