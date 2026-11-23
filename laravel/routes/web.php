<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\MovieController;
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/test', [TestController::class, 'test']);
Route::get('/movies', [MovieController::class, 'getMovies']);
Route::get('/movies/{id}', [MovieController::class, 'getMovieItem'])->whereNumber('id');
Route::post('/movies', [MovieController::class, 'createMovie']);
Route::put('/movies/{id}', [MovieController::class, 'updateMovie'])->whereNumber('id');
Route::delete('/movies/{id}', [MovieController::class, 'deleteMovie'])->whereNumber('id');
