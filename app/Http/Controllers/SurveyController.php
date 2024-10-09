<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{

    /**
     * @throws ValidationException
     */
    public function createSurvey(Request $request): array{
        try {
            $this->validate($request, [
                'title' => 'required|min:20',
                'owner_id' => 'required|integer',
                'description' => 'required|min:20',
                'questions' => 'required'
            ]);

            $survey = Survey::create([
                'title' =>  $request->input('title'),
                'owner_id' =>  $request->input('owner_id'),
                'description' =>  $request->input('description'),
            ]);

            $this->createSurveyQuestions($survey->id, $request->input('questions'));

            return ['code' => 201, 'msg' => 'survey created successfully',
                'data' => ['survey_id' => $survey->id]];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occured while creating the survey', 'error' => $error->getMessage()];
        }
    }


    private function createSurveyQuestions($survey_id, $questions){
        foreach ($questions as $question){
            Question::create([
                'question_text' => $question['question_text'],
                'survey_id' => $survey_id,
                'answer_type' => $question['answer_type'],
                'options' => $question['answer_type'] === 'text' || !isset($question['options']) ? null : $question['options']
            ]);
        }
    }

    public function deleteSurvey($survey_id): array {
        try {
            $survey = Survey::findOrFail($survey_id);
            $survey->delete();
            return ['code' => 200, 'msg' => 'survey deleted successfully'];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while deleting the survey', 'error' => $error->getMessage()];
        }
    }


    public function fetchSurvey($survey_id): array
    {
        try {
            $survey = Survey::with('questions')->find($survey_id);
            return ['code' => 200, $survey];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while fetching the survey', 'error' => $error->getMessage()];
        }
    }

    public function fetchSurveyResponses(Request $request, $survey_id): array
    {
        try {
            $page = $request->input('page', 1);
            $per_page = $request->input('per_page', 50);

            $survey = Survey::with('questions.responses')->find($survey_id);
            if (!$survey){
                return ['code' => 404, 'msg' => 'survey not found'];
            }

            $user_responses = $this->structureUserResponses($survey);

            // put user responses into a collection & paginate
            $user_responses_collection = collect(array_values($user_responses));
            $paginated_responses = $user_responses_collection->forPage($page, $per_page);

            return [
                'title' => $survey->title,
                'description' => $survey->description,
                'owner_id' => $survey->owner_id,
                'questions' => $survey['questions']->pluck('question_text'),
                'user_responses' => $paginated_responses,
                'current_page' => $page,
                'total_responses' => $user_responses_collection->count(),
                'last_page' => ceil($user_responses_collection->count() / $per_page)
            ];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while fetching the survey', 'error' => $error->getMessage()];
        }
    }


    private function structureUserResponses($survey): array
    {
        $responses = [];

        // loop through question and answers to create response structure
        foreach($survey->questions as $question) {
            foreach ($question->responses as $response) {
                $user_id = $response->user_id;

                if (!isset($responses[$user_id])) {
                    $responses[$user_id] = [
                        'user_id' => $user_id,
                        'responses' => []
                    ];
                }

                $responses[$user_id]['responses'][] = [
                    'question' => $question->question_text,
                    'answer' => $response->answer
                ];
            }
        }

        return $responses;
    }

}
