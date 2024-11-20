<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ChatSettingResource;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Models\Follow;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $agency_joined = $this->agency;
        if ($agency_joined != null) {
            $owner =
                $agency_joined->app_owner_id == $this->id ? new \stdClass() : new MiniUserResource($agency_joined->owner);
            if ($this->agency != null) {
                $agency_joined = [
                    'id'     => $this->agency->id,
                    'name'   => $this->agency->name,
                    'status' => $this->agency->status,
                    'owner'  => $owner,
                ];
            } else {
                $agency_joined = null;
            }
        }

        $pass_status = false;
        $now_room    = @$this->room;
        if ($now_room) {
            if ($now_room->room_pass) {
                $pass_status = true;
            }
        }

        $f      = null;
        $family = @$this->family;
        if ($family) {
            $f = [
                'owner_id'    => $family->user_id,
                'family_name' => $family->name,
                'img'         => $family->image,
            ];
        }
        // if ($request->user()) {
        //     $fArr = $request->user()->friends_ids()->toArray();
        // } else {
        //     $fArr = [];
        // }

        $onlineTime      = Carbon::createFromTimestamp($this->online_time);
        $currentDateTime = Carbon::now();

        if ($onlineTime->isPast()) {
            $timeDifferenceFormatted = $onlineTime->diffForHumans($currentDateTime);
        } else {
            $timeDifferenceFormatted = "In the future";
        }

        $frame  = $this->getUserDress(4, $this->dress_1, 'img2') ?: $this->getUserDress(4, $this->dress_1, 'img1');
        // Common::getUserDress($this->id, $this->dress_1, 4, 'img2', true) ?: Common::getUserDress($this->id, $this->dress_1, 4, 'img1', true);
        $bubble = $this->getUserDress(5, $this->dress_2, 'show_img');
        //  Common::getUserDress($this->id, $this->dress_2, 5, 'show_img', true);
        $intro  = $this->getUserDress(6, $this->dress_3, 'img2') ?: $this->getUserDress(6, $this->dress_3, 'img1');
        //  Common::getUserDress($this->id, $this->dress_3, 6, 'img2', true) ?: Common::getUserDress($this->id, $this->dress_3, 6, 'img1', true);

        $isHideCountry = $this->getPackWithType(13);
        $userHandling = new \App\Classes\UserHandling();
        $color_image = @$this->color_image;
        $chat_setting = \App\Models\ChatSetting::where("user_id", $this->id)->first();
        if ($chat_setting == null) {
            $chat_setting = \App\Models\ChatSetting::create([
                'user_id'     => $this->id,
                'chat_with_friends'     =>  1,
                'chat_with_all'         =>  0,
            ]);
        }

        $show_user_setting = \App\Models\UserSetting::where("user_id", $this->id)->first();
        if ($show_user_setting == null) {
            $show_user_setting = \App\Models\UserSetting::create([
                'user_id'     => $this->id,
                'show_git'    => 1,
                'show_intro'  => 1,
                'show_banner' => 1,
            ]);
        }

        $data      = [
            'id'      => @$this->id, // both
            'uuid'    => @$this->uuid, // both
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,
            'chat_id' => @$this->chat_id ?: "", // both                     ///////
            'notification_id'      => @$this->notification_id ?: "", // both   
            'name'                 => @$this->name ?: 'user' . ' ' . '#' . @$this->uuid, // both
            'nick_name'            => @$this->nick_name, // both                      ////
            'number_of_fans'       => $this->numberOfFans(), // both   ---
            'number_of_followings' => $this->numberOfFollowings(), // both  ---
            'number_of_friends'    => $this->numberOfFriends(), // both  ------
            'profile_visitors'     => $this->profileVisits()->count(), // both  -------
            'is_followed'            => $this->is_followed,
            'is_follow'            => $this->is_follow, // user data  ----
            'is_friend'            => $this->isFriends(),  //  -------
            'now_room'             =>  new NowRoomResource($this), // user data
            'agency'               => @$agency_joined, // both  -------
            'family_id'            => @$this->family_id, // both   ----
            'family_data'          => @$f, // refactor   --------
            'profile'              => new ProfileResource(@$this->profile),
            // both       ------- img type   oge    contry   reqouerd
            'diamonds'             => @$this->total_diamond_received ?: 0, // both  ---------------------
            'vip'                  => @Common::ovip_center($this->id), // both
            'lang'                 => @$this->lang, // both        --------------
            'country'              => !$isHideCountry ? ($this->country ?? '') : '',
            'have_country'         => ($this->country != null),
            'medals'               => $this->medals()->where('is_enable', true)->get(),
            // both    --------------
            'frame'                => $frame, // both
            'intro'                => $intro, // both
            'bubble'               => $bubble, // both
            'bubble_id'            => $bubble != '' ? $this->dress_2 : 0, // both
            'frame_id'             => $frame != '' ? @$this->dress_1 : 0, // both
            'intro_id'             => $intro != '' ? @$this->dress_3 : 0, // both
            'bio'                  => @$this->bio ?: '', // both  -------------
            'is_agent'             => $this->is_agent, // both
            'is_gold_id'           => $color_image ? true : false,
            'image_color'          => $color_image,
            'my_agency'            => $this->ownAgency()->select('id', 'name', 'notice', 'status', 'phone', 'url', 'img', 'contents')->first(),
            // both ------------
            //            'prev'=>$previliges, // my // 20 ,18, 17 , 20, 19, 16, 13
            'online_time'          => !$this->getPackWithType(20) ? ($this->online_time ? $timeDifferenceFormatted : '') : '',
            // both - calculated when get my data only     ----------
            'has_color_name'       => $this->getPackWithType(18),
            'anonymous'            => $this->getPackWithType(17),
            'country_hidden'       => $isHideCountry, // both
            'last_active_hidden'   => $this->getPackWithType(19),
            'visit_hidden'         => $this->getPackWithType(19), // both ------------
            'room_hidden'          => $this->getPackWithType(16), // both ------------
            'type_user'            => intval($this->type_user) ?: 0, // both
            'my_store'             => [
                'id'           => $this->id,
                'coins'        => $this->di,
                'diamonds'     => $this->total_diamond_received,
                'silver_coins' => $this->gold,
                'usd'          => (float)$this->sallary,
            ], // my
            "change_room_effect"   => new ShowUserSettingResource(@$show_user_setting),
            "chat_setting" => new ChatSettingResource($chat_setting),
            "manger_type"          => new MangerTypeResource(@$this->mangerType),
            "top_three_support"    => $userHandling->getTopThreeSupport($this->id),
            'level' => Common::level_center (@$this->id),

        ];

        if (@$this->is_mic == '0' || @$this->is_mic == '1') {
            $data['is_mic'] = $this->is_mic;
        }
        if ($this->pivot) {
            $data['visit_time'] = $this->pivot->updated_at;
        }
        return $data;
    }

    public function getUserDress($type, $dress, $item = 'img1')
    {

        $packs = $this->packs;
        /** @var \Illuminate\Database\Eloquent\Collection $packs */
        $pack = $packs->where('type', $type)->where('target_id', $dress)->first();
        if ($pack) {
            if ($pack->ware) {
                return $pack->ware->{$item};
            }
        }
        return '';
    }
}
