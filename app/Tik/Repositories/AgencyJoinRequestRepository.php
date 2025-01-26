<?php

namespace App\Tik\Repositories;

use App\Helpers\UserCommon;
use App\Models\AgencyJoinRequest;

class AgencyJoinRequestRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new AgencyJoinRequest());
    }

    public function countByMonth($userId)
    {
        $period = UserCommon::getPeriodTarget();
        $start_at =$period['start_at'];
        $end_at =$period['end_at'];
        return $this->model->query()->where('user_id', $userId)->where('status', '!=', 2)->whereBetween('created_at', [$start_at, $end_at])->count();
        // return $this->model->query()->where('user_id', $userId)->where('status', '!=', 2)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
    }

    public function countByAgency($userId, $agencyId)
    {
        return $this->model->query()->where('agency_id', $agencyId)->where('user_id', $userId)->where('status', '=', 0)->count();
    }

    public function getByUser($userId)
    {
        return $this->model->query()->where('user_id', $userId)->get();
    }

    public function getByAgencyId($agencyId)
    {
        $requests = $this->model->query()->where('agency_id', $agencyId)->where('status', 0)->whereHas('user')->with('user')->get();
        return $requests->pluck('user');
    }

    public function findRequest($userId, $agencyId)
    {
        return $this->model->where('agency_id', $agencyId)->where('status', 0)->where('user_id', $userId)->first();
    }

    public function findByUsersAndAgency($userId,$agencyId)
    {
        return $this->model->where(['user_id' => $userId, 'agency_id' => $agencyId])->first();
    }
}
