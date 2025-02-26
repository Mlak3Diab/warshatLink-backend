<?php

use App\Http\Controllers\AuthController;
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
Route::post('user/register',[AuthController::class, 'userRegister']);//postman
Route::post('user/login',[AuthController::class, 'userLogin']);//postman
Route::post('user/CheckCodeemailverification', [AuthController::class,'userCheckCodeemailverification']);//postman
