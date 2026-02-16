<?php

namespace Utd\Family\Contracts;

interface FamilyServiceContract
{
    public function getWithSearch($search = null);
    public function show($id);
    public function create($user, $request, $price);
    public function ranking($time);
    public function userRank();
    public function update($userId, $request, $familyId);
    public function join($user, $familyId);
    public function delete($user, $familyId);
    public function removeUserFromFamily($userId, $familyId, $authId);
    public function requestList($userId);
    public function actionRequest($request, $auth);
    public function familyUserType($request);
    public function memberList($familyId);
    public function familyRooms($familyId);
    public function exitMember($user);
    public function searchUsersInFamily($key, $page);
    public function kickFamily($userId);
    public function isFamilyOwner($userId);
}
