<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPullOutAttachmentsToInventoryTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_transfers', 'pull_out_attachments')) {
                $table->text('pull_out_attachments')->nullable()->after('reference_no');
            }
        });
    }

    public function down()
    {
        if (Schema::hasColumn('inventory_transfers', 'pull_out_attachments')) {
            Schema::table('inventory_transfers', function (Blueprint $table) {
                $table->dropColumn('pull_out_attachments');
            });
        }
    }
}
