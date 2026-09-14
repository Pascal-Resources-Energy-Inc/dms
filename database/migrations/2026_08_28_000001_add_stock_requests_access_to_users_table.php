<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockRequestsAccessToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'can_access_stock_requests')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('can_access_stock_requests')
                    ->nullable()
                    ->after('can_access_settings');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'can_access_stock_requests')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_access_stock_requests');
            });
        }
    }
}
