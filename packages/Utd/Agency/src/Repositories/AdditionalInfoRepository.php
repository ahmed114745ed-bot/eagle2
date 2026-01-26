<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\AdditionalInfo;

class AdditionalInfoRepository
{
    protected AdditionalInfo $model;

    public function __construct()
    {
        $this->model = new AdditionalInfo();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new info
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update info
     */
    public function update($id, array $data)
    {
        $info = $this->findById($id);
        
        if ($info) {
            $info->update($data);
            return $info->fresh();
        }
        
        return null;
    }

    /**
     * Delete info
     */
    public function delete($id)
    {
        $info = $this->findById($id);
        
        if ($info) {
            return $info->delete();
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
            ->first();
    }

    /**
     * Get pending
     */
    public function getPending()
    {
        return $this->model
            ->where('status', 0)
            ->with('agency')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Approve
     */
    public function approve($id)
    {
        $info = $this->findById($id);
        
        if ($info) {
            $info->status = 1;
            return $info->save();
        }
        
        return false;
    }

    /**
     * Reject
     */
    public function reject($id)
    {
        $info = $this->findById($id);
        
        if ($info) {
            $info->status = 2;
            return $info->save();
        }
        
        return false;
    }

    /**
     * Create or update for agency
     */
    public function createOrUpdate($agencyId, array $data)
    {
        $info = $this->getByAgency($agencyId);
        
        if ($info) {
            $info->update($data);
            return $info->fresh();
        }
        
        $data['agency_id'] = $agencyId;
        return $this->create($data);
    }
}
