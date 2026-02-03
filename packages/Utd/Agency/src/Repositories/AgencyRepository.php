<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\Agency;
use Utd\Agency\Entities\AgencyJoinRequest;
use Utd\Agency\Entities\AgencyUserJob;
use Utd\Agency\Entities\UsersJoinedAgency;
use Utd\Agency\Scopes\HostAgencyScope;
use Utd\Agency\Contracts\AgencyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AgencyRepository implements AgencyRepositoryInterface
{
    protected Agency $model;

    public function __construct()
    {
        $this->model = new Agency();
    }

    /**
     * Find agency by ID
     */
    public function findById($id)
    {
        return $this->model
            ->with(['additionalInfo', 'mempers', 'admins', 'userSalaries', 'owner.profile'])
            ->withCount('mempers')
            ->where('id', $id)
            ->first();
    }

    /**
     * Find agency by owner
     */
    public function findByOwner($ownerId, $status = null)
    {
        $query = $this->model->where('app_owner_id', $ownerId);
        
        if ($status !== null) {
            $query->where('status', $status);
        }
        
        return $query->first();
    }

    /**
     * Find agency by owner ID
     */
    public function findAgencyByOwnerId($ownerId, $status = null)
    {
        $query = $this->model->where('app_owner_id', $ownerId);
        
        if ($status !== null) {
            $query->where('status', $status);
        }
        
        return $query->first();
    }

    /**
     * Create agency
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update agency
     */
    public function update($id, array $data)
    {
        $agency = $this->findById($id);
        
        if ($agency) {
            $agency->update($data);
            return $agency->fresh();
        }
        
        return null;
    }

    /**
     * Delete agency
     */
    public function delete($id)
    {
        $agency = $this->findById($id);
        
        if ($agency) {
            return $agency->delete();
        }
        
        return false;
    }

    /**
     * Filter agency by ID
     */
    public function filterAgency($id)
    {
        return $this->model
            ->whereRaw('CAST(id AS CHAR) LIKE ?', [$id . '%'])
            ->with('owner', 'AgencypaymentGateways')
            ->get();
    }

    /**
     * Get old agencies for user
     */
    public function getOldAgencies($userId)
    {
        return UsersJoinedAgency::where('user_id', $userId)
            ->whereHas('agency')
            ->with(['agency:id,img'])
            ->select('join_date', 'leave_date', 'agency_id')
            ->get();
    }

    /**
     * Find agency by status
     */
    public function findByStatus($id)
    {
        return $this->model
            ->with('additionalInfo')
            ->where('id', $id)
            ->where('status', 1)
            ->first();
    }

    /**
     * Get agency members
     */
    public function members($agency)
    {
        return $agency->mempers()
            ->where('id', '!=', $agency->app_owner_id)
            ->withSum(['monthlyDiamondReceive as monthly_diamond_received_sum' => function ($q) {
                $q->where('month', now()->month)
                    ->where('year', now()->year);
            }], 'monthly_diamond_received')
            ->orderByDesc('monthly_diamond_received_sum')
            ->whereDoesntHave('agencyAdmins')
            ->paginate(20);
    }

    /**
     * Get user member IDs
     */
    public function userMembers($agency, $type = null, $userIds = null)
    {
        return $agency?->mempers?->pluck("id")->toArray() ?? [];
    }

    /**
     * Get with select month and year
     */
    public function getWithSelectMonthAndYear($agencyId)
    {
        return $this->model
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, created_at')
            ->where('id', $agencyId)
            ->first();
    }

    /**
     * Update agency
     */
    public function updateAgency($agency)
    {
        $agency->save();
        return true;
    }

    /**
     * Update status
     */
    public function updateStatus($agency, $status)
    {
        $agency->status = $status;
        $this->updateAgency($agency);
        return true;
    }

    /**
     * Get by additional info
     */
    public function getByAdditionalInfo()
    {
        return $this->model
            ->where('status', 0)
            ->whereHas('additionalInfo', function ($query) {
                $query->where('status', 0);
            })
            ->with('additionalInfo')
            ->get();
    }

    /**
     * Get active agency
     */
    public function getActiveAgency($id, $perPage, $page)
    {
        return $this->model
            ->withoutGlobalScope(HostAgencyScope::class)
            ->where(function ($query) {
                $query->whereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->orderByDesc('id')
            ->when(isset($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->with(['owner', 'owner.profile'])
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all active agency
     */
    public function getAllActiveAgency($id)
    {
        return $this->model
            ->withoutGlobalScope(HostAgencyScope::class)
            ->when(isset($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->with(['owner', 'owner.profile'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get agency by ID
     */
    public function agencyById($id)
    {
        return $this->model
            ->where(function ($query) {
                $query->whereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->with(['owner', 'owner.profile'])
            ->findOrFail($id);
    }

    /**
     * Get by additional info paginate
     */
    public function getByAdditionalInfoPaginate($id, $uuid, $perPage, $page, $status = null, $action = null)
    {
        if ($action == null) {
            $agencies = $this->model
                ->withoutGlobalScope(HostAgencyScope::class)
                ->where('status', 0)
                ->whereHas('additionalInfo', function ($query) {
                    $query->where('status', 0);
                });
        } else {
            $agencies = $this->model
                ->withoutGlobalScope(HostAgencyScope::class)
                ->where('status', '!=', 0)
                ->whereHas('additionalInfo', function ($query) {
                    $query->where('status', '!=', 0);
                });
        }

        $agencies = $agencies
            ->whereHas('owner', function ($query) use ($uuid) {
                $query->when(isset($uuid), function ($query) use ($uuid) {
                    $query->where('uuid', $uuid);
                });
            })
            ->with(['additionalInfo', 'owner', 'owner.profile'])
            ->when(isset($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->when(isset($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc("id")
            ->paginate($perPage, ['*'], 'page', $page);

        return $agencies;
    }

    /**
     * Get agency by filter
     */
    public function getAgencyByFilter($keyword)
    {
        return $this->model
            ->with(['owner', 'owner.profile'])
            ->where(function ($q) use ($keyword) {
                $q->where('id', 'like', '%' . $keyword . '%');
            })
            ->get();
    }

    /**
     * Count agency user admin
     */
    public function countAgencyUserAdmin($userId)
    {
        return $this->model->where('agency_manger_id', $userId)->count();
    }

    /**
     * Get by agency manager ID
     */
    public function getByAgencyMangerId($agencyMangerId)
    {
        return $this->model
            ->where('agency_manger_id', $agencyMangerId)
            ->with(['owner', 'owner.profile'])
            ->get();
    }

    /**
     * Get admin by user ID
     */
    public function getAdminByUserId($userId)
    {
        return AgencyUserJob::where('user_id', $userId)
            ->where('type', 'requestManger')
            ->first();
    }

    /**
     * Get agency by ID
     */
    public function getAgencyById($agencyId)
    {
        return Agency::where('id', $agencyId)->first();
    }

    /**
     * Get agency by owner ID
     */
    public function getAgencyByOwnerId($ownerId)
    {
        return Agency::where('app_owner_id', $ownerId)->first();
    }

    /**
     * Get join requests
     */
    public function getJoinRequests($agencyId)
    {
        return AgencyJoinRequest::where('agency_id', $agencyId);
    }

    /**
     * Get agencies by IDs
     */
    public function getByIds($ids)
    {
        return $this->model
            ->withoutGlobalScope(HostAgencyScope::class)
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Get agencies
     */
    public function agencies($id, $search, $perPage, $page)
    {
        return $this->model
            ->withoutGlobalScope(HostAgencyScope::class)
            ->where('id', '!=', $id)
            ->where(function ($query) {
                $query->whereDoesntHave('additionalInfo')
                    ->orWhereHas('additionalInfo', function ($query) {
                        $query->where('status', 1);
                    });
            })
            ->when(isset($search), function ($query) use ($search) {
                $query->whereHas('owner', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('uuid', 'like', "%$search%");
                });
            })
            ->with(['owner', 'owner.profile'])
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get report
     */
    public function report($id, $month = null, $year = null, $perPage, $page)
    {
        return $this->model
            ->when(isset($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->whereHas('agencySalaries', function ($q) use ($month, $year) {
                $q->when(isset($month) && isset($year), function ($query) use ($month, $year) {
                    $query->where('month', $month)->where('year', $year);
                });
            })
            ->withCount('users')
            ->with(['owner', 'dashOwner', 'agencySalaries' => function ($query) use ($month, $year) {
                $query->when(isset($month) && isset($year), function ($query) use ($month, $year) {
                    $query->where('month', $month)->where('year', $year);
                });
            }])
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($agency) use ($month, $year) {
                $agency->target = $agency->getTotalSallaryAgency($month, $year);
                $agency->expenses = $agency->getTotalCutAmountAgency($month, $year);
                $agency->salary = $agency->getSalaryAgency($month, $year);
                return $agency;
            });
    }

    /**
     * Get charge agency
     */
    public function getChargeAgency($id)
    {
        return $this->model
            ->withoutGlobalScope(HostAgencyScope::class)
            ->whereHas('chargeAgency')
            ->when(isset($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->with(['owner', 'owner.profile'])
            ->get();
    }
}
