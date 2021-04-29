<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSurveyresultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surveyresult', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('survey_id');
            $table->bigInteger('question_id');
            $table->tinyInteger('option_type');
            $table->bigInteger('option_id');
            $table->string('option_value')->nullable($value = true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surveyresult');
    }
}
