<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationalProfileFieldsToDistributors extends Migration
{
    public function up()
    {
        foreach (['area_distributors', 'provincial_distributors'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'has_service_vehicle')) {
                    $table->boolean('has_service_vehicle')->nullable()->after('business_type');
                }

                if (!Schema::hasColumn($tableName, 'business_years_operation')) {
                    $table->unsignedSmallInteger('business_years_operation')->nullable()->after('has_service_vehicle');
                }

                if (!Schema::hasColumn($tableName, 'contract_attachments')) {
                    $table->text('contract_attachments')->nullable()->after('business_years_operation');
                }
            });
        }
    }

    public function down()
    {
        foreach (['area_distributors', 'provincial_distributors'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $columns = collect([
                'has_service_vehicle',
                'business_years_operation',
                'contract_attachments',
            ])->filter(function ($column) use ($tableName) {
                return Schema::hasColumn($tableName, $column);
            })->values()->all();

            if ($columns) {
                Schema::table($tableName, function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
}
