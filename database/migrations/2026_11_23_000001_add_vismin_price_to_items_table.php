<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisminPriceToItemsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('items', 'vismin_price')) {
            Schema::table('items', function (Blueprint $table) {
                $table->decimal('vismin_price', 12, 2)->nullable()->after('dprice');
            });
        }
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('vismin_price');
        });
    }
}
