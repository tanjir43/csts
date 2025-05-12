<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface TicketRepositoryInterface
{
    public function all(array $filters, int $perPage = 15, string $order = 'desc'): LengthAwarePaginator;

    public function allForUser(array $filters, int $perPage = 15, string $order = 'desc', $userId): LengthAwarePaginator;

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function updateStatus($id, $status);

    public function addAttachment($id, $file);
}
