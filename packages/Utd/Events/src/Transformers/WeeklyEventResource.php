<?php

namespace Utd\Events\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Events\Entities\GeneralRole;

class WeeklyEventResource extends JsonResource
{
    public $type;

    public function __construct($resource, $type)
    {
        parent::__construct($resource);
        $this->type = $type;
    }

    public function toArray($request)
    {
        $endDate = $this->end_date_local;
        $time = Carbon::now()->copy()->diff($endDate);
        $timeComponents = [
            'day' => @$time->days ?? 0,
            'hour' => @$time->h ?? 0,
            'minute' => @$time->i ?? 0,
            'second' => @$time->s ?? 0,
        ];

        $rule = GeneralRole::query()->where('type', $this->type)->first();

        return [

            'description' => $rule !== null ? app()->getLocale() === 'ar' ? $rule->desc_ar : $rule->desc_en : '',
            'remainingTime' => $timeComponents,
            'gifts' => weeklyGiftResource::collection($this->gifts),
        ];
    }
}
