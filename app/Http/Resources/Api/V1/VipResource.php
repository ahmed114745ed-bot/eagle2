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
        $sender_vips = Vip::where('type',2)->get();
        $receiver_vips = Vip::where('type',1)->get();

        // Group rows into chunks of 9
        $sender_chunks = $sender_vips->chunk(10);
        $receiver_chunks = $receiver_vips->chunk(10);

        // Transform each chunk into the desired structure
        $sender_levelGroups = $sender_chunks->map(function ($chunk) {
            return [
                'minlevel' => $chunk->first()->level, // Minimum level in the chunk
                'maxlevel' => $chunk->last()->level,  // Maximum level in the chunk
                'badge' => $chunk->last()->img,
            ];
        });


        $receiver_levelGroups = $receiver_chunks->map(function ($chunk) {
            return [
                'minlevel' => $chunk->first()->level, // Minimum level in the chunk
                'maxlevel' => $chunk->last()->level,  // Maximum level in the chunk
                'badge' => $chunk->last()->img,
            ];
        });

        return ['sender' => $sender_levelGroups->toArray(), 'receiver' => $receiver_levelGroups->toArray()]; // Convert collection to array
    });
}


}
