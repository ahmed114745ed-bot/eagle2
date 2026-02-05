<?php

namespace App\Contracts;

interface PkRepositoryContract
{
    public function getActiveByRoom($roomId);

    public function getByRoom($roomId);

    public function endPk($pkId);

    public function getPk($roomId);

    public function findById($id);

    public function roomPks($userId, $perPage, $page);

    public function create(array $data);

    public function update(array $data, $id);
}
