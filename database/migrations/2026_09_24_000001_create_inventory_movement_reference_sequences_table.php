<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryMovementReferenceSequencesTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_movement_reference_sequences', function (Blueprint $table) {
            $table->date('reference_date')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_movement_reference_sequences');
    }
}
