<?php

namespace Utd\Agency\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Utd\Agency\Entities\AgencySalary;

class AgencySalaryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;

    protected $year;

    protected $isPaid;

    public function __construct($month = null, $year = null, $isPaid = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->isPaid = $isPaid;
    }

    /**
     * Get collection
     */
    public function collection()
    {
        return AgencySalary::with('agency')
            ->when($this->month && $this->year, function ($query) {
                $query->where('month', $this->month)->where('year', $this->year);
            })
            ->when($this->isPaid !== null, function ($query) {
                $query->where('is_paid', $this->isPaid);
            })
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Agency ID',
            'Agency Name',
            'Month',
            'Year',
            'Salary',
            'Cut Amount',
            'Net Salary',
            'Is Paid',
            'Created At',
        ];
    }

    /**
     * Map row
     */
    public function map($salary): array
    {
        return [
            $salary->id,
            $salary->agency_id,
            $salary->agency?->name,
            $salary->month,
            $salary->year,
            $salary->sallary,
            $salary->cut_amount,
            $salary->sallary - $salary->cut_amount,
            $salary->is_paid ? 'Yes' : 'No',
            $salary->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
