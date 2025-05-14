<?php

use Illuminate\Support\Facades\Log;
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
        return $request->user()->load('roles');
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    # Ticket
    Route::apiResource('tickets', TicketController::class);
    Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus']);
    Route::patch('/tickets/{id}/priority', [TicketController::class, 'updatePriority']);

    # Broadcasting Authentication
    Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
        Log::info('Broadcasting auth request', [
            'user' => $request->user() ? $request->user()->toArray() : null,
            'channel' => $request->input('channel_name'),
            'socket_id' => $request->input('socket_id')
        ]);

        $user = $request->user();

        if (!$user) {
            Log::warning('Broadcasting auth failed: No authenticated user');
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        \Illuminate\Support\Facades\Auth::setUser($user);

        try {
            $response = \Illuminate\Support\Facades\Broadcast::auth($request);
            Log::info('Broadcasting auth successful', ['user_id' => $user->id]);
            return $response;
        } catch (\Exception $e) {
            Log::error('Broadcasting auth exception', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Authorization failed'], 403);
        }
    })->middleware('broadcasting.auth');

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
