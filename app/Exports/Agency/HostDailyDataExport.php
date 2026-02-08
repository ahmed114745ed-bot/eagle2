<?php

namespace App\Exports\Agency;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Agency\Exports\HostDailyDataExport instead
 */
if (class_exists('\Utd\Agency\Exports\HostDailyDataExport')) {
    class HostDailyDataExport extends \Utd\Agency\Exports\HostDailyDataExport
    {
        // This class is just an alias for backward compatibility
        // All functionality is inherited from the package
    }
} else {
    // Fallback implementation when package is not available
    class HostDailyDataExport implements FromCollection, WithHeadings
    {
        protected $data;
        
        public function __construct($data)
        {
            $this->data = $data;
        }
        
        public function collection()
        {
            return collect($this->data);
        }
        
        public function headings(): array
        {
            return ['ID', 'User ID', 'Agency ID', 'Date', 'Diamonds', 'Coins', 'Hours'];
        }
    }
}
