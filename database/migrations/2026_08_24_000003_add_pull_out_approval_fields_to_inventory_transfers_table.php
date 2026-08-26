<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPullOutApprovalFieldsToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('replacement_unit_cost');
            $table->string('warehouse')->nullable()->after('approval_status');
            $table->text('warehouse_remarks')->nullable()->after('warehouse');
            $table->unsignedInteger('reviewed_by')->nullable()->after('warehouse_remarks');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'warehouse', 'warehouse_remarks', 'reviewed_by', 'reviewed_at']);
        });
    }
}
