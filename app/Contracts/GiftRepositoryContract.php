<?php

namespace App\Contracts;

interface GiftRepositoryContract
{
    public function all($type = null);
    public function getByCategory($categoryId);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
}
