<?php

namespace App\Http\Resources\Agency;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Alias for backward compatibility
 * @deprecated Use Utd\Agency\Transformers\AgencyInvitationResource instead
 */
if (class_exists('\Utd\Agency\Transformers\AgencyInvitationResource')) {
    class AgencyInvitationResource extends \Utd\Agency\Transformers\AgencyInvitationResource
    {
        // This class is just an alias for backward compatibility
        // All functionality is inherited from the package
    }
} else {
    // Fallback implementation when package is not available
    class AgencyInvitationResource extends JsonResource
    {
        public function toArray($request)
        {
            return [
                'id' => $this->id ?? null,
                'agency_id' => $this->agency_id ?? null,
                'user_id' => $this->user_id ?? null,
                'status' => $this->status ?? null,
                'created_at' => $this->created_at ?? null,
                'updated_at' => $this->updated_at ?? null,
            ];
        }
    }
}
