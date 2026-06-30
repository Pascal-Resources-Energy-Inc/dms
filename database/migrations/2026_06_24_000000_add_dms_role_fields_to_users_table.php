<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDmsRoleFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('dealer')->after('password');
            }

            if (! Schema::hasColumn('users', 'territory')) {
                $table->string('territory')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('territory');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter(['role', 'territory', 'status'], function ($column) {
                return Schema::hasColumn('users', $column);
            });

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
