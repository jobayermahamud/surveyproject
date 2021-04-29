@include('layouts.header')


<body>
    <div class="container-scroller">
     <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card position-relative">
                <div class="card-body">
                  <div id="detailedReports" class="carousel slide detailed-report-carousel position-static pt-2" data-ride="carousel">
                    <div class="carousel-inner">
                        @php
                            $counter=0;
                        @endphp
                        @foreach ($processData as $key => $value)
                            @if ($counter<count($processData)-1)
                              @php
                                  $counter++;
                              @endphp
                                <div style='margin-top:30px' class="carousel-item">
                                    <p>{{$processData[$key][0]['question_name']}}</p>
                                    <hr>
                                    @for ($i=0;$i<count($value);$i++)
                                        @if ($value[$i]['option_type'])
                                            <p><input type='radio' data-target='option_{{$value[$i]['question_id']}}' url_ref='{{URL::signedRoute('ml_opt', 
                                            ['surveyId' => $value[$i]['id'],
                                             'questionId'=>$value[$i]['question_id'],
                                             'optionId'=>$value[$i]['option_id'],
                                             'optionValue'=>$value[$i]['option_title'],
                                            ])}}' name='option_{{$key}}'/>&nbsp;{{$value[$i]['option_title']}}</p>
                                            <hr>
                                            
                                        @else
                                            <textarea id='textoption_{{$value[$i]['question_id']}}' style='width:70%'></textarea>
                                        @endif
                                        
                                    @endfor

                                    @if ($processData[$key][0]['option_type'])
                                        <a opt_type='ml_opt' id='option_{{$processData[$key][0]['question_id']}}' href='#' class='btn btn-primary btn-sm'>VOTE</a>
                                    @else
                                        <a opt_type='text_opt' data-target='textoption_{{$processData[$key][0]['question_id']}}' href='{{URL::signedRoute('text_opt', 
                                            ['surveyId' => $processData[$key][0]['id'],
                                             'questionId'=>$processData[$key][0]['question_id'],
                                            ])}}' class='btn btn-primary btn-sm'>VOTE</a>
                                    @endif
                                    
                                </div>
                            @else
                                <div style='margin-top:30px' class="carousel-item active">
                                    <p>{{$processData[$key][0]['question_name']}}</p>
                                    <hr>
                                    @for ($i=0;$i<count($value);$i++)
                                        @if ($value[$i]['option_type'])
                                            <p><input type='radio' url_ref='{{URL::signedRoute('ml_opt', 
                                            ['surveyId' => $value[$i]['id'],
                                             'questionId'=>$value[$i]['question_id'],
                                             'optionId'=>$value[$i]['option_id'],
                                             'optionValue'=>$value[$i]['option_title'],
                                            ])}}' data-target='option_{{$value[$i]['question_id']}}' name='option_{{$key}}'/>&nbsp;{{$value[$i]['option_title']}}</p>
                                            <hr>
                                            
                                        @else
                                            <textarea id='textoption_{{$value[$i]['question_id']}}' style='width:70%'></textarea>
                                            
                                        @endif
                                        
                                    @endfor
                                    @if ($processData[$key][0]['option_type'])
                                        <a opt_type='ml_opt' id='option_{{$processData[$key][0]['question_id']}}' href='#' class='btn btn-primary btn-sm'>VOTE</a>
                                    @else
                                        <a opt_type='text_opt' data-target='textoption_{{$processData[$key][0]['question_id']}}' href='{{URL::signedRoute('text_opt', 
                                            ['surveyId' => $processData[$key][0]['id'],
                                             'questionId'=>$processData[$key][0]['question_id'],
                                            ])}}' class='btn btn-primary btn-sm'>VOTE</a>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                      
                      
                    </div>
                    <a class="carousel-control-prev" href="#detailedReports" role="button" data-slide="prev">
                      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                      <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#detailedReports" role="button" data-slide="next">
                      <span class="carousel-control-next-icon" aria-hidden="true"></span>
                      <span class="sr-only">Next</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
    </div>        



@include('layouts.footer')

<script>
    addEventListener('click',(e)=>{
        if(e.target.type=='radio'){
            if(e.target.checked){
                document.getElementById(e.target.getAttribute('data-target')).setAttribute('href',e.target.getAttribute('url_ref'));
            }
            
        }

        

        if(e.target.getAttribute('opt_type')=='ml_opt'){
            e.preventDefault();
            if(e.target.getAttribute('href')=='#'){
               alert("Select your answer first");
            }else{
                

                $.get(e.target.getAttribute('href'), function(data, status){
                    e.target.remove();
                    alert(JSON.parse(data)['msg'])
                });
                
            }
        }


        if(e.target.getAttribute('opt_type')=='text_opt'){
            e.preventDefault();
            // console.log(e.target.getAttribute('href'))
            // console.log(e.target.getAttribute('data-target'))
            // console.log(document.getElementById(e.target.getAttribute('data-target')).value);
            
            



            $.ajax({
            url: e.target.getAttribute('href'),
            type: "POST",
            
            data: {
                comment_text: document.getElementById(e.target.getAttribute('data-target')).value,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            }).done(function (data) {
               
               e.target.remove();
               alert(JSON.parse(data)['msg'])
            });
        }
    })
</script>