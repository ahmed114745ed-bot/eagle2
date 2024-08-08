<?php

namespace Modules\Events\Transformers;

use Carbon\Carbon;
use Modules\Events\Entities\GeneralRole;
use App\Http\Resources\GiftResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PkEventResource extends JsonResource
{
    public function toArray($request)
    {
        $endDate = $this->end_date;
        $time = Carbon::now()->diff($endDate);
        $timeComponents = [
            'day' => @$time->days ?? 0,
            'hour' => @$time->h ?? 0,
            'minute' => @$time->i ?? 0,
            'second' => @$time->s ?? 0,
        ];
        $rule=GeneralRole::query()->where("type",'pk_event')->first();
        return [

            'description' =>  $rule != null ? app()->getLocale() == 'ar' ? $rule->desc_ar:$rule->desc_en : "",
            'remainingTime' => $timeComponents,
        ];
    }
}
