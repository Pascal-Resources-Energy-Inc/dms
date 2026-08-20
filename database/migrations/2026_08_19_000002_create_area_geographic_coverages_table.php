<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaGeographicCoveragesTable extends Migration
{
    public function up()
    {
        Schema::create('area_geographic_coverages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('area_id');
            $table->string('region');
            $table->string('province');
            $table->string('city_municipality');
            $table->string('barangay');
            $table->timestamps();
            $table->index('area_id');
        });

        DB::table('areas')->orderBy('id')->get()->each(function ($area) {
            if ($area->region && $area->province && $area->city_municipality && $area->barangay) {
                DB::table('area_geographic_coverages')->insert([
                    'area_id' => $area->id,
                    'region' => $area->region,
                    'province' => $area->province,
                    'city_municipality' => $area->city_municipality,
                    'barangay' => $area->barangay,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('area_geographic_coverages');
    }
}
