<?php

namespace App\Tik\Repositories;

use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\AgencyUserJob;

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
        return $this->model->with('additionalInfo',)->where('id', $id)->first();
    }
    public function findByStatus($id)
    {
        return $this->model->with('additionalInfo')->where('id', $id)->where('status', 1)->first();
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

    public function getByAdditionalInfoPaginate($id, $uuid, $perPage, $page, $status = null, $action = null)
    {
        if ($action == null) {
            $agencies =   $this->model->where('status', 0)->whereHas('additionalInfo', function ($query) {
                $query->where('status', 0);
            });
        } else {
            $agencies =   $this->model->where('status', '!=', 0)->whereHas('additionalInfo', function ($query) {
                $query->where('status', '!=', 0);
            });
        }
        $agencies =    $agencies->whereHas('owner', function ($query) use ($uuid) {
            $query->when(isset($id), function ($query) use ($uuid) {
                $query->where('uuid', $uuid);
            });
        })->with('additionalInfo', 'owner')->when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->when(isset($status), function ($query) use ($status) {
            $query->where('status', $status);
        })->orderByDesc("id")->paginate($perPage, ['*'], 'page', $page);
        return $agencies;
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

    public function countAgencyUserAdmin($userId)
    {
        return $this->model->where('agency_manger_id', $userId)->count();
    }

    public function getByAgencyMangerId($agencyMangerId)
    {
        return $this->model->where('agency_manger_id', $agencyMangerId)->with('owner')->get();
    }

    public function getAdminByUserId($userId)
    {
        return AgencyUserJob::where('user_id', $userId)->where('type', 'requestManger')->first();
    }

    public function getAgencyById($agencyId)
    {
        return Agency::where('id', $agencyId)->first();
    }

    public function getAgencyByOwnerId($ownerId)
    {
        return Agency::where('app_owner_id', $ownerId)->first();
    }

    public function getJoinRequests($agencyId)
    {
        return AgencyJoinRequest::where('agency_id', $agencyId);
    }
}
