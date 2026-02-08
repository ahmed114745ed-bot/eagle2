<?php

namespace App\Http\Resources\Agency;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Agency\Transformers\HostDailyReportResource instead
 */
if (class_exists('\Utd\Agency\Transformers\HostDailyReportResource')) {
    class HostDailyReportResource extends \Utd\Agency\Transformers\HostDailyReportResource
    {
        // This class is just an alias for backward compatibility
        // All functionality is inherited from the package
    }
} else {
    // Fallback implementation when package is not available
    class HostDailyReportResource extends JsonResource
    {
        public function toArray($request)
        {
            return [
                'id' => $this->id ?? null,
                'user_id' => $this->user_id ?? null,
                'agency_id' => $this->agency_id ?? null,
                'date' => $this->date ?? null,
                'diamonds' => $this->diamonds ?? 0,
                'coins' => $this->coins ?? 0,
                'hours' => $this->hours ?? 0,
            ];
        }
    }
}
