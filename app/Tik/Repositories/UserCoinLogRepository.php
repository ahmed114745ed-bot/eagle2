<?php

namespace App\Tik\Repositories;

use Carbon\Carbon;
use App\Models\TimeLog;
use App\Models\UserCoinLog;

class UserCoinLogRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new UserCoinLog());
    }


    public function index($userId, $type = null, $startDate = null, $endDate = null, $page, $perPage)
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        return $this->model
            ->where('user_id', $userId)
            ->when($type == 'coin', function ($q) {
                $q->where(function ($q) {
                    $q->whereNotIn('type', ['exchange'])
                        ->orWhere(function ($q) {
                            $q->where('sub_type', 'gift_logs')
                                ->where('amount', '>', 0);
                        });
                });
            })
            ->when($type == 'diamonds', function ($q) {
                $q->where(function ($q) {
                    $q->whereIn('type', ['exchange', 'gift_logs']);
                });
            })
            ->when($start !== null && $end !== null, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
