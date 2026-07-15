<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceTiersToItemsTable extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('dealer_price', 12, 2)->default(0)->after('price');
            $table->decimal('md_price', 12, 2)->default(0)->after('dealer_price');
            $table->decimal('dprice', 12, 2)->default(0)->after('md_price');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['dealer_price', 'md_price', 'dprice']);
        });
    }
}
