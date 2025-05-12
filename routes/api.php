<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\CommentController;

# Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {
    # Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    # Ticket
    Route::apiResource('tickets', TicketController::class);
    Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus']);

    # Comment
    Route::get('/tickets/{ticket}/comments', [CommentController::class, 'index']);
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store']);
    Route::put('/tickets/{ticket}/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/tickets/{ticket}/comments/{comment}', [CommentController::class, 'destroy']);

    # Chat
    Route::get('/tickets/{ticket}/chats', [ChatController::class, 'index']);
    Route::post('/tickets/{ticket}/chats', [ChatController::class, 'store']);
    Route::patch('/chats/{chat}/read', [ChatController::class, 'markAsRead']);
});
