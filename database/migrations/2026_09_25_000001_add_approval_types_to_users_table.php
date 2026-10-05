<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalTypesToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'approval_types')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('approval_types')->nullable()->after('department');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'approval_types')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('approval_types');
            });
        }
    }
}
