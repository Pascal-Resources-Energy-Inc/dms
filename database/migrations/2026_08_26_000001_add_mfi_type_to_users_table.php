<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMfiTypeToUsersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('users', 'mfi_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mfi_type')->nullable()->after('role');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'mfi_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('mfi_type');
            });
        }
    }
}
