<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveAgencyMembersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            $this->mempers()
                ->orderBy('monthly_diamond_received', 'desc')
                ->with(['userSallary' => function ($query) {
                    $query->select('id', 'user_id', 'sallary')->where('month', now()->month)->where('year', now()->year);
                }, 'profile' => function ($query) {
                    $query->select('id', 'user_id', 'avatar');
                }])
                ->get(['id', 'uuid', 'total_days', 'name', 'monthly_diamond_received'])
        ];
    }
}
