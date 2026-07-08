<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTinAndStorePictureToAreaDistributorsTable extends Migration
{
    public function up()
    {
        Schema::table('area_distributors', function (Blueprint $table) {
            if (!Schema::hasColumn('area_distributors', 'tin')) {
                $table->string('tin')->nullable()->after('business_type');
            }

            if (!Schema::hasColumn('area_distributors', 'store_picture')) {
                $table->string('store_picture')->nullable()->after('tin');
            }
        });
    }

    public function down()
    {
        Schema::table('area_distributors', function (Blueprint $table) {
            if (Schema::hasColumn('area_distributors', 'store_picture')) {
                $table->dropColumn('store_picture');
            }

            if (Schema::hasColumn('area_distributors', 'tin')) {
                $table->dropColumn('tin');
            }
        });
    }
}
