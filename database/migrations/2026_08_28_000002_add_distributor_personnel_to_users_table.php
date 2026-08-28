<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistributorPersonnelToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'is_distributor_personnel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_distributor_personnel')->default(false)->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'is_distributor_personnel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_distributor_personnel');
            });
        }
    }
}
