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

    public function getRoomRankingData($roomOwnerId, $type, $limit)
    {
        $query = GiftLog::with(['sender.profile', 'sender.mangerType']) // Eager load relationships
            ->where('roomowner_id', $roomOwnerId);

        // Filter for today's data if type is 1
        if ($type == 1) {
            $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        }

        return $query->selectRaw("SUM(giftPrice) as exp, sender_id")
            ->groupBy('sender_id')
            ->orderByDesc('exp')
            ->limit($limit)
            ->get()
            ->reject(fn($item) => $item->exp == 0);
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
            ->where(fn($q) => $q->whereYear('created_at', '<', $year)->orWhere(fn($q) => $q->whereMonth('created_at', '<=', $month)->whereYear('created_at', '<=', $year)))
            ->where('receiver_id', $userId)
            ->where('agency_id', $agencyId)->groupBy(\DB::raw('date(created_at)'))
            ->limit(31)->get();
    }

    public function getByDailyNew($userId, $agencyId, $start_at, $end_at)
    {


        // dd($start_at , $end_at);

        $data = $this->model->query()
            ->selectRaw('sum(giftPrice) as diamonds, max(created_at) as date')
            ->whereBetween('created_at', [$start_at, $end_at]) // Applying whereBetween
            ->where('receiver_id', $userId)
            ->where('agency_id', $agencyId)->groupBy(\DB::raw('date(created_at)'))
            ->limit(31)->get();

        return $data;
    }

    public function getByDate($userId, $date)
    {
        return $this->model->query()->selectRaw('receiver_id, SUM(giftNum * giftPrice) AS total')->groupBy("receiver_id")->where('receiver_id', $userId)->whereDate("created_at", $date)->first();
    }

    public function topUser($withRelation, $actionId)
    {
        return $this->model->with($withRelation)->select(DB::raw('sum(giftPrice) as totalGiftPrice'), $actionId)->groupBy($actionId)->orderByDesc('totalGiftPrice')->whereDate('created_at', Carbon::today())->limit(3)->get();
    }

    public function getByUserId($userId)
    {
        return $this->model->query()
            ->has('sender')
            ->with('sender', 'receiver')
            ->select('sender_id', 'receiver_id')
            ->selectRaw('SUM(giftNum * giftPrice) AS total')
            ->selectRaw('CAST(SUM(giftNum * giftPrice) AS DECIMAL(10, 2)) AS total_decimal')
            ->where('receiver_id', $userId)
            ->groupBy('sender_id', 'receiver_id')
            ->orderByDesc('total')
            ->take(20)
            ->get();
    }

    public function userGiftInfo($id, $type, $startDate = null, $endDate = null, $perPage, $page)
    {
        return $this->model->with('sender', 'receiver', 'gift')
            ->selectRaw('giftId, sender_id, receiver_id, SUM(giftNum * giftPrice) AS total')
            ->when($type == 'sender', fn($q) => $q->where('sender_id', $id)->where('receiver_id', '!=', $id))
            ->when($type == 'receiver', fn($q) => $q->where('receiver_id', $id)->where('sender_id', '!=', $id))
            ->when($type == 'yourself', fn($q) => $q->where('receiver_id', $id)->where('sender_id', $id))
            ->when(is_null($type), function ($q) use ($id) {
                $q->where(function ($query) use ($id) {
                    $query->where('receiver_id', $id)
                        ->orWhere('sender_id', $id);
                });
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $formattedStartDate = Carbon::parse($startDate)->startOfDay();
                $formattedEndDate = Carbon::parse($endDate)->endOfDay();
                \Log::info("Filtering from {$formattedStartDate} to {$formattedEndDate}"); // Debugging log
                $q->whereBetween('created_at', [$formattedStartDate, $formattedEndDate]);
            })->groupBy('giftId', 'sender_id', 'receiver_id')->paginate($perPage, ['*'], 'page', $page);
    }
}
