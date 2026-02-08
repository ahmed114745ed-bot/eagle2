<?php

namespace App\Classes\Agencies;

use Illuminate\Http\Request;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Agency\Classes\Agencies\AgencyDataSearch instead
 */
if (class_exists('\Utd\Agency\Classes\Agencies\AgencyDataSearch')) {
    class AgencyDataSearch extends \Utd\Agency\Classes\Agencies\AgencyDataSearch
    {
        // This class is just an alias for backward compatibility
        // All functionality is inherited from the package
    }
} else {
    // Fallback implementation when package is not available
    class AgencyDataSearch
    {
        public function fetchData(Request $request)
        {
            // Basic fallback implementation
            return [
                'agencies' => [],
                'filters' => $request->only(['agency_id', 'owner_id', 'country_id']),
            ];
        }
        
        public function getStatistics(Request $request)
        {
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'pending' => 0,
            ];
        }
    }
}
