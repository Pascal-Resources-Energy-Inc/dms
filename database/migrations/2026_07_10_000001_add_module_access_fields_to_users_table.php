<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuleAccessFieldsToUsersTable extends Migration
{
    private $columns = [
        'can_access_transactions',
        'can_access_distributors',
        'can_access_dealers',
        'can_access_customers',
        'can_access_purchase_orders',
        'can_access_inventory',
        'can_access_reports',
        'can_access_settings',
    ];

    public function up()
    {
        $afterColumn = Schema::hasColumn('users', 'can_delete_rewards')
            ? 'can_delete_rewards'
            : (Schema::hasColumn('users', 'can_delete')
                ? 'can_delete'
                : (Schema::hasColumn('users', 'status') ? 'status' : 'remember_token'));

        Schema::table('users', function (Blueprint $table) use (&$afterColumn) {
            foreach ($this->columns as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->string($column)->nullable()->after($afterColumn);
                    $afterColumn = $column;
                }
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter($this->columns, function ($column) {
                return Schema::hasColumn('users', $column);
            });

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
