<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Facades\ManagerHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user->id,
            'uuid' => $this->uuid->uuid,
            'name' => @$this->name ?: '',
            'target' => $this->target,
            'due' => ManagerHelper::getTotalAgenciesSalary($this->managerAgencies, $this->app_id),
        ];
    }
}
