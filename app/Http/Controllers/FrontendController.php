<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;


class FrontendController extends Controller
{
    public function index(Request $request){
        $date=date('Y-m-d',getdate()[0]);
        $survey=DB::table('survey')
                ->join('survey_question','survey_question.survey_id','=','survey.id')
                ->join('question','survey_question.question_id','=','question.id')
                ->leftJoin('question_option','question.id','=','question_option.question_id')
                ->select('survey.*','question.id as question_id','question.option_type','question.question_name','question_option.id as option_id','question_option.option_title')
                ->where('survey.date_to','>=',$date)->where('survey.survey_status',1)
                ->get()->toArray();
    
        $processData=array();

        for($i=0;$i<count($survey);$i++){
            if(isset($processData[$survey[$i]->question_id])){
                   array_push($processData[$survey[$i]->question_id],(array)$survey[$i]);
            }else{
                $processData[$survey[$i]->question_id]=array();
                array_push($processData[$survey[$i]->question_id],(array)$survey[$i]);
            }
        }
        
        // echo '<pre>';
        // foreach ($processData as $key => $value) {
        //     for($i=0;$i<count($value);$i++){
        //         print_r($value[$i]['question_name']);
        //     }
        // }
        // echo '<pre>';
        // print_r($processData);
        // return;
        return view('front',compact('processData'));
    } 


    public function multiOption(Request $request,$surveyId,$questionId,$optionId,$optionValue){
        if (!$request->hasValidSignature()) {
            return json_encode(array('msg'=>'Unauthorized action'));
        }

        $check=DB::table('surveyresult')
               ->where('survey_id',$surveyId)
               ->where('question_id',$questionId)
               ->where('option_id',$optionId)
               ->where('option_value',$optionValue)->get();
        
        if(count($check)>0){
            DB::table('surveyresult')
               ->where('survey_id',$surveyId)
               ->where('question_id',$questionId)
               ->where('option_id',$optionId)
               ->where('option_value',$optionValue)->update(['votes'=>($check[0]->votes+1)]);
        }else{
             DB::table('surveyresult')->insert([
                'survey_id' => $surveyId,
                'question_id' => $questionId,
                'option_id' => $optionId,
                'option_value' => $optionValue,
                'option_type' => 1,
                'votes'=>1
            ]);
        }       
               
               

       

        return json_encode(array('msg'=>'Thanks for your opinion'));
    }


    public function textOption(Request $request,$surveyId,$questionId){
        if (!$request->hasValidSignature()) {
            return json_encode(array('msg'=>'Unauthorized action'));
        }

        


        DB::table('surveyresult')->insert([
            'survey_id' => $surveyId,
            'question_id' => $questionId,
            'option_id' =>0,
            'option_value' =>$request->all()['comment_text'],
            'option_type' => 0
        ]);

        

        return json_encode(array('msg'=>'Thanks for your opinion'));
    }
}
