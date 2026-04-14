<?php

namespace App\Contracts;

class NullGiftLogRepository implements GiftLogRepositoryContract
{
    public function getRoomRankingData($roomOwnerId, $type, $limit)
    {
        return collect();
    }

    public function getByAgency($rel, $start, $end, $agencyId, $keywords, $perPage, $page)
    {
        return collect();
    }

    public function userGiftInfo($id, $type, $startDate = null, $endDate = null, $perPage = null, $page = null)
    {
        return collect();
    }

    public function getSumOfReceiverObtain($userId)
    {
        return 0;
    }

    public function getByUserId($userId)
    {
        return collect();
    }

    public function topUser($withRelation, $actionId)
    {
        return collect();
    }

    public function sumGiftPriceByReceiver($receiverId, $startDate, $endDate, $date)
    {
        return 0;
    }

    public function totalUsersGiftPrice($receiverIds)
    {
        return 0;
    }

    public function getByDaily($userId, $agencyId, $start, $end)
    {
        return collect();
    }

    public function getByDate($userId, $date)
    {
        return null;
    }
}
