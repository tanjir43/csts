<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\TicketRepositoryInterface;

class TicketRepository implements TicketRepositoryInterface
{
    protected $model;

    public function __construct(Ticket $ticket)
    {
        $this->model = $ticket;
    }

    public function all(array $filters, int $perPage = 15, string $order = 'desc'): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['subject'])) {
            $query->where('subject', 'like', '%' . $filters['subject'] . '%');
        }

        $query->orderBy('created_at', $order);

        $tickets = $query->paginate($perPage);

        $tickets->getCollection()->transform(function ($ticket) {
            $media = $ticket->getFirstMedia('attachments');

            $ticket->attachment = $media ? [
                'url' => $media->getUrl(),
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ] : null;

            return $ticket;
        });

        return $tickets;
    }

    public function allForUser(array $filters, int $perPage = 15, string $order = 'desc', $userId): LengthAwarePaginator
    {
        $query = $this->model->with('user')->where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['subject'])) {
            $query->where('subject', 'like', '%' . $filters['subject'] . '%');
        }

        $query->orderBy('created_at', $order);

        $tickets = $query->paginate($perPage);

        $tickets->getCollection()->transform(function ($ticket) {
            $media = $ticket->getFirstMedia('attachments');

            $ticket->attachment = $media ? [
                'url' => $media->getUrl(),
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
            ] : null;

            return $ticket;
        });

        return $tickets;
    }


    public function find($id)
    {
        return $this->model->with(['comments.user', 'chats'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create([
            'user_id' => Auth::id(),
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open'
        ]);
    }

    public function update($id, array $data)
    {
        $ticket = $this->model->findOrFail($id);
        $ticket->update($data);
        return $ticket;
    }

    public function delete($id)
    {
        $ticket = $this->model->findOrFail($id);
        return $ticket->delete();
    }

    public function updateStatus($id, $status)
    {
        $ticket = $this->model->findOrFail($id);
        $ticket->status = $status;
        $ticket->save();
        return $ticket;
    }

    public function addAttachment($id, $file)
    {
        $ticket = $this->model->findOrFail($id);
        return $ticket->addMedia($file)->toMediaCollection('attachments');
    }
}
