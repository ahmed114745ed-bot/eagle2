<?php

namespace Utd\Agency\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Utd\Agency\Entities\Agency;

class AgencyExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;

    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? now()->month;
        $this->year = $year ?? now()->year;
    }

    /**
     * Get collection
     */
    public function collection()
    {
        return Agency::with(['owner', 'agencySalaries' => function ($query) {
            $query->where('month', $this->month)->where('year', $this->year);
        }])
            ->withCount('users')
            ->get();
    }

    /**
     * Get headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Owner',
            'Owner UUID',
            'Phone',
            'Members Count',
            'Status',
            'Salary',
            'Cut Amount',
            'Net Salary',
            'Created At',
        ];
    }

    /**
     * Map row
     */
    public function map($agency): array
    {
        $salary = $agency->agencySalaries->first();

        return [
            $agency->id,
            $agency->name,
            $agency->owner?->name,
            $agency->owner?->uuid,
            $agency->phone,
            $agency->users_count,
            $agency->status === 1 ? 'Active' : 'Inactive',
            $salary?->sallary ?? 0,
            $salary?->cut_amount ?? 0,
            ($salary?->sallary ?? 0) - ($salary?->cut_amount ?? 0),
            $agency->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
