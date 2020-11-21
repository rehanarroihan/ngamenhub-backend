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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'v1/auth'], function () use ($router) {
    $router->post('register', ['uses' => 'UserController@register']);
    $router->post('login', ['uses' => 'UserController@login']);
    $router->post('forgot-password', ['uses' => 'UserController@forgot']);
});

$router->group(['prefix' => 'v1/event'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('', ['uses' => 'EventController@get']);
        $router->get('{event_id}', ['uses' => 'EventController@detail']);
        $router->post('', ['uses' => 'EventController@create']);
        $router->post('photos', ['uses' => 'EventController@upload']);
        /* $router->get('/{event_id}}', ['uses' => 'ClassesController@enroll']);
        $router->post('/candidate/apply', ['uses' => 'ClassesController@exit']);
        $router->get('/candidates', ['uses' => 'ClassesController@delete']);
        $router->get('/candidate/accept', ['uses' => 'ClassesController@delete']); */
    });
});