<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api/v1'], function () use ($router){
    // user routes
    $router->group(['prefix' => 'user'], function () use ($router){
        $router->post('/register', 'AuthController@register');
        $router->post('/login', 'AuthController@login');

    });

    // survey routes
    $router->group(['prefix' => 'survey'], function () use ($router){
        $router->post('/', 'SurveyController@createSurvey');
        $router->get('/{survey_id}', 'SurveyController@fetchSurvey');
        $router->delete('/{survey_id}', 'SurveyController@deleteSurvey');
        $router->post('/{survey_id}/questions', 'SurveyController@addQuestion');
        $router->post('/{survey_id}/response', 'SurveyController@fetchSurveyResponses');


        //response routes
        $router->group(['prefix' => 'response'], function () use ($router){
           $router->post('/', 'ResponseController@submitResponse');
        });
    });
});
