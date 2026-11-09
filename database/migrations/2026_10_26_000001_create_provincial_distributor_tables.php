<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProvincialDistributorTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('provincial_distributors')) {
            Schema::create('provincial_distributors', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id')->unique();
                $table->string('ad_reference', 50)->nullable()->unique();
                $table->string('store_code', 100)->nullable();
                $table->string('avatar')->nullable();
                $table->string('name')->nullable();
                $table->date('joining_date')->nullable();
                $table->string('attachment')->nullable();
                $table->string('facebook')->nullable();
                $table->string('email_address')->nullable();
                $table->text('address')->nullable();
                $table->text('delivery_address')->nullable();
                $table->string('street_address')->nullable();
                $table->string('location_region')->nullable()->index();
                $table->string('location_province')->nullable();
                $table->string('location_city')->nullable();
                $table->string('location_barangay')->nullable();
                $table->string('zipcode', 20)->nullable();
                $table->string('contact_number', 50)->nullable();
                $table->string('status', 30)->default('Active')->index();
                $table->string('business_name')->nullable();
                $table->string('business_type')->nullable();
                $table->string('tin', 50)->nullable();
                $table->string('store_picture')->nullable();
                $table->boolean('withholding_tax')->default(false);
                $table->decimal('latitude', 11, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('provincial_distributor_areas')) {
            Schema::create('provincial_distributor_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('provincial_distributor_id')->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('area_name');
                $table->string('project_type')->nullable();
                $table->string('user_role')->default('Provincial Distributor');
                $table->date('joining_date')->nullable();
                $table->decimal('package_amount', 12, 2)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['provincial_distributor_id', 'area_name'], 'pd_area_lookup');
            });
        }

        $this->copyExistingProvincialDistributors();
    }

    private function copyExistingProvincialDistributors()
    {
        if (!Schema::hasTable('area_distributors') || !Schema::hasTable('users')) {
            return;
        }

        $profileColumns = Schema::getColumnListing('provincial_distributors');
        $legacyProfiles = DB::table('area_distributors')
            ->join('users', 'users.id', '=', 'area_distributors.user_id')
            ->where('users.role', 'Provincial Distributor')
            ->select('area_distributors.*')
            ->get();

        foreach ($legacyProfiles as $legacyProfile) {
            $legacyData = (array) $legacyProfile;
            $legacyId = $legacyData['id'];
            unset($legacyData['id']);

            $profileData = array_intersect_key($legacyData, array_flip($profileColumns));
            $existingId = DB::table('provincial_distributors')
                ->where('user_id', $legacyData['user_id'])
                ->value('id');

            $provincialDistributorId = $existingId ?: DB::table('provincial_distributors')->insertGetId($profileData);

            if (!Schema::hasTable('ad_areas')) {
                continue;
            }

            $areaColumns = Schema::getColumnListing('provincial_distributor_areas');
            $legacyAreas = DB::table('ad_areas')->where('ad_id', $legacyId)->get();

            foreach ($legacyAreas as $legacyArea) {
                $areaData = (array) $legacyArea;
                unset($areaData['id'], $areaData['ad_id'], $areaData['ad_user_id']);
                $areaData['provincial_distributor_id'] = $provincialDistributorId;
                $areaData['user_id'] = $legacyData['user_id'];
                $areaData['user_role'] = 'Provincial Distributor';
                $areaData = array_intersect_key($areaData, array_flip($areaColumns));

                DB::table('provincial_distributor_areas')->insert($areaData);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('provincial_distributor_areas');
        Schema::dropIfExists('provincial_distributors');
    }
}
