<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\AgencyJoinRequest;

class AgencyJoinRequestRepository
{
    protected AgencyJoinRequest $model;

    public function __construct()
    {
        $this->model = new AgencyJoinRequest();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new request
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update request
     */
    public function update($id, array $data)
    {
        $request = $this->findById($id);

        if ($request) {
            $request->update($data);

            return $request->fresh();
        }

        return null;
    }

    /**
     * Delete request
     */
    public function delete($id)
    {
        $request = $this->findById($id);

        if ($request) {
            return $request->delete();
        }

        return false;
    }

    /**
     * Count by month for user
     */
    public function countByMonth($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * Count by agency for user
     */
    public function countByAgency($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->where('status', 0)
            ->count();
    }

    /**
     * Get by user
     */
    public function getByUser($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('agency')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get by agency
     */
    public function getByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->with('user')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get pending requests by agency
     */
    public function getPendingByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('status', 0)
            ->with(['user', 'user.profile'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get all requests by agency with filters
     */
    public function getByAgencyWithStatus($agencyId, $status = null)
    {
        $query = $this->model
            ->where('agency_id', $agencyId)
            ->with(['user', 'user.profile', 'admin', 'userOperator']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id')->get();
    }

    /**
     * Get paginated requests by agency with status
     */
    public function getPaginatedByAgencyWithStatus($agencyId, $status = null, $perPage = 10)
    {
        $query = $this->model
            ->where('agency_id', $agencyId)
            ->with(['user', 'user.profile', 'admin', 'userOperator']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Accept request
     */
    public function accept($id)
    {
        $request = $this->findById($id);

        if ($request) {
            $request->status = 1;

            return $request->save();
        }

        return false;
    }

    /**
     * Reject request
     */
    public function reject($id)
    {
        $request = $this->findById($id);

        if ($request) {
            $request->status = 2;

            return $request->save();
        }

        return false;
    }
}
