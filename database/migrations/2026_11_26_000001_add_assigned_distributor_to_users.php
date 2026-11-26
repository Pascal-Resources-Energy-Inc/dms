<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedDistributorToUsers extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'assigned_distributor_user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('assigned_distributor_user_id')->nullable()->index();
            });
        }
        foreach (['personnel_contact_number', 'personnel_facebook'] as $column) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable();
                });
            }
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('assigned_distributor_user_id');
            $table->dropColumn(['personnel_contact_number', 'personnel_facebook']);
        });
    }
}
