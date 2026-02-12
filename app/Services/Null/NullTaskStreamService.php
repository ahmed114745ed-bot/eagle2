<?php

namespace App\Services\Null;

use App\Contracts\TaskStreamServiceContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class NullTaskStreamService implements TaskStreamServiceContract
{
    public function index(): LengthAwarePaginator
    {
        return new Paginator([], 0, 10);
    }

    public function store()
    {
        return null;
    }

    public function join(array $data)
    {
        return null;
    }

    public function leave(array $data)
    {
        return null;
    }

    public function sendInvitation(array $data)
    {
        return [];
    }

    public function respondInvitation(array $data)
    {
        return null;
    }

    public function liveFriends(): LengthAwarePaginator
    {
        return new Paginator([], 0, 10);
    }
}
