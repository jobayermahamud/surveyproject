<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\FrontendController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



// QuestionControllerRoute start

Route::match(['get'], 'questions',[QuestionController::class,'index']);
Route::match(['get'], 'question_status_change/{questionId}',[QuestionController::class,'changeStatus'])->name('change_status');
Route::match(['get'], 'question_delete/{questionId}',[QuestionController::class,'deleteQuestion'])->name('delete_question');
Route::match(['get'], 'question_details/{questionId}',[QuestionController::class,'questionDetails'])->name('question_details');
Route::match(['get','post'], 'question_edit/{questionId}',[QuestionController::class,'questionEdit'])->name('question_edit');

Route::match(['get', 'post'], 'add_question',[QuestionController::class,'addQuestion']);

Route::match(['post'], 'question_add_new_option/{questionId}',[QuestionController::class,'questionAddNewOption'])->name('question_add_new_option');
Route::match(['post'], 'question_edit_option/{optionId}',[QuestionController::class,'questionEditOption'])->name('question_edit_option');
Route::match(['get'], 'question_delete_option/{optionId}',[QuestionController::class,'questionOptionDelete'])->name('question_delete_option');

// QuestionControllerRoute end


// Survey route start

Route::match(['get', 'post'], 'addsurvey',[SurveyController::class,'addSurvey']);
Route::match(['get'], 'survey',[SurveyController::class,'index'])->name('survey');
Route::match(['get'], 'survey_delete/{surveyId}',[SurveyController::class,'deleteSurvey'])->name('delete_survey');
Route::match(['get'], 'survey_details/{surveyId}',[SurveyController::class,'surveyDetails'])->name('survey_details');
Route::match(['get'], 'survey_status_change/{surveyId}',[SurveyController::class,'changeStatus'])->name('change_survey_status');
Route::match(['get'], 'add_question/{qId}/{sId}',[SurveyController::class,'addQuestion'])->name('add_question');
Route::match(['get'], 'rm_question/{qId}/{sId}',[SurveyController::class,'rmQuestion'])->name('rm_question');
Route::match(['get','post'], 'survey_edit/{surveyId}',[SurveyController::class,'surveyEdit'])->name('survey_edit');

// Survey Route end



// Frontend route start

Route::match(['get'], 'surveyfront',[FrontendController::class,'index']);


Route::match(['get'], 'multiopt/{surveyId}/{questionId}/{optionId}/{optionValue}',[FrontendController::class,'multiOption'])->name('ml_opt');
Route::match(['post'], 'textopt/{surveyId}/{questionId}',[FrontendController::class,'textOption'])->name('text_opt');


// Forintend route end