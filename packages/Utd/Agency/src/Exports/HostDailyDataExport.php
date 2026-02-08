<?php

namespace Utd\Agency\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HostDailyDataExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'User ID',
            'User Name',
            'User UUID',
            'Agency ID',
            'Date',
            'Diamonds',
            'Coins',
            'Hours',
            'Target',
            'Achievement Rate %',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        // If $row is a resource instance, get the resource array
        $data = is_object($row) && method_exists($row, 'toArray') 
            ? $row->toArray(request()) 
            : (array) $row;

        return [
            $data['id'] ?? '',
            $data['user_id'] ?? '',
            $data['user']['name'] ?? '',
            $data['user']['uuid'] ?? '',
            $data['agency_id'] ?? '',
            $data['date'] ?? '',
            $data['diamonds'] ?? 0,
            $data['coins'] ?? 0,
            $data['hours'] ?? 0,
            $data['target'] ?? 0,
            $data['achievement_rate'] ?? 0,
        ];
    }
}
