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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/register",'App\Http\Controllers\Strix@register'); 
Route::post("/login",'App\Http\Controllers\Strix@login'); 
Route::get("/getcount", 'App\Http\Controllers\Strix@getcount');
Route::group(['middleware' => 'auth:sanctum'], function(){
    Route::post("/addCategory", 'App\Http\Controllers\Strix@addCategory');
    Route::post("/addQuestion", 'App\Http\Controllers\Strix@addQuestion');
    Route::post("/addAnswer", 'App\Http\Controllers\Strix@addAnswer');
    Route::get("/all", 'App\Http\Controllers\Strix@all');
    Route::get("/answeredBy", 'App\Http\Controllers\Strix@answeredBy');
    Route::get("/askedBy", 'App\Http\Controllers\Strix@askedBy');
    Route::get("/byCategorie", 'App\Http\Controllers\Strix@byCategorie');
    Route::get("/QADetails", 'App\Http\Controllers\Strix@QADetails');
    
});