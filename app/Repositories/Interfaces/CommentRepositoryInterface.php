<?php

namespace App\Repositories\Interfaces;

interface CommentRepositoryInterface
{
    public function all($ticketId);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
}
