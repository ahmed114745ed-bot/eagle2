<?php

namespace App\Http\Resources\Api\V1;
use App\Models\User;
use App\Facades\UserHandling;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Achievement\Http\Services\UserAchievementService;

class AllUsersResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    { 
        $achievement      = new UserAchievementService();
        $data_achivement = $achievement->getUserAchievement($this->resource);
        return [
            'id' => $this->id,
            'coins' => number_format($this->di),
            'uuid' => $this->uuid . ' ' . $this->original_uuid,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'charge_status' => $this->charge_status,
            'hide_chat' => $this->userSetting->hide_chat,
            'reals_count' =>  count($this->reals),
            'moment_count' => count($this->moments),
            'total_days' => $this->total_days,
            'total_hours' => $this->liveTime->sum("hours"),
            'image' => $this->profile->avatar,
            'can_play' => UserHandling::chickLevelToPlay($this->resource),
            'phone' => $this->phone,
            'agency_id' => $this->agency_id,
            'family_id' => $this->family_id,
            'agency' => [
                'id' => $this->agency->id,
                'name' => $this->agency->name,
                'notice' => $this->agency->notice,
                'phone' =>$this->agency->phone,
                'url' => $this->agency->url,
                'image'=> $this->agency->img,
                'contents' => $this->agency->contents,
            ],
            'target' => $this->targets()->orderBy('created_at', 'desc')->get()->map(function ($target) {
                $target = $target->only(
                    [
                        'id',
                        'add_month',
                        'add_year',
                        'target_usd',
                        'target_hours',
                        'target_days',
                        'target_agency_share',
                        'user_diamonds',
                        'user_hours',
                        'user_days',
                        'user_obtain',
                        'agency_obtain',
                        'updated_at'
                    ]
                );


                return $target;
            }),
            'device-token' => [
                'count' =>User::where('device_token', $this->device_token)->count(),
                'users' => User::select("name", 'uuid', 'phone')->where('device_token', $this->device_token)->where('device_token', '!=', null)->map(function ($user) {
                    return [  'name'     => $user['name'],
                    'uuid' => $user['uuid'],
                    'phone'  => $user['phone']];
    
                }),
            ],
            'achievements' => $data_achivement->map(function ($user) {

                $img = $user["valid_image"] ?? $user["custom_image"];
                return [
                    'id'     => $user['id'],
                    'target' => $user['target'],
                    'image'  => $img,
                ];
            }),


        ];
    }

}