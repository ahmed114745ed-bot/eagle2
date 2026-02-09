<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\AgencyHostInvite;

class AgencyHostInviteRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new AgencyHostInvite());
    }

    public function check($agencyId,$userId)
    {
        return $this->model->where([ 'agency_id' => $agencyId,'user_id'  => $userId])->latest('id')->first();
    }

    public function getByAgencyId($agencyId)
    {
        return $this->model->where("agency_id",$agencyId)->get();
    }

    public function updateStatus($agencyHostInvite,$status)
    {
        $agencyHostInvite->update(['status' => $status]);
        return true;
    }

    public function getPendingByUserId($userId)
    {
        return $this->model->where('user_id', $userId)
            ->where('status', 0)
            ->get();
    }
}