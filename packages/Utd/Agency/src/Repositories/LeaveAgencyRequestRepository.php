<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\LeaveAgencyRequest;

class LeaveAgencyRequestRepository
{
    protected LeaveAgencyRequest $model;

    public function __construct()
    {
        $this->model = new LeaveAgencyRequest();
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
     * Get pending by agency
     */
    public function getPendingByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('status', 0)
            ->with('user')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Check pending request exists
     */
    public function hasPendingRequest($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->where('status', 0)
            ->exists();
    }

    /**
     * Approve request
     */
    public function approve($id)
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

    /**
     * Get request by user and agency
     */
    public function getRequest($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->first();
    }
}
