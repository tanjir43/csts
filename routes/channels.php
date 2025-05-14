<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

# Private chat channel authorization
Broadcast::channel('chat.{ticketId}', function ($user, $ticketId) {

    if (!$user) {
        Log::warning('Broadcast auth failed: No user authenticated');
        return false;
    }

    $ticket = \App\Models\Ticket::find($ticketId);

    if (!$ticket) {
        Log::warning('Broadcast auth failed: Ticket not found', ['ticket_id' => $ticketId]);
        return false;
    }

    $hasAdminRole = $user->hasRole('admin');
    $ownsTicket = $ticket->user_id === $user->id;

    Log::info('Broadcast auth check', [
        'has_admin_role' => $hasAdminRole,
        'owns_ticket' => $ownsTicket,
        'ticket_owner_id' => $ticket->user_id,
        'user_id' => $user->id
    ]);

    $authorized = $hasAdminRole || $ownsTicket;

    Log::info('Broadcast authorization result', [
        'authorized' => $authorized,
        'user_id' => $user->id,
        'ticket_id' => $ticketId
    ]);

    return $authorized;
});
