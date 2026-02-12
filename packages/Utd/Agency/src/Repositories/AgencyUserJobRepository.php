<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\AgencyUserJob;

class AgencyUserJobRepository
{
    protected AgencyUserJob $model;

    public function __construct()
    {
        $this->model = new AgencyUserJob();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new user job
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update user job
     */
    public function update($id, array $data)
    {
        $job = $this->findById($id);

        if ($job) {
            $job->update($data);

            return $job->fresh();
        }

        return null;
    }

    /**
     * Delete user job
     */
    public function delete($id)
    {
        $job = $this->findById($id);

        if ($job) {
            return $job->delete();
        }

        return false;
    }

    /**
     * Get by agency
     */
    public function getByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->with('user')
            ->get();
    }

    /**
     * Get by user
     */
    public function getByUser($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('agency')
            ->get();
    }

    /**
     * Get by agency and type
     */
    public function getByAgencyAndType($agencyId, $type)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('type', $type)
            ->with('user')
            ->get();
    }

    /**
     * Get admins by agency
     */
    public function getAdminsByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('type', AgencyUserJob::TYPE_REQUEST_MANAGER)
            ->with('user')
            ->get();
    }

    /**
     * Check if user is admin of agency
     */
    public function isAdmin($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->where('type', AgencyUserJob::TYPE_REQUEST_MANAGER)
            ->exists();
    }

    /**
     * Make user admin
     */
    public function makeAdmin($userId, $agencyId)
    {
        return $this->create([
            'user_id' => $userId,
            'agency_id' => $agencyId,
            'type' => AgencyUserJob::TYPE_REQUEST_MANAGER,
        ]);
    }

    /**
     * Remove admin role
     */
    public function removeAdmin($userId, $agencyId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('agency_id', $agencyId)
            ->where('type', AgencyUserJob::TYPE_REQUEST_MANAGER)
            ->delete();
    }

    /**
     * Get by user and type
     */
    public function getByUserAndType($userId, $type)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();
    }
}
