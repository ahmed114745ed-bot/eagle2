<?php

namespace App\Services\Family;

use App\Contracts\FamilyContract;

class NullFamilyService implements FamilyContract
{
    public function getWithSearch($search = null) { return collect(); }
    public function show($id) { return null; }
    public function create($user, $request, $price) { return null; }
    public function ranking($time) { return collect(); }
    public function userRank() { return collect(); }
    public function update($userId, $request, $familyId) { return null; }
    public function join($user, $familyId) { return null; }
    public function delete($user, $familyId) { return null; }
    public function removeUserFromFamily($userId, $familyId, $authId) { return null; }
    public function requestList($userId) { return collect(); }
    public function actionRequest($request, $auth) { return null; }
    public function familyUserType($request) { return null; }
    public function memberList($familyId) { return collect(); }
    public function familyRooms($familyId) { return collect(); }
    public function exitMember($user) { return null; }
    public function searchUsersInFamily($key, $page) { return collect(); }
    public function kickFamily($userId) { return null; }
    public function isFamilyOwner($userId) { return false; }
}
