<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalcolumnToSurveyresulttable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('surveyresult', function (Blueprint $table) {
            
            $table->integer('votes')->after('option_value')->nullable($value=true)->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('surveyresulttable', function (Blueprint $table) {
            //
        });
    }
}
