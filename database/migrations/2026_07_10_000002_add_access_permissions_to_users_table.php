<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessPermissionsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'access_permissions')) {
                $afterColumn = Schema::hasColumn('users', 'can_access_settings')
                    ? 'can_access_settings'
                    : (Schema::hasColumn('users', 'can_delete_rewards') ? 'can_delete_rewards' : 'remember_token');

                $table->text('access_permissions')->nullable()->after($afterColumn);
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'access_permissions')) {
                $table->dropColumn('access_permissions');
            }
        });
    }
}
