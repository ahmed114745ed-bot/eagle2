<?php

namespace Modules\Events\Transformers;

use Carbon\Carbon;
use App\Helpers\Common;

use Modules\Events\Entities\GeneralRole;
use Illuminate\Http\Resources\Json\JsonResource;

class UserChargeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */


    public function toArray($request)
    {

        $currentDate = Carbon::now();

        $endOfMonth = $currentDate->copy()->endOfMonth();

        // Calculate the difference between the end of the month and the current date
        $timeDifference = $endOfMonth->diff($currentDate);
        $timeComponents = [
            'day' => $timeDifference->days,
            'hour' => $timeDifference->h,
            'minute' => $timeDifference->i,
            'second' => $timeDifference->s,
        ];
        $TotalAmount  = $this->charges->sum('amount') + $this->coinLogs->sum('obtained_coins');
        $rule = GeneralRole::query()->where("type", 'charge_event')->first();
        if ($TotalAmount < 0 ){
            $TotalAmount = 0 ;
        }
        return [

            'user' => [
                'user_id'   => $this->id,
                'uuid'      => $this->uuid ?? 0,
                'name'      => $this->name ?? '',
                'avatar'    => $this->profile->avatar ?? '',
                'amount'    => $TotalAmount ?? 0,
            ],

            'role'      => $rule != null ? app()->getLocale() == 'ar' ? $rule->desc_ar : $rule->desc_en : "",
            'remainingTime' => $timeComponents,
        ];
    }
}
