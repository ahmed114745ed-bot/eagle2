<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BoxUseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $startTime = Carbon::createFromTimestamp($this->start_at);
        // $currentTime = Carbon::now();
        // if ($startTime >= $currentTime) {
        //     $rem_time = $startTime->diffInSeconds($currentTime);
        // } else {
        //     $rem_time = 0;
        // }


        // $rem_time = Carbon::createFromTimestamp($this->start_at)->diffInSeconds(now());


        if (!$this->user instanceof User) return [];
        return [
            'id' => $this->id,
            'user' => [
                'id'        => $this->user->id,
                'uuid'      => $this->user->uuid,
                'image'     => $this->user?->profile?->avatar ?? '',
                'name'      => $this->user->name,
                'is_follow'            => @(bool)Common::IsFollow(@$request->user()->id, $this->user->id), // user data  ----
            ],
            'coins' => $this->box->coins,
            //            'end_at'=>$this->end_at,
            //            'room_uid'=>$this->room_uid,
            //            'room_id'=>$this->room_id,
            'users_num' => $this->users_num,
            //            'not_used_num'=>$this->not_used_num,
            'type' => $this->type == 1 ? 'super' : 'normal',
            //            'label'=>$this->label,
            //            'image'=>$this->image,
            //            'rem_time'=>$this->type == 1 ? $rem_time : 0
            // 'rem_time' => $rem_time,
            "end_time" => Carbon::createFromTimestamp($this->end_at)->toDateTimeString(),
        ];
    }
}
