<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSurveyFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // core snapshot of survey record
        Schema::create('partner_surveys', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('area_distributor_id')->index(); // linked to every AD
            $table->date('period_start');
            $table->date('period_end');
            $table->string('distributor_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('province', 150)->nullable();
            $table->text('awarded_territories')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('respondent_user_id')->nullable()->index();
            $table->string('respondent_name')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Semiannual management review and action classification.
            $table->string('management_classification', 40)->nullable()->index();
            $table->text('classification_rationale')->nullable();
            $table->text('agreed_actions')->nullable();
            $table->unsignedInteger('classified_by_user_id')->nullable()->index();
            $table->timestamp('classified_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['area_distributor_id', 'period_start', 'period_end'],
                'partner_surveys_ad_period_unique'
            );
        });

        // sale channels
        Schema::create('partner_survey_channel_mixes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_survey_id');
            $table->string('channel_code', 50);
            $table->string('other_channel')->nullable();
            $table->decimal('estimated_monthly_sales', 14, 2)->nullable();
            $table->unsignedInteger('active_customers')->nullable();
            $table->timestamps();

            $table->unique(
                ['partner_survey_id', 'channel_code'],
                'partner_survey_channel_unique'
            );
            $table->foreign('partner_survey_id')
                ->references('id')
                ->on('partner_surveys')
                ->onDelete('cascade');
        });

        // 6 mos table montly performance
        Schema::create('partner_survey_monthly_performances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_survey_id');
            $table->date('period_month');
            $table->decimal('sales_php', 14, 2)->default(0);
            $table->decimal('volume_kg', 14, 3)->default(0);
            $table->decimal('target_php', 14, 2)->default(200000);
            $table->string('data_source', 50)->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['partner_survey_id', 'period_month'],
                'partner_survey_month_unique'
            );
            $table->foreign('partner_survey_id')
                ->references('id')
                ->on('partner_surveys')
                ->onDelete('cascade');
        });

        // these are the questions that are bind to every snapshot of survey
        Schema::create('partner_survey_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_survey_id');
            $table->string('section_code', 5);
            $table->string('question_number', 10);
            $table->string('question_code', 100);
            $table->string('item_code', 100)->nullable();
            $table->string('option_code', 100)->nullable();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 20, 4)->nullable();
            $table->boolean('is_not_applicable')->default(false);
            $table->timestamps();

            $table->index(
                ['partner_survey_id', 'question_code'],
                'partner_survey_answer_question_index'
            );
            $table->index(
                ['partner_survey_id', 'section_code', 'question_number'],
                'partner_survey_answer_section_index'
            );
            $table->foreign('partner_survey_id')
                ->references('id')
                ->on('partner_surveys')
                ->onDelete('cascade');
        });
    }

    // i think there is no turning back once it's ran
    // public function down() {
    //     Schema::dropIfExists('partner_survey_answers');
    //     Schema::dropIfExists('partner_survey_monthly_performances');
    //     Schema::dropIfExists('partner_survey_channel_mixes');
    //     Schema::dropIfExists('partner_surveys');
    // }
}
