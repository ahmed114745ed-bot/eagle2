<?php

namespace App\Tik\Repositories;

use App\Models\Agency;


class AgencyRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Agency());
    }


    public function findAgencyByOwnerId($ownerId, $status = null)
    {
        $data = $this->model->where('app_owner_id', $ownerId);
        if ($status) $data->where('status', $status);
        return  $data->first();
    }

    public function findById($id)
    {
        return $this->model->with('additionalInfo')->where('id', $id)->first();
    }

    public function members($agency)
    {
        return $agency->mempers()->where('id', '!=', $agency->app_owner_id)->orderBy('monthly_diamond_received', 'desc')->paginate(20);
    }

    public function userMembers($agency, $type = null, $userIds = null)
    {
            $members = $agency?->mempers?->pluck("id")->toArray();
        return  $members;
    }

    public function getWithSelectMonthAndYear($agencyId)
    {
        return $this->model->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, created_at')->where('id', $agencyId)->first();
    }

    public function updateAgency($agency)
    {
        $agency->save();
        return true;
    }

    public function updateStatus($agency, $status)
    {
        $agency->status = $status;
        $this->updateAgency($agency);
        return true;
    }

    public function getByAdditionalInfo()
    {
        return $this->model->where('status', 0)->whereHas('additionalInfo', function ($query) {
            $query->where('status', 0);
        })->with('additionalInfo')->get();
    }

    public function getAgencyByFilter($keyword)
    {
        return $this->model->query()
            ->with('owner')
            ->where(function ($q) use ($keyword) {
                $q->where('id', 'like', '%' . $keyword . '%')
                    ->orWhereHas('owner', function ($query) use ($keyword) {
                        $query->where('uuid', 'like', '%' . $keyword . '%');
                    });
            })->take(10)->get();
    }
}
