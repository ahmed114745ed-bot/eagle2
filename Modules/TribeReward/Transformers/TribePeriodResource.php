<?php

namespace Modules\TribeReward\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TribePeriodResource extends JsonResource
{
    public function toArray($request)
    {
        $rewards = [];
        foreach ($this->tribeTops as $top) {
            $range = ($top->min == $top->max)
                ? "top {$top->min}"
                : "top {$top->min}-{$top->max}";

            $rewards[$range] = TribeRewardResource::collection($top->tribeRewards);
        }

        return [
            'role' => 'Top Agency Leader',
            'end_time' => $this->end_date,
            'rewards' => $rewards,
        ];
    }
}
