<?php

namespace App\Repositories;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\ChatRepositoryInterface;

class ChatRepository implements ChatRepositoryInterface
{
    protected $model;

    public function __construct(Chat $chat)
    {
        $this->model = $chat;
    }

    public function allForTicket($ticketId)
    {
        return $this->model->where('ticket_id', $ticketId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) {
                $chatArray = $chat->toArray();
                $chatArray['body'] = $chat->message;
                return $chatArray;
            });
    }

    public function create(array $data)
    {
        return $this->model->create([
            'user_id' => Auth::id(),
            'ticket_id' => $data['ticket_id'],
            'message' => $data['message'],
            'is_read' => false
        ]);
    }

    public function markAsRead($id)
    {
        $chat = $this->model->findOrFail($id);
        $chat->update(['is_read' => true]);
        return $chat;
    }
}
