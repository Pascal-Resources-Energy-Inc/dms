<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessAndOfferSheetToDistributorAreas extends Migration
{
    public function up()
    {
        foreach (['ad_areas', 'provincial_distributor_areas'] as $name) {
            foreach (['business_name', 'offer_sheet_path', 'offer_sheet_name'] as $column) {
                if (!Schema::hasColumn($name, $column)) {
                    Schema::table($name, function (Blueprint $table) use ($column) {
                        $table->string($column)->nullable();
                    });
                }
            }
        }
    }

    public function down()
    {
        foreach (['ad_areas', 'provincial_distributor_areas'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['business_name', 'offer_sheet_path', 'offer_sheet_name']);
            });
        }
    }
}
