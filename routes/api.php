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

Route::post('/login', [App\Http\Controllers\Auth\LoginApiController::class, 'login'])->name('login');

Route::post('/transaction/transfer', [App\Http\Controllers\TransactionController::class, 'transfer'])->middleware('token')->name('transfer');

Route::post('/transaction/deposit', [App\Http\Controllers\TransactionController::class, 'deposit'])->middleware('token')->name('deposit');
