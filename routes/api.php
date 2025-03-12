<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
Route::post('user/register',[AuthController::class, 'userRegister']);//postman
Route::post('/verify-code', [AuthController::class, 'verifyCode']);//postman
Route::post('user/login',[AuthController::class, 'userLogin']);//postman
Route::post('/send-reset-code', [ResetPasswordController::class, 'sendResetCode']);//postman
Route::post('/verify-reset-code', [ResetPasswordController::class, 'verifyResetCode']);//postman
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->middleware('auth:api');//postman
Route::post('google-login', [AuthController::class, 'googleLogin']);

Route::get('/cities', [LocationController::class, 'getCities']);
Route::get('/towns', [LocationController::class, 'getTowns']);
