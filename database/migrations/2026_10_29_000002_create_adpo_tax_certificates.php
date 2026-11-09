<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdpoTaxCertificates extends Migration
{
    public function up()
    {
        Schema::create('adpo_tax_certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ad_purchase_order_id')->index();
            $table->unsignedInteger('signed_by');
            $table->string('signer_name');
            $table->timestamp('signed_at');
            $table->text('snapshot');
            $table->mediumText('signature');
            $table->longText('pdf_base64');
            $table->string('sha256', 64);
            $table->timestamp('superseded_at')->nullable();
            $table->timestamps();
        });
        Schema::create('adpo_tax_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('certificate_id');
            $table->string('email');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
            $table->unique(['certificate_id', 'email']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('adpo_tax_deliveries');
        Schema::dropIfExists('adpo_tax_certificates');
    }
}
