<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});

Route::get('/getcode', 'wechatApiController@getcode');

Route::get('/users', 'wechatApiController@indexUserInfo');
Route::post('/users', 'wechatApiController@updateUserInfo');
Route::delete('/users/{usrId}', 'wechatApiController@deleteUserInfo');

Route::get('/orchid/{name}','orchidController@getOrchid');

Route::get('/photo/name/{name}','PhotoController@queryByName');
Route::resource('/photo', 'PhotoController');
