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
    Route::get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    # Ticket
    Route::apiResource('tickets', TicketController::class);
    Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus']);
    Route::patch('/tickets/{id}/priority', [TicketController::class, 'updatePriority']);

    # Broadcasting Authentication
    Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
        return \Illuminate\Support\Facades\Broadcast::auth($request);
    });

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
