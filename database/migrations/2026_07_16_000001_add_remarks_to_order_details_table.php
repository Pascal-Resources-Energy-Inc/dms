<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToOrderDetailsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('order_details') || Schema::hasColumn('order_details', 'remarks')) {
            return;
        }

        Schema::table('order_details', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('status');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('order_details') || !Schema::hasColumn('order_details', 'remarks')) {
            return;
        }

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
}
