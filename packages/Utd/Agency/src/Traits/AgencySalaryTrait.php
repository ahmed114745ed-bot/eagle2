<?php

namespace Utd\Agency\Traits;

use Illuminate\Support\Facades\DB;
use Utd\Agency\Entities\AgencySalary;

trait AgencySalaryTrait
{
    /**
     * Get target for specific month and year
     */
    public function target($month = null, $year = null)
    {
        $month = $month ?: date('m');
        $year = $year ?: date('Y');

        return $this->hasMany(AgencySalary::class, 'agency_id')
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get target attribute
     */
    public function getTargetAttribute($month = null, $year = null)
    {
        $month = $month ?: date('m');
        $year = $year ?: date('Y');

        return $this->hasMany(AgencySalary::class, 'agency_id')
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    /**
     * Get salary attribute
     */
    public function getSalaryAttribute()
    {
        return $this->agencySalaries->sum(fn ($row) => ($row->sallary - $row->cut_amount));
    }

    /**
     * Set salary attribute
     */
    public function setSalaryAttribute()
    {
        $salary = AgencySalary::query()
            ->where('agency_id', $this->id)
            ->where('is_paid', 0)
            ->sum(DB::raw('sallary - cut_amount'));

        $this->attributes['salary'] = $salary;

        return $salary;
    }

    /**
     * Get total salary for agency
     */
    public function getTotalSallaryAgency($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $agencySalary = AgencySalary::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary'));

        return floor($agencySalary ?? 0);
    }

    /**
     * Get total cut amount for agency
     */
    public function getTotalCutAmountAgency($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $agencySalary = AgencySalary::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('cut_amount'));

        return round($agencySalary, 2);
    }

    /**
     * Get total net salary for agency
     */
    public function getTotalNetSallaryAgency($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $result = AgencySalary::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->selectRaw('SUM(sallary) - SUM(cut_amount) as total')
            ->value('total');

        return round($result, 2);
    }

    /**
     * Get salary for agency
     */
    public function getSalaryAgency($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $agencySalary = AgencySalary::query()
            ->where(DB::raw('concat(year,"-", month)'), '<=', $year.'-'.$month)
            ->where('is_paid', 0)
            ->where('agency_id', $this->id)
            ->orderByDesc('id')
            ->sum(DB::raw('sallary - cut_amount'));

        return round($agencySalary, 2);
    }

    /**
     * Get salary for specific period
     */
    public function getSalary($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $agencySalary = AgencySalary::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary - cut_amount'));

        return floor($agencySalary ?? 0);
    }

    /**
     * Sum net salary
     */
    public function sumNetSalary($month = null, $year = null)
    {
        $agencySalary = AgencySalary::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary - cut_amount'));

        return $agencySalary ?? 0;
    }

    /**
     * Sum cut amount
     */
    public function sumCutAmount($month = null, $year = null)
    {
        $agencySalary = AgencySalary::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('cut_amount'));

        return floor($agencySalary ?? 0);
    }

    /**
     * Sum salary
     */
    public function sumSalary($month = null, $year = null)
    {
        $agencySalary = AgencySalary::query()
            ->when(isset($month) && isset($year), function ($query) use ($year, $month) {
                $query->where(DB::raw('concat(year,"-", month)'), '=', $year.'-'.$month);
            })
            ->where('agency_id', $this->id)
            ->sum(DB::raw('sallary'));

        return $agencySalary ?? 0;
    }
}
