<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

Route::get('/test-pusher', function () {
    event(new \App\Events\ChatMessageSent(\App\Models\Chat::first()));
    return 'Event broadcasted!';
});

