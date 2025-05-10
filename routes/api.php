<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

# Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {
    # Auth
    Route::post('/logout', [AuthController::class, 'logout']);
});

