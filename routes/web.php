<?php

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


$router->post('/login','AuthController@authenticate');
$router->post('/register','AuthController@register');

$router->group(['prefix'=>'/template'], function($router){
	$router->get('/','TemplateController@index');
	$router->post('/','TemplateController@store');
	$router->get('/{id}','TemplateController@show');
	$router->patch('/{id}','TemplateController@update');
	$router->delete('/{id}','TemplateController@delete');
	$router->post('/{id}/assigns','TemplateController@assign');
});

$router->group(['prefix'=>'/checklists'], function($router){
	$router->get('/','TaskController@index');
	$router->post('/','TaskController@store');
	$router->get('/{id}','TaskController@show');
	$router->patch('/{id}','TaskController@update');
	$router->delete('/{id}','TaskController@delete');
});

