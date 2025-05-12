<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\TicketRepositoryInterface;
use App\Repositories\Interfaces\CommentRepositoryInterface;

class CommentController extends Controller
{
    protected $commentRepository;
    protected $ticketRepository;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        TicketRepositoryInterface $ticketRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->ticketRepository = $ticketRepository;
    }

    public function index($ticketId)
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comments = $this->commentRepository->all($ticketId);

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = $this->commentRepository->create([
            'ticket_id' => $ticketId,
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment
        ], 201);
    }

    public function update(Request $request, $ticketId, $commentId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = $this->commentRepository->update($commentId, [
            'content' => $request->content
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment
        ]);
    }

    public function destroy($ticketId, $commentId)
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->commentRepository->delete($commentId);

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }
}
