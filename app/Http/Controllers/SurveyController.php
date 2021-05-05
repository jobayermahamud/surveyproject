<?php

namespace App\Http\Controllers;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


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


        

        $survey=Survey::where('id',$surveyId)->get()[0];

        $surveyQuestionList=DB::table('survey_question')
                           ->join('question','question.id','=','survey_question.question_id')
                           ->join('survey','survey.id','=','survey_question.survey_id')
                           ->where('survey_id',$surveyId)
                           ->orderBy('question.id','desc')
                           ->get();

        return view('details_report',compact('surveyQuestionList','survey')); 
        
        
    

       
        
        

    }

    public function exportExcel(Request $request,$surveyId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $surveyQuestionList=DB::table('survey_question')
                           ->join('question','question.id','=','survey_question.question_id')
                           ->join('survey','survey.id','=','survey_question.survey_id')
                           ->where('survey_id',$surveyId)
                           ->orderBy('question.id','desc')
                           ->get();
        
        $survey=Survey::find($surveyId);

        // echo '<pre>';
        // print_r($surveyQuestionList);
        // return;
        
        

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $cellCounter=2;
        $sheet->setCellValue('A1','Question name');
        $spreadsheet->getActiveSheet()->getStyle('A1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
          $spreadsheet->getActiveSheet()->getStyle('A1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A1')
                    ->getFill()->getStartColor()->setARGB('000000');  
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);
        $sheet->setCellValue('B1','Option');
        $spreadsheet->getActiveSheet()->getStyle('B1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $spreadsheet->getActiveSheet()->getStyle('B1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('B1')
                    ->getFill()->getStartColor()->setARGB('000000');   
        
        $sheet->setCellValue('C1','Total');
        $spreadsheet->getActiveSheet()->getStyle('C1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $spreadsheet->getActiveSheet()->getStyle('C1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
          $spreadsheet->getActiveSheet()->getStyle('C1')
                    ->getFill()->getStartColor()->setARGB('000000');   
        

        foreach ($surveyQuestionList as $question) {
            $arrayToMerge=array();
            if($question->option_type){
                $options=getQuestionOptions($question->question_id);
                
                foreach ($options as $option) {
                    array_push($arrayToMerge,$cellCounter);
                    $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                    $sheet->setCellValue('B'.$cellCounter,$option->option_title);
                    $sheet->setCellValue('C'.$cellCounter,getVotes($question->survey_id,$question->question_id,$option->id));
                    $cellCounter++;
                }
            }else{
                $texts=getTexts($question->survey_id,$question->question_id);
                //"A".$cellCounter.':A'.((count($texts)+$cellCounter)-1);
                //$cellCounter++;
                if(count($texts)>0){
                    foreach ($texts as $text) {
                        array_push($arrayToMerge,$cellCounter);
                        //$spreadsheet->getActiveSheet()->mergeCells('A'.$cellCounter.':A'.((count($texts)+$cellCounter)-1));
                        $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                        $sheet->setCellValue('B'.$cellCounter,$text->option_value);
                        $sheet->setCellValue('C'.$cellCounter,'');
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$cellCounter.':C'.$cellCounter);
                        $cellCounter++;
                    }
                    
                }else{
                    array_push($arrayToMerge,$cellCounter);
                    //$spreadsheet->getActiveSheet()->mergeCells('A'.$cellCounter.':A'.$cellCounter);
                    $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                    $sheet->setCellValue('B'.$cellCounter,"No answer");
                    $sheet->setCellValue('C'.$cellCounter,'');
                    $spreadsheet->getActiveSheet()->mergeCells('B'.$cellCounter.':C'.$cellCounter);
                    $cellCounter++;
                }
            }

            $spreadsheet->getActiveSheet()->mergeCells('A'.$arrayToMerge[0].':A'.$arrayToMerge[count($arrayToMerge)-1]);
        }            

        $writer = new Xlsx($spreadsheet);
        $writer->save($survey->survey_name.'.xlsx');            

        
        return Storage::download($survey->survey_name.'.xlsx');
        
        

    }




     public function surveyPdfExport(Request $request,$surveyId){
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        $surveyQuestionList=DB::table('survey_question')
                           ->join('question','question.id','=','survey_question.question_id')
                           ->join('survey','survey.id','=','survey_question.survey_id')
                           ->where('survey_id',$surveyId)
                           ->orderBy('question.id','desc')
                           ->get();


        // echo '<pre>';
        // print_r($surveyQuestionList);
        // return;  
        

        return view('survey_pdf',compact('surveyQuestionList')); 
        
        
    

       
        
        

    }
    



    public function surveyReportTest(Request $request,$surveyId){
        $surveyQuestionList=DB::table('survey_question')
                           ->join('question','question.id','=','survey_question.question_id')
                           ->join('survey','survey.id','=','survey_question.survey_id')
                           ->where('survey_id',$surveyId)
                           ->orderBy('question.id','desc')
                           ->get();
        
        $survey=Survey::find($surveyId);

        // echo '<pre>';
        // print_r($surveyQuestionList);
        // return;
        
        

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $cellCounter=2;
        $sheet->setCellValue('A1','Question name');
        $spreadsheet->getActiveSheet()->getStyle('A1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
          $spreadsheet->getActiveSheet()->getStyle('A1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A1')
                    ->getFill()->getStartColor()->setARGB('000000');  
        $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);
        $sheet->setCellValue('B1','Option');
        $spreadsheet->getActiveSheet()->getStyle('B1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $spreadsheet->getActiveSheet()->getStyle('B1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('B1')
                    ->getFill()->getStartColor()->setARGB('000000');   
        
        $sheet->setCellValue('C1','Total');
        $spreadsheet->getActiveSheet()->getStyle('C1')
          ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $spreadsheet->getActiveSheet()->getStyle('C1')
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
          $spreadsheet->getActiveSheet()->getStyle('C1')
                    ->getFill()->getStartColor()->setARGB('000000');   
        

        foreach ($surveyQuestionList as $question) {
            $arrayToMerge=array();
            if($question->option_type){
                $options=getQuestionOptions($question->question_id);
                
                foreach ($options as $option) {
                    array_push($arrayToMerge,$cellCounter);
                    $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                    $sheet->setCellValue('B'.$cellCounter,$option->option_title);
                    $sheet->setCellValue('C'.$cellCounter,getVotes($question->survey_id,$question->question_id,$option->id));
                    $cellCounter++;
                }
            }else{
                $texts=getTexts($question->survey_id,$question->question_id);
                //"A".$cellCounter.':A'.((count($texts)+$cellCounter)-1);
                //$cellCounter++;
                if(count($texts)>0){
                    foreach ($texts as $text) {
                        array_push($arrayToMerge,$cellCounter);
                        //$spreadsheet->getActiveSheet()->mergeCells('A'.$cellCounter.':A'.((count($texts)+$cellCounter)-1));
                        $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                        $sheet->setCellValue('B'.$cellCounter,$text->option_value);
                        $sheet->setCellValue('C'.$cellCounter,'');
                        $spreadsheet->getActiveSheet()->mergeCells('B'.$cellCounter.':C'.$cellCounter);
                        $cellCounter++;
                    }
                    
                }else{
                    array_push($arrayToMerge,$cellCounter);
                    //$spreadsheet->getActiveSheet()->mergeCells('A'.$cellCounter.':A'.$cellCounter);
                    $sheet->setCellValue('A'.$cellCounter,$question->question_name);
                    $sheet->setCellValue('B'.$cellCounter,"No answer");
                    $sheet->setCellValue('C'.$cellCounter,'');
                    $spreadsheet->getActiveSheet()->mergeCells('B'.$cellCounter.':C'.$cellCounter);
                    $cellCounter++;
                }
            }

            $spreadsheet->getActiveSheet()->mergeCells('A'.$arrayToMerge[0].':A'.$arrayToMerge[count($arrayToMerge)-1]);
        }            

        $writer = new Xlsx($spreadsheet);
        $writer->save($survey->survey_name.'.xlsx');            
        
        //return view('test_report',compact('surveyQuestionList'));
    }


}
