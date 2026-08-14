<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ScopeOtherChargeUniqueIndexesToAdUser extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('other_charges') || !Schema::hasColumn('other_charges', 'ad_user_id')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            // The original schema made `code` globally unique. Charges belong to
            // individual AD users, so names and codes must only be unique per AD.
            $table->dropUnique('other_charges_code_unique');
            $table->unique(['ad_user_id', 'code'], 'other_charges_ad_user_code_unique');
            $table->unique(['ad_user_id', 'name'], 'other_charges_ad_user_name_unique');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('other_charges') || !Schema::hasColumn('other_charges', 'ad_user_id')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            $table->dropUnique('other_charges_ad_user_code_unique');
            $table->dropUnique('other_charges_ad_user_name_unique');
        });
    }
}
