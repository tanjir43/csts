<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Events\ChatMessageSent;
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

        return response()->json(['data' => $messages]); // Changed structure
    }

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'body' => 'required|string'
        ]);

        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message = $this->chatRepository->create([
            'ticket_id' => $ticketId,
            'message' => $request->body
        ]);

        // Trigger the broadcast event
        event(new ChatMessageSent($message));

        // Add 'body' field for frontend consistency
        $messageData = $message->toArray();
        $messageData['body'] = $message->message;

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $messageData
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
