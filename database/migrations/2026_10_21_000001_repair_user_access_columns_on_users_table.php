<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairUserAccessColumnsOnUsersTable extends Migration
{
    public function up()
    {
        $stringColumns = [
            'can_edit',
            'can_add',
            'can_delete',
            'can_edit_rewards',
            'can_add_rewards',
            'can_delete_rewards',
            'can_access_transactions',
            'can_access_distributors',
            'can_access_dealers',
            'can_access_customers',
            'can_access_purchase_orders',
            'can_access_inventory',
            'can_access_reports',
            'can_access_settings',
            'can_access_stock_requests',
        ];

        foreach ($stringColumns as $column) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable();
                });
            }
        }

        if (!Schema::hasColumn('users', 'access_permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('access_permissions')->nullable();
            });
        }
    }

    public function down()
    {
        // This is a schema-repair migration. Existing access columns must not be
        // removed during rollback because they may predate this migration.
    }
}
