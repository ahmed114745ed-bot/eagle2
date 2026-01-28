<?php

namespace Utd\Agency\Repositories;

use Utd\Agency\Entities\AgencySalary;
use Illuminate\Support\Facades\DB;

class AgencySalaryRepository
{
    protected AgencySalary $model;

    public function __construct()
    {
        $this->model = new AgencySalary();
    }

    /**
     * Find by ID
     */
    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new salary record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update salary record
     */
    public function update($id, array $data)
    {
        $salary = $this->findById($id);
        
        if ($salary) {
            $salary->update($data);
            return $salary->fresh();
        }
        
        return null;
    }

    /**
     * Get by agency
     */
    public function getByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get by agency and period
     */
    public function getByAgencyAndPeriod($agencyId, $month, $year)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get unpaid by agency
     */
    public function getUnpaidByAgency($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('is_paid', 0)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Sum unpaid salary by agency
     */
    public function sumUnpaidSalary($agencyId)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('is_paid', 0)
            ->sum(DB::raw('sallary - cut_amount'));
    }

    /**
     * Sum salary by agency and period
     */
    public function sumSalaryByPeriod($agencyId, $month, $year)
    {
        return $this->model
            ->where('agency_id', $agencyId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum(DB::raw('sallary - cut_amount'));
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($id)
    {
        $salary = $this->findById($id);
        
        if ($salary) {
            $salary->is_paid = 1;
            return $salary->save();
        }
        
        return false;
    }

    /**
     * Mark multiple as paid
     */
    public function markMultipleAsPaid(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->update(['is_paid' => 1]);
    }

    /**
     * Get salary report
     */
    public function getSalaryReport($month = null, $year = null, $perPage = 20, $page = 1)
    {
        return $this->model
            ->when(isset($month) && isset($year), function ($query) use ($month, $year) {
                $query->where('month', $month)->where('year', $year);
            })
            ->with('agency')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
