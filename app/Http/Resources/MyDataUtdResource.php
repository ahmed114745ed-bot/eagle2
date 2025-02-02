<?php

namespace App\Http\Resources;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyDataUtdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? 0,
            'name' => $this->name  ?? '',
            'uuid' => $this->uuid ?? 0,
            'image' => $this->profile->avatar ?? '',
            'bio' => @$this->bio ?: '',
            'coins' => $this->di ?? 0,
            'diamonds' => $this->exchange_diamonds ?? 0,
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'country' => new CountryResource(@$this->country),
            'level' => Common::level_center(@$this),
            'vip' => Common::ovip_center($this),
            'agency' => [
                'id' => $this->agency_id ?? 0,
                'image' => @$this?->agency?->img ?? '',
                'owner' => new OwnerAgencyResource(@$this?->agency?->owner)
            ],





        ];
    }
}
