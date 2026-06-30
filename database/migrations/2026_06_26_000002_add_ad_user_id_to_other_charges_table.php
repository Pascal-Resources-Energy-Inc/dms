<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdUserIdToOtherChargesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('other_charges') || Schema::hasColumn('other_charges', 'ad_user_id')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            $table->unsignedInteger('ad_user_id')->nullable()->after('id');
            $table->foreign('ad_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('ad_user_id');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('other_charges') || !Schema::hasColumn('other_charges', 'ad_user_id')) {
            return;
        }

        Schema::table('other_charges', function (Blueprint $table) {
            $table->dropForeign(['ad_user_id']);
            $table->dropIndex(['ad_user_id']);
            $table->dropColumn('ad_user_id');
        });
    }
}
