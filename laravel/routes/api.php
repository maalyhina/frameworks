<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\TicketOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('genres', GenreController::class);
Route::apiResource('halls', HallController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('movies', MovieController::class);
Route::apiResource('screenings', ScreeningController::class);
Route::apiResource('orders', TicketOrderController::class)->parameters(['orders' => 'ticket_order']);