<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'WebsiteController@index');
    Route::get('/websites/create', 'WebsiteController@create');
    Route::post('/websites/store', 'WebsiteController@store');
    Route::get('/websites/{id}', 'WebsiteController@show');
    Route::get('/websites/{id}/edit', 'WebsiteController@edit');
    Route::post('/websites/{id}/update', 'WebsiteController@update');
    Route::get('/websites/{id}/destroy', 'WebsiteController@destroy');
    Route::get('/websites/{id}/sync', 'WebsiteController@sync');
    Route::get('/websites/{id}/audit', 'WebsiteController@audit');
    Route::get('/websites/{id}/scores', 'WebsiteScoresController@index');
    Route::get('/websites/{id}/logs', 'WebsiteInterceptorLogsController@index');
    Route::get('/websites/{id}/logs/destroy-all', 'WebsiteInterceptorLogsController@destroyAll');

    Route::get('/log/{id}', 'InterceptorLogController@show');
    Route::get('/log/{id}/destroy', 'InterceptorLogController@destroy');
});
