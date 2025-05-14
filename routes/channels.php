<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat.{ticketId}', function ($user, $ticketId) {
    # Check if user is owner of the ticket or an admin
    $ticket = \App\Models\Ticket::find($ticketId);

    if (!$ticket) {
        return false;
    }

    # Allow if user is the ticket owner or has admin role
    return $user->hasRole('admin') || $ticket->user_id === $user->id;
});
