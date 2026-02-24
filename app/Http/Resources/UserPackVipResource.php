<?php

namespace App\Http\Resources;

use App\Support\PackageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Vip\Http\Resources\UserVipUtdResource;

class UserPackVipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'packs' => UserPackUtdResource::collection($this->packsUser),
            'vip' => PackageHelper::isInstalled('vip')
                ? UserVipUtdResource::collection($this->userHaveVip)
                : [],
        ];
    }
}
