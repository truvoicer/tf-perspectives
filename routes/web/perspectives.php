<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpathyController;
use App\Http\Controllers\PerspectiveController;
use Illuminate\Http\Request;

Route::get('/perspectives', [PerspectiveController::class, 'index']);
Route::get('/perspectives/{perspective}', [PerspectiveController::class, 'show']);

Route::get('/me', fn(Request $request) => $request->user()->only('id', 'name'));
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/perspectives', [PerspectiveController::class, 'store']);
Route::post('/perspectives/{perspective}/empathize', [EmpathyController::class, 'toggle']);
