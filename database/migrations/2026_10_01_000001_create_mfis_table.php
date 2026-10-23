<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMfisTable extends Migration
{
    public function up()
    {
        Schema::create('mfis', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->boolean('show_customer_full_name')->default(false);
            $table->timestamps();
        });

        $names = ['SEDP', 'ASHI'];

        if (Schema::hasTable('centers') && Schema::hasColumn('centers', 'mfi')) {
            $names = array_merge($names, DB::table('centers')->whereNotNull('mfi')->pluck('mfi')->all());
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'mfi_type')) {
            $names = array_merge($names, DB::table('users')->whereNotNull('mfi_type')->pluck('mfi_type')->all());
        }

        foreach (array_unique(array_filter(array_map(function ($name) {
            return strtoupper(trim((string) $name));
        }, $names))) as $name) {
            DB::table('mfis')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('mfis');
    }
}
