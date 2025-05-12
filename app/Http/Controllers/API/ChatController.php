<?php

namespace App\Http\Controllers\API;

use Pusher\Pusher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\Interfaces\TicketRepositoryInterface;

class ChatController extends Controller
{
    protected $chatRepository;
    protected $ticketRepository;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->chatRepository = $chatRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function index($ticketId)
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $this->chatRepository->allForTicket($ticketId);

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = $this->chatRepository->create([
            'ticket_id' => $ticketId,
            'message' => $request->message
        ]);

        $message->load('user');

        # Broadcast the message using Pusher
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true
            ]
        );

        $pusher->trigger("ticket-channel-{$ticketId}", 'new-message', [
            'chat' => $message,
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'chat_message' => $message
        ], 201);
    }

    public function markAsRead($messageId)
    {
        $this->chatRepository->markAsRead($messageId);

        return response()->json([
            'message' => 'Message marked as read'
        ]);
    }
}
