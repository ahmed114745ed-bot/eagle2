<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\Room;

interface RoomRepoInterface
{
    public function all($req);
    public function find($id);
    public function findByType($id, $type);
    public function create($data);
    public function update($req, $id);
    public function save($model);
    public function delete($id);
    public function getAllOpening();
    public function getAllOpeningIds();
}
