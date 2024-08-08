<?php

namespace App\Tik\Repositories;

use Carbon\Carbon;
use App\Models\GiftLog;


class GiftLogRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new GiftLog());
    }


    public function getFirstRoomByOwnerId($ownerId)
    {
        return $this->model->query()->selectRaw('sender_id, SUM(giftNum * giftPrice) AS total')->where('roomowner_id', $ownerId)->groupBy('sender_id')->orderByDesc('total')->first();
    }

    public function getSumOfReceiverObtain($userId)
    {
      return $this->model->query()->where('receiver_id', $userId)
      ->whereYear('created_at', '=', Carbon::now()->year)
      ->whereMonth('created_at', '=', Carbon::now()->month)
      ->whereDay('created_at', '=', Carbon::now()->day)
      ->sum('receiver_obtain');
    }
}
