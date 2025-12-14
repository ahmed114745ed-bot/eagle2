<?php

namespace Modules\UsersWallet\Repositories\Eloquent;

use App\Tik\Repositories\AbstractRepository;
use Carbon\Carbon;


use App\Models\AllUserLog;
use Modules\UsersWallet\Entities\WalletLog;


class UserLogRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new AllUserLog());
    }


    public function index($userId, $type = null, $startDate = null, $endDate = null, $page, $perPage)
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end   = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
 
        if ($type === 'profits') {
            return WalletLog::where('user_id', $userId)
                ->where('operation', 'add')
                ->when($start !== null && $end !== null, function ($q) use ($start, $end) {
                   $q->whereBetween('created_at', [$start, $end]);
                 })
                ->orderBy('id', 'DESC')
                ->paginate($perPage, ['*'], 'page', $page);
        }

        return $this->model
            ->where('user_id', $userId)
            ->when($type == 'coin', function ($q) {
                $q->where('feature_type', 'coin');
            })
            ->when($type == 'diamonds', function ($q) {
                $q->where('feature_type', 'diamond');
            })
            ->when($start !== null && $end !== null, function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
