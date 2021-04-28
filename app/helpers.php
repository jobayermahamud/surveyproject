<?php
use Illuminate\Support\Facades\DB;

function checkQuestionExists($questionId,$surveyId){
      $data=DB::table('survey_question')->where('survey_id',$surveyId)->where('question_id',$questionId)->get();

      if(count($data)>0){
           return true;
      }else{
          return false;
      }
}

?>