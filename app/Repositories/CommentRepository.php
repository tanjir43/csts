<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\CommentRepositoryInterface;

class CommentRepository implements CommentRepositoryInterface
{
    protected $model;

    public function __construct(Comment $comment)
    {
        $this->model = $comment;
    }

    public function all($ticketId)
    {
        return $this->model->where('ticket_id', $ticketId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create([
            'user_id' => Auth::id(),
            'ticket_id' => $data['ticket_id'],
            'content' => $data['content']
        ]);
    }

    public function update($id, array $data)
    {
        $comment = $this->model->findOrFail($id);
        $comment->update(['content' => $data['content']]);
        return $comment;
    }

    public function delete($id)
    {
        $comment = $this->model->findOrFail($id);
        return $comment->delete();
    }
}
