<?php

namespace App\Contracts;

interface GiftLogRepositoryContract
{
    public function getRoomRankingData($roomOwnerId, $type, $limit);
    public function getByAgency($rel, $start, $end, $agencyId, $keywords, $perPage, $page);
    public function userGiftInfo($id, $type, $startDate = null, $endDate = null, $perPage = null, $page = null);
    public function getSumOfReceiverObtain($userId);
    public function getByUserId($userId);
    public function topUser($withRelation, $actionId);
}
