<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminAndAreaFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'warehouse' => function () use ($table) { $table->string('warehouse')->nullable()->after('complete_address'); },
                'designation' => function () use ($table) { $table->string('designation')->nullable()->after('warehouse'); },
                'employee_number' => function () use ($table) { $table->string('employee_number')->nullable()->after('designation'); },
                'department' => function () use ($table) { $table->string('department')->nullable()->after('employee_number'); },
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('users', $column)) {
                    $definition();
                }
            }
        });

        if (! Schema::hasTable('user_awarded_areas')) {
            Schema::create('user_awarded_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->date('joining_date')->nullable();
                $table->string('area_name_rise')->nullable();
                $table->string('area_name_genesis')->nullable();
                $table->timestamps();

                $table->index('user_id');
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['department', 'employee_number', 'designation', 'warehouse'];

            $existing = array_filter($columns, function ($column) {
                return Schema::hasColumn('users', $column);
            });

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        Schema::dropIfExists('user_awarded_areas');
    }
}
