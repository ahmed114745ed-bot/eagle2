<?php

namespace Utd\Room\Repositories;

use Illuminate\Database\Eloquent\Model;

interface RoomRepoInterface
{
    public function all($req);
    public function find(int $id): ?Model;
    public function findByUid($id);
    public function findByType($id, $type);
    public function create(array $data): mixed;
    public function save($model);
    public function delete(int $id): bool;
    public function getAllOpening();
    public function getAllOpeningIds();
    
    // DB Query Helper Methods (Issue #10)
    public function getValueByUid($uid, $column);
    public function getColumnsByUid($uid, $columns);
    public function updateByUid($uid, $data);
    public function getMicrophoneStatusByUid($uid);
    public function getMicInfoByUid($uid);
    public function getRoomUserInfoByUid($uid);
    public function getRoomBlackByUid($uid);
}
