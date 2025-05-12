<?php

namespace App\Repositories\Interfaces;

interface ChatRepositoryInterface
{
    public function allForTicket($ticketId);

    public function create(array $data);

    public function markAsRead($id);
}
