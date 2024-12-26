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
            'levels' => $this->getLevelGroups()
        ];
    }

private function getLevelGroups()
{

    // Use Cache::remember to cache the results for 1 hour
    return Cache::remember('levels_chunks', 3600, function () {
        // Fetch all rows for the given type and order by level
        $vips = Vip::all();

        // Group rows into chunks of 9
        $chunks = $vips->chunk(10);

        // Transform each chunk into the desired structure
        $levelGroups = $chunks->map(function ($chunk) {
            return [
                'minlevel' => $chunk->first()->level, // Minimum level in the chunk
                'maxlevel' => $chunk->last()->level,  // Maximum level in the chunk
                'badge' => $chunk->last()->img,
            ];
        });

        return $levelGroups->toArray(); // Convert collection to array
    });
}


}
