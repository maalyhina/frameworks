<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\TicketOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:client')->group(function () {
        Route::get('genres', [GenreController::class, 'index']);
        Route::get('genres/{genre}', [GenreController::class, 'show']);
        Route::get('halls', [HallController::class, 'index']);
        Route::get('halls/{hall}', [HallController::class, 'show']);
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
        Route::get('movies', [MovieController::class, 'index']);
        Route::get('movies/{movie}', [MovieController::class, 'show']);
        Route::get('screenings', [ScreeningController::class, 'index']);
        Route::get('screenings/{screening}', [ScreeningController::class, 'show']);
        Route::get('orders', [TicketOrderController::class, 'index']);
        Route::get('orders/{ticket_order}', [TicketOrderController::class, 'show']);
        Route::post('orders', [TicketOrderController::class, 'store']);
    });

    Route::middleware('role:manager')->group(function () {
        Route::post('genres', [GenreController::class, 'store']);
        Route::put('genres/{genre}', [GenreController::class, 'update']);
        Route::post('halls', [HallController::class, 'store']);
        Route::put('halls/{hall}', [HallController::class, 'update']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::put('customers/{customer}', [CustomerController::class, 'update']);
        Route::post('movies', [MovieController::class, 'store']);
        Route::put('movies/{movie}', [MovieController::class, 'update']);
        Route::post('screenings', [ScreeningController::class, 'store']);
        Route::put('screenings/{screening}', [ScreeningController::class, 'update']);
        Route::put('orders/{ticket_order}', [TicketOrderController::class, 'update']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::delete('genres/{genre}', [GenreController::class, 'destroy']);
        Route::delete('halls/{hall}', [HallController::class, 'destroy']);
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);
        Route::delete('movies/{movie}', [MovieController::class, 'destroy']);
        Route::delete('screenings/{screening}', [ScreeningController::class, 'destroy']);
        Route::delete('orders/{ticket_order}', [TicketOrderController::class, 'destroy']);
    });
});