<?php

namespace Utd\Gifts\Repositories;

use App\Contracts\GiftLogRepositoryContract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Utd\Gifts\Entities\GiftLog;

class GiftLogRepository extends AbstractRepository implements GiftLogRepositoryContract
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
        if ($type === 1) {
            $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        }

        return $query->selectRaw('SUM(giftPrice) as exp, sender_id')
            ->groupBy('sender_id')
            ->orderByDesc('exp')
            ->limit($limit)
            ->get()
            ->reject(fn ($item) => $item->exp === 0);
    }

    public function getByAgency($rel, $start, $end, $agencyId, $keywords, $perPage, $page)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->whereHas($rel)
            ->with($rel)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("sum(giftPrice) as exp, $keywords")
            ->groupBy($keywords)
            ->orderByRaw('exp desc')
            ->paginate($perPage, ['*'], 'page', $page);
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
        return $this->model->query()->whereBetween('created_at', [$startDate, $endDate])->where('receiver_id', $receiverId);
    }

    public function topUser($withRelation, $actionId)
    {
        return $this->model->with($withRelation)->select(DB::raw('sum(giftPrice) as totalGiftPrice'), $actionId)->groupBy($actionId)->orderByDesc('totalGiftPrice')->whereDate('created_at', Carbon::today())->limit(3)->get();
    }

    public function getByUserId($userId)
    {
        return $this->model->query()
            ->has('sender')
            ->with([
                'sender.profile',
                'sender.country',
                'receiver',
            ])
            // ->select('sender_id', 'receiver_id')
            // ->selectRaw('SUM( giftPrice) AS total')
            // ->selectRaw('CAST(SUM(giftPrice) AS DECIMAL(10, 2)) AS total_decimal')
            ->selectRaw('
            sender_id,
            receiver_id,
            SUM(giftPrice) AS total
        ')
            ->where('receiver_id', $userId)
            ->groupBy('sender_id', 'receiver_id')
            ->orderByDesc('total')
            ->take(20)
            ->get();
    }

    public function userGiftInfo($id, $type, $startDate = null, $endDate = null, $perPage = null, $page = null)
    {
        return $this->model->with('sender', 'receiver', 'gift')
            ->selectRaw('giftId, sender_id, receiver_id, SUM(giftNum * giftPrice) AS total')
            ->when($type === 'sender', fn ($q) => $q->where('sender_id', $id)->where('receiver_id', '!=', $id))
            ->when($type === 'receiver', fn ($q) => $q->where('receiver_id', $id)->where('sender_id', '!=', $id))
            ->when($type === 'yourself', fn ($q) => $q->where('receiver_id', $id)->where('sender_id', $id))
            ->when(is_null($type), function ($q) use ($id) {
                $q->where(function ($query) use ($id) {
                    $query->where('receiver_id', $id)
                        ->orWhere('sender_id', $id);
                });
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $formattedStartDate = Carbon::parse($startDate)->startOfDay();
                $formattedEndDate = Carbon::parse($endDate)->endOfDay();
                $q->whereBetween('created_at', [$formattedStartDate, $formattedEndDate]);
            })->groupBy('giftId', 'sender_id', 'receiver_id')->paginate($perPage, ['*'], 'page', $page);
    }

    public function sumGiftPriceByReceiver($receiverId, $startDate, $endDate, $date)
    {
        return $this->model
            ->where('receiver_id', $receiverId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('giftPrice');
    }

    public function totalUsersGiftPrice($receiverIds)
    {
        return $this->model
            ->whereIn('receiver_id', $receiverIds)
            ->sum('giftPrice');
    }

    public function getByDaily($userId, $agencyId, $start, $end)
    {
        return $this->model
            ->where('sender_id', $userId)
            ->where('agency_id', $agencyId)
            ->whereBetween('created_at', [$start, $end])
            ->get();
    }

    public function getByDate($userId, $date)
    {
        return $this->model
            ->where('sender_id', $userId)
            ->where('created_at', '>=', $date)
            ->get();
    }
}
