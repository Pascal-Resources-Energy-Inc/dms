<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditMemoFieldsToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->string('credit_memo_reference_no')->nullable()->after('warehouse_reference_no');
            $table->text('credit_memo_attachments')->nullable()->after('credit_memo_reference_no');
            $table->unsignedBigInteger('credit_memo_submitted_by')->nullable()->after('credit_memo_attachments');
            $table->timestamp('credit_memo_submitted_at')->nullable()->after('credit_memo_submitted_by');
        });
    }

    public function down()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn([
                'credit_memo_reference_no',
                'credit_memo_attachments',
                'credit_memo_submitted_by',
                'credit_memo_submitted_at',
            ]);
        });
    }
}
