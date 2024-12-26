<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Vip;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class VipResource extends JsonResource
{
    public static $prevs = null;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'exp' => $this->exp,
            'img' => $this->img,
            'sender' => $this->getLevelData(2,10),
            'receiver' => $this->getLevelData(1,10),
            'badge' => Vip::query()->orderBy('id', 'desc')->first()?->img
        ];
    }

private function getLevelData($type, $patternNum)
{
    // Create a unique cache key based on the function parameters
    $cacheKey = "level_data_{$type}_{$patternNum}";

    // Use Laravel's cache helper to retrieve or store the data
    return Cache::remember($cacheKey, 3600, function () use ($type, $patternNum) {
        // Query for the minimum level
        $min_level = Vip::query()
            ->where(function ($query) use ($patternNum) {
                $query->whereRaw("level % $patternNum = 1")->orWhere('level', 1);
            })
            ->where('type', $type)
            ->orderBy('level', 'asc')
            ->first();

        // Query for the maximum level
        $max_level = Vip::query()
            ->where(function ($query) use ($patternNum) {
                $query->whereRaw("level % $patternNum = 1")->orWhere('level', 1);
            })
            ->where('type', $type)
            ->orderBy('level', 'desc')
            ->first();

        // Return the result as an array
        return [
            'min_level' => $min_level,
            'max_level' => $max_level,
        ];
    });
}

}
