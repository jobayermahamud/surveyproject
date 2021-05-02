<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    public function index(Request $request){
        $surveylist=Survey::all();
        return view('survey_list',compact('surveylist'));
    }


    
    public function changeStatus(Request $request,int $surveyId){
        if (! $request->hasValidSignature()) {
            return json_encode(array('status'=>'403','msg'=>'Unauthorized'));
        }

        $survey=Survey::find($surveyId);
        if($survey->survey_status==1){
            $survey->survey_status=0;
            $survey->save();
            return json_encode(array('status'=>'200','msg'=>'survey Inactivated'));
        }
        if($survey->survey_status==0){
            $survey->survey_status=1;
            $survey->save();
            return json_encode(array('status'=>'200','msg'=>'survey Activated'));
        }

        
    }


    public function deleteSurvey(Request $request,int $surveyId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        DB::transaction(function () use($surveyId){
            $Survey=Survey::find($surveyId);
            $Survey->delete();
            DB::table('survey_question')->where('survey_id',$surveyId)->delete();
        });

        $request->session()->flash('delete_survey', 'Survey deleted');

        return redirect('survey');

    }


    public function addSurvey(Request $request){
        if($request->isMethod('post')){
            // echo '<pre>';
            // print_r($request->all());
            // return;

            if($request->input('survey_name')==''){
                $request->session()->flash('error','Survey name is required');
                return redirect()->back();
            }
            if($request->input('date_from')==''){
                $request->session()->flash('error','Date from is required');
                return redirect()->back();
            }

            if($request->input('date_to')==''){
                $request->session()->flash('error','Date to is required');
                return redirect()->back();
            }

            

            if(!$request->has('question_list')){
                $request->session()->flash('error','You must select atleast one question');
                return redirect()->back();
            }

            

            DB::transaction(function () use($request){
                $survey=new Survey;
                $survey->survey_name=$request->input('survey_name');
                $survey->date_from=$request->input('date_from');
                $survey->date_to=$request->input('date_to');
                $survey->survey_status=$request->input('sts_opt');
                $survey->save();
                
                for($i=0;$i<count($request->input('question_list'));$i++){
                        DB::table('survey_question')->insert([
                            'survey_id' =>$survey->id,
                            'question_id'=>$request->input('question_list')[$i]
                        ]);
                }

                
                

            });


            return redirect('survey');


        }
        $questions=Question::where('is_active',1)->get();
        return view('add_survey',compact('questions'));
    }





    public function surveyDetails(Request $request,$surveyId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        $surveyDetails=DB::table('survey')
                         ->join('survey_question','survey.id','=','survey_question.survey_id')
                         ->join('question','survey_question.question_id','=','question.id')
                         ->select('survey.*','question.question_name')
                         ->where('survey.id',$surveyId)
                         ->get();


        // echo '<pre>';
        // print_r($surveyDetails);
        // return;
         return view('survey_details',compact('surveyDetails'));                


    }



    public function surveyEdit(Request $request,int $surveyId){
        if (!$request->hasValidSignature()) {
            abort(401);
        }

        if($request->isMethod('post')){
            if($request->input('survey_name')==''){
                $request->session()->flash('error','Survey name is required');
                return redirect()->back();
            }
            if($request->input('date_from')==''){
                $request->session()->flash('error','Date from is required');
                return redirect()->back();
            }

            if($request->input('date_to')==''){
                $request->session()->flash('error','Date to is required');
                return redirect()->back();
            }
            $survey=Survey::find($surveyId);
            $survey->survey_name=$request->input('survey_name');
            $survey->date_from=$request->input('date_from');
            $survey->date_to=$request->input('date_to');
            $survey->survey_status=$request->input('sts_opt');

            $survey->save();


            
            $request->session()->flash('success','Information updated');
            return redirect()->back();



        }

        $surveyDetails=DB::table('survey')
                         ->join('survey_question','survey.id','=','survey_question.survey_id')
                         ->join('question','survey_question.question_id','=','question.id')
                         ->select('survey.*','survey_question.question_id as survey_question_id','question.question_name','question.id as question_id')
                         ->where('survey.id',$surveyId)
                         ->orderBy('question.id')
                         ->get();

        $questions=DB::table('question')->get();               
        // echo '<pre>';
        // print_r($surveyDetails);
        // return;                 
        return view('edit_survey',compact('surveyDetails','questions'));
    }


    public function addQuestion(Request $request,$qId,$sId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        DB::table('survey_question')->insert([
                            'survey_id' =>$sId,
                            'question_id'=>$qId
        ]);

        return json_encode(array('status'=>200,'msg'=>'New question added'));

    }

    public function rmQuestion(Request $request,$qId,$sId){
        DB::beginTransaction();
            DB::table('survey_question')->where('question_id',$qId)->where('survey_id',$sId)->delete();
            $checkQuestion=DB::table('survey_question')->where('survey_id',$sId)->get();
            
            if(count($checkQuestion)>0){
                DB::commit();
            }else{
                DB::rollBack();
                return json_encode(array('status'=>403,'msg'=>'You must select at least one question'));
            }
    }



    public function surveyReportList(Request $request){
        // $surveyList=DB::table('surveyresult')
        //             ->join('survey','surveyresult.survey_id','=','survey.id')
        //             ->select(DB::raw('surveyresult.option_value,survey.id,survey.survey_name,COUNT(surveyresult.survey_id) as total'))
        //             ->groupBy('survey.id')
        //             ->groupBy('surveyresult.option_value')
        //             ->groupBy('survey.survey_name')
        //             ->where('surveyresult.option_type',1)
        //             ->get();
        
        $surveyList=DB::table('surveyresult')
                    ->join('survey','surveyresult.survey_id','=','survey.id')
                    ->select('survey.*')
                    ->distinct()
                    ->get();            


        //  echo '<pre>';
        //  print_r($surveyList);
        //  return;           
        return view('survey_report_list',compact('surveyList'));
    }


    public function surveyReportFull(Request $request,$surveyId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }


        $surveyMultipleChooseResult=DB::table('surveyresult')
                    ->join('survey','surveyresult.survey_id','=','survey.id')
                    ->join('question','surveyresult.question_id','=','question.id')
                    ->select(DB::raw('question.id as question_id,question.question_name,surveyresult.option_value,survey.id,survey.survey_name,COUNT(surveyresult.survey_id) as total'))
                    ->groupBy('survey.id')
                    ->groupBy('surveyresult.option_value')
                    ->groupBy('survey.survey_name')
                    ->groupBy('question.id')
                    ->groupBy('question.question_name')
                    ->where('surveyresult.option_type',1)
                    ->where('survey.id',$surveyId)
                    ->orderBy('total')
                    ->orderBy('surveyresult.option_id')
                    ->get();
        
        $surveyTextOption= DB::table('surveyresult')
                    ->join('survey','surveyresult.survey_id','=','survey.id')
                    ->join('question','surveyresult.question_id','=','question.id')
                    ->select('question.id as question_id','question.question_name','surveyresult.option_value','survey.id','survey.survey_name')
                    ->where('survey.id',$surveyId)
                    ->where('surveyresult.option_type',0)
                    ->get();
        
        


        $processData=array();
        for($i=0;$i<count($surveyMultipleChooseResult);$i++){
            //echo $surveyMultipleChooseResult[$i]->question_id;
            if(isset($processData[$surveyMultipleChooseResult[$i]->question_id])){
                array_push($processData[$surveyMultipleChooseResult[$i]->question_id],$surveyMultipleChooseResult[$i]);
            }else{
                $processData[$surveyMultipleChooseResult[$i]->question_id]=array();
                array_push($processData[$surveyMultipleChooseResult[$i]->question_id],$surveyMultipleChooseResult[$i]);
            }
        }

        $survey=Survey::where('id',$surveyId)->get()[0];

        echo '<pre>';
        print_r($surveyMultipleChooseResult);
        return;            
        
        return view('details_report',compact('processData','survey','surveyTextOption'));

    }
    
}
