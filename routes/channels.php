<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

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

// Private chat channel authorization
Broadcast::channel('chat.{ticketId}', function ($user, $ticketId) {
    // Debug logging
    Log::info('Broadcast auth attempt', [
        'user_id' => $user ? $user->id : null,
        'ticket_id' => $ticketId,
        'user_name' => $user ? $user->name : null
    ]);

    // Check if user is authenticated
    if (!$user) {
        Log::warning('Broadcast auth failed: No user authenticated');
        return false;
    }

    // Find the ticket
    $ticket = \App\Models\Ticket::find($ticketId);

    if (!$ticket) {
        Log::warning('Broadcast auth failed: Ticket not found', ['ticket_id' => $ticketId]);
        return false;
    }

    // Check if user has admin role or owns the ticket
    $hasAdminRole = $user->hasRole('admin');
    $ownsTicket = $ticket->user_id === $user->id;

    Log::info('Broadcast auth check', [
        'has_admin_role' => $hasAdminRole,
        'owns_ticket' => $ownsTicket,
        'ticket_owner_id' => $ticket->user_id,
        'user_id' => $user->id
    ]);

    // Allow if user is the ticket owner or has admin role
    $authorized = $hasAdminRole || $ownsTicket;

    Log::info('Broadcast authorization result', [
        'authorized' => $authorized,
        'user_id' => $user->id,
        'ticket_id' => $ticketId
    ]);

    return $authorized;
});
