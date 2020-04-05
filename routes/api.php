<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(["prefix" => "v1", 'middleware' => 'cors'], function(){

    Route::prefix('auth')->group(function () {
        Route::post('login', 'Api\AuthController@login');
        Route::post('register', 'Api\AuthController@register');

        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('refresh-token', 'Api\AuthController@refreshToken');
            Route::post('logout','Api\AuthController@logout');
            Route::get('get-user', 'Api\AuthController@getUser');
            Route::post('update-user', 'Api\AuthController@update');
        });
    });
    Route::group(['middleware' => 'auth:api'], function () {
        Route::resource("articles", "Api\ArticleController");
        Route::get("categories", "Api\CategoryController@index");
        Route::post("comment", "Api\CommentController@leaveComment");
    });
});


