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


function getTotalAnswer($questionId,$surveyId){
    $data=DB::table('surveyresult')->where('survey_id',$surveyId)->where('question_id',$questionId)->get();

    return count($data);
}

function getQuestionOptions($questionId){
    return DB::table('question_option')->where('question_id',$questionId)->get();
}

function getVotes($surveyId,$questionId,$optionId){
    $votes=DB::table('surveyresult')->where('survey_id',$surveyId)->where('question_id',$questionId)->where('option_id',$optionId)->get();
    if(count($votes)>0){
         return $votes[0]->votes;
    }else{
        return 0;
    }
}

function getTexts($surveyId,$questionId){
    $texts=DB::table('surveyresult')->where('survey_id',$surveyId)->where('question_id',$questionId)->get();
    return $texts;
}

?>