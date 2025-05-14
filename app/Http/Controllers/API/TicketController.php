<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TicketRequest;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\TicketRepositoryInterface;

class TicketController extends Controller
{
    protected $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function index(Request $request)
    {
        $filters    = $request->only('status', 'title');
        $perPage    = $request->get('per_page', 15);
        $order      = $request->get('order', 'desc');

        if (Auth::user()->hasRole('admin')) {
            $tickets = $this->ticketRepository->all($filters, $perPage, $order);
        } else {
            $tickets = $this->ticketRepository->allForUser($filters, $perPage, $order, Auth::id());
        }

        return response()->json(['tickets' => $tickets]);
    }

    public function show($id)
    {
        $ticket = $this->ticketRepository->find($id);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'ticket' => $ticket,
            'attachment_url' => $ticket->getFirstMediaUrl('attachments')
        ]);
    }

    public function store(TicketRequest $request)
    {
        $ticket = $this->ticketRepository->create($request->validated());

        if ($request->hasFile('attachment')) {
            $this->ticketRepository->addAttachment($ticket->id, $request->file('attachment'));
        }

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
            'attachment' => $ticket->getFirstMediaUrl('attachments'),
        ], 201);
    }

    public function update(TicketRequest $request, $id)
    {
        $ticket = $this->ticketRepository->find($id);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $updatedTicket = $this->ticketRepository->update($id, $request->validated());

        if ($request->hasFile('attachment')) {
            $this->ticketRepository->addAttachment($ticket->id, $request->file('attachment'));
        }

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $updatedTicket,
            'attachment' => $ticket->getFirstMediaUrl('attachments'),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved,closed'
        ]);

        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket = $this->ticketRepository->updateStatus($id, $request->status);

        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ]);
    }

    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|string|in:low,medium,high'
        ]);

        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket = $this->ticketRepository->updatePriority($id, $request->priority);

        return response()->json([
            'message' => 'Ticket priority updated successfully',
            'ticket' => $ticket
        ]);
    }

    public function destroy($id)
    {
        $ticket = $this->ticketRepository->find($id);

        if (!Auth::user()->hasRole('admin') && $ticket->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->ticketRepository->delete($id);

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }
}
