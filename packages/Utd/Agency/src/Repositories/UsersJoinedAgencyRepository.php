<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\UsersJoinedAgency;

class UsersJoinedAgencyRepository
{
    protected UsersJoinedAgency $model;

    public function __construct()
    {
        $this->model = new UsersJoinedAgency();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update($id, array $data)
    {
        $record = $this->findById($id);
        
        if ($record) {
            $record->update($data);
            return $record->fresh();
        }
        
        return null;
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
     * Get active membership
     */
    public function getActiveMembership($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->whereNull('leave_date')
            ->first();
    }

    /**
     * Get user history
     */
    public function getUserHistory($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereHas('agency')
            ->with(['agency:id,img,name'])
            ->select('join_date', 'leave_date', 'agency_id')
            ->orderByDesc('join_date')
            ->get();
    }

    /**
     * Record join
     */
    public function recordJoin($userId, $agencyId)
    {
        return $this->create([
            'user_id' => $userId,
            'agency_id' => $agencyId,
            'join_date' => now(),
        ]);
    }

    /**
     * Record leave
     */
    public function recordLeave($userId, $agencyId)
    {
        $membership = $this->getActiveMembership($userId, $agencyId);
        
        if ($membership) {
            $membership->leave_date = now();
            return $membership->save();
        }
        
        return false;
    }

    /**
     * Get agency members history
     */
    public function getAgencyMembersHistory($agencyId, $perPage = 20, $page = 1)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->with('user')
            ->orderByDesc('join_date')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
