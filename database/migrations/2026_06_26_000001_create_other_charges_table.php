<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherChargesTable extends Migration
{
    public function up()
    {
        Schema::create('other_charges', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('charge_type')->default('fixed');
            $table->string('applies_to')->default('order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'charge_type']);
            $table->index('applies_to');
        });
    }

    public function down()
    {
        Schema::dropIfExists('other_charges');
    }
}
