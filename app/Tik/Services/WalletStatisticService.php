<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\WareRepository;
use App\Http\Resources\MomentGiftResource;
use App\Tik\Repositories\GiftLogRepository;
use Modules\Moment\Entities\MomentUserGift;
use App\Http\Resources\AudioGiftsListResource;


class WalletStatisticService
{
    public function __construct(
        private readonly GiftLogRepository $GiftLogRepository,
    ) {}


    public function diamondsStatistic($userId, $type, $startDate, $endDate, $perPage, $page)
    {
        $list = [];
        switch ($type) {

            case 1:
                $list = $this->GiftLogRepository->listGiftReceiveLive($userId, $startDate, $endDate, $perPage, $page);
                $resource = AudioGiftsListResource::collection($list);
                break;

            case 2:
                $list = $this->GiftLogRepository->listGiftReceiveAudio($userId, $startDate, $endDate, $perPage, $page);
                $resource = AudioGiftsListResource::collection($list);
                break;

            case 3:
                $list = MomentUserGift::selectRaw('user_id, moment_id,gift_id, SUM(num) as total')
                    ->where('moment_id', $userId)
                    ->whereHas('moment', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->groupBy('user_id', 'moment_id', 'gift_id')
                    ->with('user', 'gift')
                    ->get();
                $resource = MomentGiftResource::collection($list);

                break;
        }

        return [

            'total_diamonds' => $list->sum('total'),
            'list' => $resource,
        ];
    }
}
