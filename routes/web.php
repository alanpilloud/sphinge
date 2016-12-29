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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/websites', 'WebsiteController@index');
Route::get('/websites/create', 'WebsiteController@create');
Route::post('/websites/store', 'WebsiteController@store');
Route::get('/websites/{id}', 'WebsiteController@show');
Route::get('/websites/{id}/edit', 'WebsiteController@edit');
Route::post('/websites/{id}/update', 'WebsiteController@update');
Route::get('/websites/{id}/destroy', 'WebsiteController@destroy');
Route::get('/websites/{id}/sync', 'WebsiteController@sync');
