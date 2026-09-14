<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReturnRefundWorkflowToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->string('ris_number')->nullable()->after('reference_no');
            $table->date('return_date')->nullable()->after('ris_number');
            $table->text('return_attachments')->nullable()->after('return_date');
            $table->integer('warehouse_received_qty')->nullable()->after('return_attachments');
            $table->string('warehouse_reference_no')->nullable()->after('warehouse_received_qty');
            $table->timestamp('warehouse_received_at')->nullable()->after('warehouse_reference_no');
            $table->timestamp('ad_notified_at')->nullable()->after('warehouse_received_at');
        });
    }

    public function down()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn(['ris_number', 'return_date', 'return_attachments', 'warehouse_received_qty', 'warehouse_reference_no', 'warehouse_received_at', 'ad_notified_at']);
        });
    }
}
