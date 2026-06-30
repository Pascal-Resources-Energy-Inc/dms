<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDistributorProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'user_reference' => function () use ($table) { $table->string('user_reference')->nullable()->unique()->after('status'); },
                'project_tag' => function () use ($table) { $table->string('project_tag')->nullable()->after('user_reference'); },
                'first_name' => function () use ($table) { $table->string('first_name')->nullable()->after('project_tag'); },
                'middle_name' => function () use ($table) { $table->string('middle_name')->nullable()->after('first_name'); },
                'last_name' => function () use ($table) { $table->string('last_name')->nullable()->after('middle_name'); },
                'mobile_number' => function () use ($table) { $table->string('mobile_number')->nullable()->after('last_name'); },
                'birthdate' => function () use ($table) { $table->date('birthdate')->nullable()->after('mobile_number'); },
                'age' => function () use ($table) { $table->unsignedTinyInteger('age')->nullable()->after('birthdate'); },
                'facebook' => function () use ($table) { $table->string('facebook')->nullable()->after('age'); },
                'mother_name' => function () use ($table) { $table->string('mother_name')->nullable()->after('facebook'); },
                'business_name' => function () use ($table) { $table->string('business_name')->nullable()->after('mother_name'); },
                'business_type' => function () use ($table) { $table->string('business_type')->nullable()->after('business_name'); },
                'avatar_path' => function () use ($table) { $table->string('avatar_path')->nullable()->after('business_type'); },
                'attachment_path' => function () use ($table) { $table->string('attachment_path')->nullable()->after('avatar_path'); },
                'withholding_tax' => function () use ($table) { $table->boolean('withholding_tax')->default(false)->after('attachment_path'); },
                'street_address' => function () use ($table) { $table->string('street_address')->nullable()->after('withholding_tax'); },
                'region' => function () use ($table) { $table->string('region')->nullable()->after('street_address'); },
                'province' => function () use ($table) { $table->string('province')->nullable()->after('region'); },
                'city' => function () use ($table) { $table->string('city')->nullable()->after('province'); },
                'barangay' => function () use ($table) { $table->string('barangay')->nullable()->after('city'); },
                'zip_code' => function () use ($table) { $table->string('zip_code')->nullable()->after('barangay'); },
                'delivery_same_as_address' => function () use ($table) { $table->boolean('delivery_same_as_address')->default(true)->after('zip_code'); },
                'delivery_address' => function () use ($table) { $table->text('delivery_address')->nullable()->after('delivery_same_as_address'); },
                'latitude' => function () use ($table) { $table->decimal('latitude', 10, 7)->nullable()->after('delivery_address'); },
                'longitude' => function () use ($table) { $table->decimal('longitude', 10, 7)->nullable()->after('latitude'); },
                'complete_address' => function () use ($table) { $table->text('complete_address')->nullable()->after('longitude'); },
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('users', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'complete_address', 'longitude', 'latitude', 'delivery_address',
                'delivery_same_as_address', 'zip_code', 'barangay', 'city',
                'province', 'region', 'street_address', 'withholding_tax',
                'attachment_path', 'avatar_path', 'business_type',
                'business_name', 'mother_name', 'facebook', 'age',
                'birthdate', 'mobile_number', 'last_name', 'middle_name',
                'first_name', 'project_tag', 'user_reference',
            ];

            $existing = array_filter($columns, function ($column) {
                return Schema::hasColumn('users', $column);
            });

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
