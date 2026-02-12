<?php

namespace Utd\Agency\Services;

use Carbon\Carbon;
use Utd\Agency\Repositories\AgencyRepository;
use Utd\Agency\Repositories\AgencySalaryRepository;

class TargetService
{
    public function __construct(
        protected AgencyRepository $agencyRepository,
        protected AgencySalaryRepository $agencySalaryRepository,
    ) {}

    /**
     * Calculate agency target
     */
    public function calculateAgencyTarget($agencyId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $agency = $this->agencyRepository->findById($agencyId);

        if (! $agency) {
            return 0;
        }

        return $agency->getTotalSallaryAgency($month, $year);
    }

    /**
     * Calculate user target
     */
    public function calculateUserTarget($userId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        // Calculate based on user salary
        return 0;
    }

    /**
     * Get target summary
     */
    public function getTargetSummary($agencyId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $agency = $this->agencyRepository->findById($agencyId);

        if (! $agency) {
            return null;
        }

        return [
            'agency_id' => $agencyId,
            'month' => $month,
            'year' => $year,
            'total_salary' => $agency->getTotalSallaryAgency($month, $year),
            'total_cut_amount' => $agency->getTotalCutAmountAgency($month, $year),
            'net_salary' => $agency->getTotalNetSallaryAgency($month, $year),
        ];
    }

    /**
     * Get members target
     */
    public function getMembersTarget($agencyId, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $agency = $this->agencyRepository->findById($agencyId);

        if (! $agency) {
            return [];
        }

        return $agency->users()
            ->with(['userSalaries' => function ($query) use ($month, $year) {
                $query->where('month', $month)->where('year', $year);
            }])
            ->get()
            ->map(function ($user) {
                $salary = $user->userSalaries->first();

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'target_diamonds' => $salary->target_diamonds ?? 0,
                    'achieved' => $salary->achieved ?? 0,
                ];
            });
    }

    /**
     * Update agency salary
     */
    public function updateAgencySalary($agencyId, $month, $year, $salary, $cutAmount = 0)
    {
        $existing = $this->agencySalaryRepository->getByAgencyAndPeriod($agencyId, $month, $year);

        if ($existing) {
            return $this->agencySalaryRepository->update($existing->id, [
                'sallary' => $salary,
                'cut_amount' => $cutAmount,
            ]);
        }

        return $this->agencySalaryRepository->create([
            'agency_id' => $agencyId,
            'month' => $month,
            'year' => $year,
            'sallary' => $salary,
            'cut_amount' => $cutAmount,
            'is_paid' => 0,
        ]);
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryAsPaid($salaryId)
    {
        return $this->agencySalaryRepository->markAsPaid($salaryId);
    }

    /**
     * Get unpaid salaries
     */
    public function getUnpaidSalaries($agencyId)
    {
        return $this->agencySalaryRepository->getUnpaidByAgency($agencyId);
    }

    /**
     * Get total unpaid salary
     */
    public function getTotalUnpaidSalary($agencyId)
    {
        return $this->agencySalaryRepository->sumUnpaidSalary($agencyId);
    }
}
