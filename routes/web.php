<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Auth::routes();

Route::get('/', [App\Http\Controllers\AppController::class, 'index'])->middleware('auth');

Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index'])->middleware('auth');

Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->middleware('auth');

Route::post('/reactions', [App\Http\Controllers\MessageController::class, 'react'])->middleware('auth');

Route::get('/{any}', [App\Http\Controllers\AppController::class, 'index'])->where('any', '.*')->middleware('auth'); // catch all routes or else it will return 404 with Vue router in history mode