<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\WareRepository;
use App\Http\Resources\MomentGiftResource;
use App\Tik\Repositories\GiftLogRepository;
use App\Tik\Repositories\UserLogRepository;
use Modules\Moment\Entities\MomentUserGift;
use App\Http\Resources\AudioGiftsListResource;



class WalletStatisticService
{
    public function __construct(
        private readonly GiftLogRepository $giftLogRepository,
        private readonly UserLogRepository $userCoinLogRepository
    ) {}


    public function diamondsStatistic($userId, $type, $startDate, $endDate, $perPage, $page)
    {
        $list = collect(); // default empty collection
        $resourceClass = AudioGiftsListResource::class; // default resource

        switch ($type) {
            case 1:
                $list = $this->giftLogRepository->listGiftReceiveLive($userId, $startDate, $endDate, $perPage, $page);
                break;

            case 2:
                $list = $this->giftLogRepository->listGiftReceiveAudio($userId, $startDate, $endDate, $perPage, $page);
                break;

            case 3:
                $list = MomentUserGift::selectRaw('user_id, moment_id, gift_id, SUM(num) as total')
                    ->whereHas('moment', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->groupBy('user_id', 'moment_id', 'gift_id')
                    ->with(['user', 'gift'])
                    ->paginate($perPage, ['*'], 'page', $page);

                $resourceClass = MomentGiftResource::class;
                break;
        }

        $resource = $resourceClass::collection($list);

        return [
            'total_diamonds' => $list->sum('total'),
            'list' => $resource,
        ];
    }

    public function history($userId, $type,$startDate, $endDate, $page, $perPage)
    {
        return $this->userCoinLogRepository->index($userId, $type, $startDate, $endDate,$page, $perPage);
    }
}
