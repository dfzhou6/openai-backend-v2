<?php

use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\WechatController;
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

Route::post('login', [LoginController::class, 'Index']);
Route::get('login/check', [LoginController::class, 'Check']);
Route::get('logout', [LoginController::class, 'Logout']);

Route::get('chat', [IndexController::class, 'Index']);
Route::post('hello', [IndexController::class, 'UpdateHelloMsg']);
Route::get('hello', [IndexController::class, 'GetHelloMsg']);

Route::post('wechat', [WechatController::class, 'Index']);
