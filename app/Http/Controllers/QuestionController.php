<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Question;

class QuestionController extends Controller
{

    public function changeStatus(Request $request,int $questionId){
        if (! $request->hasValidSignature()) {
            return json_encode(array('status'=>'403','msg'=>'Unauthorized'));
        }

        $question=Question::find($questionId);
        if($question->is_active==1){
            $question->is_active=0;
            $question->save();
            return json_encode(array('status'=>'200','msg'=>'Question Inactivated'));
        }
        if($question->is_active==0){
            $question->is_active=1;
            $question->save();
            return json_encode(array('status'=>'403','msg'=>'Question Activated'));
        }

        
    }

    public function deleteQuestion(Request $request,int $questionId){
        if (! $request->hasValidSignature()) {
            return 401;
        }
        DB::transaction(function () use($questionId){
            $question=Question::find($questionId);
            $question->delete();
            DB::table('question_option')->where('question_id',$questionId)->delete();
        });

        $request->session()->flash('delete_question', 'Questin deleted');

        return redirect('questions');

    }
    public function index(Request $request){
        $questions=Question::all();
        return view('questions',compact('questions'));
    }


    public function addQuestion(Request $request){
        if($request->isMethod('post')){
            // echo '<pre>';
            // print_r($request->all());
            // return;

            DB::transaction(function () use($request){
                $question=new Question;
                $question->question_name=$request->input('question_name');
                $question->option_type=$request->input('opt_type');
                $question->is_active=$request->input('sts_opt');
                $question->save();


                if($question->option_type==0){
                    DB::table('question_option')->insert([
                        'option_title' =>$request->input('text_area_option'),
                        'option_value' =>0,
                        'question_id' => $question->id,
                    ]);
                }

                if($question->option_type==1){
                    for($i=0;$i<count($request->input('multi_opt'));$i++){
                        DB::table('question_option')->insert([
                            'option_title' =>$request->input('opt_label')[$request->input('multi_opt')[$i]],
                            'option_value' =>$request->input('multi_opt')[$i],
                            'question_id' => $question->id,
                        ]);
                    }
                }
            });
        }
        return view('add_question');
    }



    public function questionDetails(Request $request,$questionId){
        if (! $request->hasValidSignature()) {
            return 401;
        }
        $questionDetails=DB::table('question')
                         ->join('question_option','question_option.question_id','=','question.id')
                         ->select('question.*','question_option.option_title','question_option.option_value')
                         ->where('question.id',$questionId)
                         ->get();

         return view('question_details',compact('questionDetails'));                


    }


    public function questionEdit(Request $request,int $questionId){
        if (!$request->hasValidSignature()) {
            return 401;
        }

        return view('add_question');
    }    
}
