<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h3 style='text-align:center'>{{$surveyQuestionList[0]->survey_name}}</h3>
    <table cellspacing='0' border='1' width=100%>
        <thead>
            <tr>
                <th>Question name</th>
                <th>Option list</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($surveyQuestionList as $item)
               @if ($item->option_type)
                @php
                    $options=getQuestionOptions($item->question_id);
                    $counter=true;
                @endphp
                @foreach ($options as $opt)
                   @if ($counter)
                       <tr>
                            <td rowspan='{{count(getQuestionOptions($item->question_id))}}'>{{$item->question_name}}</td>
                            <td>{{$opt->option_title}}</td>
                            <td>{{getVotes($item->survey_id,$item->question_id,$opt->id)}}</td>
                        </tr>
                        @php
                            $counter=false;
                        @endphp
                   @else
                       <tr>
                            {{-- <td rowspan='{{count(getQuestionOptions($item->question_id))}}'>{{$item->question_name}}</td> --}}
                            <td>{{$opt->option_title}}</td>
                            <td>{{getVotes($item->survey_id,$item->question_id,$opt->id)}}</td>
                        </tr>
                   @endif
                    
                @endforeach
                
                
               @else
                 @php
                    $texts=getTexts($item->survey_id,$item->question_id);
                    $counter=true;
                 @endphp
                @if (count($texts)>0)
                    @foreach ($texts as $text)
                        @if ($counter)
                            <tr>
                                <td rowspan='{{count($texts)}}'>{{$item->question_name}}</td>
                                <td colspan='2'>{{$text->option_value}}</td>
                                
                            </tr>
                            @php
                                $counter=false;
                            @endphp
                        @else
                            <tr>
                                <td colspan='2'>{{$text->option_value}}</td>
                                
                            </tr>
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td>{{$item->question_name}}</td>
                        <td colspan='2'>NO ANSWER</td>
                        
                    </tr>
                @endif
                
               @endif
                
            @endforeach
        </tbody>
    </table>

    <script>
    window.print();
</script>
</body>
</html>