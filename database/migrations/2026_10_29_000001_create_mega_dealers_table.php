<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMegaDealersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('mega_dealers')) {
            Schema::create('mega_dealers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('dealer_reference')->nullable()->index();
                $table->integer('user_id')->nullable()->index();
                $table->string('name')->nullable();
                $table->text('number')->nullable();
                $table->string('facebook')->nullable();
                $table->string('store_name')->nullable();
                $table->string('store_type')->nullable();
                $table->text('address')->nullable();
                $table->string('street_address')->nullable();
                $table->string('location_region')->nullable();
                $table->string('location_province')->nullable();
                $table->string('location_city')->nullable();
                $table->string('location_barangay')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('status')->nullable()->index();
                $table->string('email_address')->nullable();
                $table->longText('avatar')->nullable();
                $table->text('contract')->nullable();
                $table->string('valid_file')->nullable();
                $table->string('valid_id', 150)->nullable();
                $table->string('valid_id_number')->nullable();
                $table->longText('signature')->nullable();
                $table->string('center')->nullable();
                $table->string('spo')->nullable();
                $table->string('mfi')->nullable();
                $table->string('area')->nullable()->index();
                $table->string('dealer_type')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('dealers') || !Schema::hasTable('users')) {
            return;
        }

        $megaDealerUserIds = DB::table('users')
            ->where('role', 'Mega Dealer')
            ->pluck('id');

        if ($megaDealerUserIds->isEmpty()) {
            return;
        }

        $columns = [
            'id', 'dealer_reference', 'user_id', 'name', 'number', 'facebook', 'store_name',
            'store_type', 'address', 'street_address', 'location_region', 'location_province',
            'location_city', 'location_barangay', 'postal_code', 'status', 'email_address',
            'avatar', 'contract', 'valid_file', 'valid_id', 'valid_id_number', 'signature',
            'center', 'spo', 'mfi', 'area', 'dealer_type', 'latitude', 'longitude',
            'created_at', 'updated_at', 'deleted_at',
        ];

        $rows = DB::table('dealers')
            ->whereIn('user_id', $megaDealerUserIds)
            ->get($columns)
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();

        if (!empty($rows)) {
            DB::table('mega_dealers')->insert($rows);
            DB::table('dealers')->whereIn('user_id', $megaDealerUserIds)->delete();
        }
    }

    public function down()
    {
        if (!Schema::hasTable('mega_dealers')) {
            return;
        }

        if (Schema::hasTable('dealers')) {
            $columns = [
                'dealer_reference', 'user_id', 'name', 'number', 'facebook', 'store_name',
                'store_type', 'address', 'street_address', 'location_region', 'location_province',
                'location_city', 'location_barangay', 'postal_code', 'status', 'email_address',
                'avatar', 'contract', 'valid_file', 'valid_id', 'valid_id_number', 'signature',
                'center', 'spo', 'mfi', 'area', 'dealer_type', 'latitude', 'longitude',
                'created_at', 'updated_at', 'deleted_at',
            ];

            DB::table('mega_dealers')->orderBy('id')->get($columns)->each(function ($row) {
                if (!DB::table('dealers')->where('user_id', $row->user_id)->exists()) {
                    DB::table('dealers')->insert((array) $row);
                }
            });
        }

        Schema::dropIfExists('mega_dealers');
    }
}
