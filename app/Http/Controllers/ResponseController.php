<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResponseController extends Controller
{

    public function submitResponse(Request $request): array {
        try {
            $user_id = $request->input('user_id');
            $survey_id = Question::find($request['answers'][0]['question_id'])->survey_id;

            // check whether the use has already made a submission for the survey
            $existingResponse = Response::where('user_id', $user_id)
                ->whereHas('question', function ($query) use ($survey_id){
                    $query->where('survey_id', $survey_id);
                })->first();

            if($existingResponse){
                return ['code' => 400, 'msg' => 'user response already recorded'];
            }

            foreach ($request['answers'] as $response){
                Response::create([
                    'user_id' => $request->input('user_id'),
                    'question_id' => $response['question_id'],
                    'answer' => $response['answer'],
                ]);
            }

            return ['code' => 201, 'msq' => 'response saved successfully'];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while saving the response', 'error' => $error->getMessage()];
        }

    }
}
