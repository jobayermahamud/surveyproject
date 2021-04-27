<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuestionController;

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
Route::match(['get', 'post'], 'add_question',[QuestionController::class,'addQuestion']);


// QuestionControllerRoute end
