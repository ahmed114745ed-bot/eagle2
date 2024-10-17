<?php

namespace App\Tik\Repositories;

use Carbon\Carbon;
use App\Models\GiftLog;
use Illuminate\Support\Facades\DB;


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
    public function getByReceiver($receiverId, $startDate, $endDate)
    {
        return $this->model->query()->whereBetween('created_at', [$startDate, $endDate])->where("receiver_id", $receiverId);
    }

    public function sumGiftPriceByReceiver($receiverId, $startDate, $endDate, $date)
    {
        return $this->getByReceiver($receiverId, $startDate, $endDate)->whereDate('created_at', $date)->sum("giftPrice");
    }

    public function totalUsersGiftPrice($receiverIds)
    {
        $this->model->query()->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->whereIn("receiver_id", $receiverIds)->sum('giftPrice');
    }

    public function getByDaily($userId, $agencyId, $month, $year)
    {
        return  $this->model->query()
            ->selectRaw('sum(giftPrice) as diamonds, max(created_at) as date')
            ->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->where('receiver_id', $userId)
            ->where('agency_id', $agencyId)->groupBy(\DB::raw('date(created_at)'))
            ->limit(31)->get();
    }

    public function getByDate($userId, $date)
    {
        return $this->model->query()->selectRaw('receiver_id, SUM(giftNum * giftPrice) AS total')->groupBy("receiver_id")->where('receiver_id', $userId)->whereDate("created_at", $date)->first();
    }

    public function topUser($withRelation,$actionId)
    {
        return $this->model->with($withRelation)->select(DB::raw('sum(giftPrice) as totalGiftPrice'), $actionId)->groupBy($actionId)->orderByDesc('totalGiftPrice')->whereDate('created_at', Carbon::today())->limit(3)->get();
    }
}
