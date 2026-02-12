<?php

namespace Utd\Room\Services;

use Utd\Room\Repositories\RoomVipsRepository;

class RoomVipsService
{
    public function __construct(protected RoomVipsRepository $repository) {}

    public function index($perPage = 15, $page = 1)
    {
        return $this->repository->index($perPage, $page);
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function search($key)
    {
        return $this->repository->search($key);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
