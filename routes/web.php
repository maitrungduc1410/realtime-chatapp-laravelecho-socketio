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

Route::middleware('auth')->group(function () {
  Route::get('/', [App\Http\Controllers\AppController::class, 'index']);
  Route::get('/messages', [App\Http\Controllers\MessageController::class, 'index']);
  Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store']);
  Route::post('/reactions', [App\Http\Controllers\MessageController::class, 'react']);
  Route::post('/start_chat', [App\Http\Controllers\MessageController::class, 'startChat']);
  
  // Catch-all route (for Vue Router in history mode)
  Route::get('/{any}', [App\Http\Controllers\AppController::class, 'index'])->where('any', '.*');
});