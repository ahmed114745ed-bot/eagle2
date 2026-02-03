<?php

namespace Utd\Room\Repositories;

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
    
    // DB Query Helper Methods (Issue #10)
    public function getValueByUid($uid, $column);
    public function getColumnsByUid($uid, $columns);
    public function updateByUid($uid, $data);
    public function getMicrophoneStatusByUid($uid);
    public function getMicInfoByUid($uid);
    public function getRoomUserInfoByUid($uid);
    public function getRoomBlackByUid($uid);
}
