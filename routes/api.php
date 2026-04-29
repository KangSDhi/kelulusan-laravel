<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController as Auth;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::post('/auth/login', [Auth::class, 'login']);
Route::post('/auth/login/graduation', [Auth::class, 'loginGraduation']);

Route::middleware('auth:api,graduation_api')->group(function () {
    Route::get('/auth/check/user', [Auth::class, 'checkUser']);
});
