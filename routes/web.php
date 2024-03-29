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
    $router->post('register', ['uses' => 'AuthController@register']);
    $router->post('login', ['uses' => 'AuthController@login']);
    $router->post('forgot-password', ['uses' => 'AuthController@forgot']);
});

$router->group(['prefix' => 'v1/user'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('me', ['uses' => 'UserController@detail']);
        $router->get('jobs', ['uses' => 'EventController@getJobs']);
        $router->get('balance', ['uses' => 'UserController@getBalance']);
        $router->put('', ['uses' => 'UserController@update']);
        $router->post('picture/upload', ['uses' => 'UserController@profilepict']);
        $router->post('portfolio/upload', ['uses' => 'UserController@portfolio']);
    });
});

$router->group(['prefix' => 'v1/event'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('', ['uses' => 'EventController@get']);
        $router->get('{event_id}', ['uses' => 'EventController@detail']);
        $router->delete('{event_id}', ['uses' => 'EventController@delete']);
        $router->post('', ['uses' => 'EventController@create']);
        $router->post('photos', ['uses' => 'EventController@upload']);
        $router->post('candidate/apply', ['uses' => 'EventController@apply']);
        $router->post('candidate/accept', ['uses' => 'EventController@accept']);
        $router->get('candidate/{user_id}', ['uses' => 'UserController@userdetail']);
        $router->post('finish', ['uses' => 'EventController@finish']);
    });
});

$router->group(['prefix' => 'v1/maps'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('autocomplete', ['uses' => 'MapsApiController@autocomplete']);
        $router->get('detail/{place_id}', ['uses' => 'MapsApiController@detail']);
    });
});

$router->group(['prefix' => 'v1/transaction'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('', ['uses' => 'TransactionController@all']);
        $router->get('detail/{transaction_id}', ['uses' => 'TransactionController@detail']);
        $router->post('checkout', ['uses' => 'TransactionController@create']);
    });
});

$router->group(['prefix' => 'v1/transaction'], function () use ($router) {
    $router->post('callback', ['uses' => 'TransactionController@callback']); 
    $router->get('success', ['uses' => 'TransactionController@success']); 
    $router->get('unfinish', ['uses' => 'TransactionController@unfinish']); 
    $router->get('error', ['uses' => 'TransactionController@error']); 
});

$router->group(['prefix' => 'v1/group'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('', ['uses' => 'GroupController@me']);
        $router->post('', ['uses' => 'GroupController@create']);
        $router->post('join', ['uses' => 'GroupController@joinbycode']);
        $router->get('{group_id}', ['uses' => 'GroupController@groupdetail']);
    });
});

$router->group(['prefix' => 'v1/chat'], function () use ($router) {
    $router->group(['middleware' => 'jwt.auth'], function () use ($router) {
        $router->get('rooms', ['uses' => 'PersonalChatController@rooms']);
        $router->post('room', ['uses' => 'PersonalChatController@room']);
    });
});