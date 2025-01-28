<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pk;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\FamilyUser;
use App\Models\FamilyLevel;
use App\Models\UserSetting;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class MyDataResource extends JsonResource
{
    public function toArray($request)
    {

        $family = $this->family;
        $f = null;

        if ($family) {

            $f = [
                'owner_id' => $family->user_id,
                'family_name' => $family->name,
                'max_num' => $family->num,
                'img' => $family->image,
                'members_num' => $family->members_count,
                'level' => $family->level,
            ];
        }

        $user_id = $this->id;

        // $pack = $this->packs
        //     ->whereIn('type', [18, 21, 17, 20, 19, 13, 16, 9, 11, 14, 15])
        //     ->where('is_used', 1);

        $time_log = $this->timeLog()->latest()->first();


        $agency_joined = $this->agency;
        if ($agency_joined) {
            $owner = $agency_joined->app_owner_id == $this->id ? new \stdClass() : new MiniUserResource($agency_joined->owner);
            $agency_joined = [
                'id' => $agency_joined->id,
                'name' => $agency_joined->name,
                'status' => $agency_joined->status,
                'owner' => $owner,
            ];
        }

        $pass_status = false;
        $now_room = @$this->room;
        if ($now_room && $now_room->room_pass) {
            $pass_status = true;
        }

        $admin = $this->agencyUserJob;
        $owner = $this->ownAgency;

        $dress_1_data = $this->getUserDress(4, $this->dress_1, 'img2');
        // Common::getUserDress($this->id, $this->dress_1, 4, 'img2', true);
        $dress_1_fallback = $this->getUserDress(4, $this->dress_1, 'img1');
        // Common::getUserDress($this->id, $this->dress_1, 4, 'img1', true);
        $frame = $dress_1_data ?: $dress_1_fallback;

        $bubble = $this->getUserDress(5, $this->dress_2, 'show_img');
        // Common::getUserDress($this->id, $this->dress_2, 5, 'show_img', true);

        $dress_3_data = $this->getUserDress(6, $this->dress_3, 'img2');
        // Common::getUserDress($this->id, $this->dress_3, 6, 'img2', true);
        $dress_3_fallback = $this->getUserDress(6, $this->dress_3, 'img1');

        // Common::getUserDress($this->id, $this->dress_3, 6, 'img1', true);
        $intro = $dress_3_data ?: $dress_3_fallback;

        $isHideCountry = $this->getPackWithType(13);

        $show_user_setting = $this->userSetting;
        if ($show_user_setting == null) {
            $show_user_setting = UserSetting::firstOrCreate(['user_id' => $this->id], [
                'show_git' => 1,
                'show_intro' => 1,
                'show_banner' => 1,
            ]);
        }

        $achievement_images = [];
        if ($this->medals) {
            foreach ($this->medals as $medal) {
                if ($medal->achievementLevel) {
                    $achievement_images[] = $medal->achievementLevel->valid_image;
                }
            }
        }
        $counters = [];
        if ($request->show_counter == true) {
            $userCounterServices = new \Modules\Public\Http\Services\UserCounterServices();
            $user = User::find($this->id);
            $types = ['system_message', 'official_message', 'followers', 'followeds', 'friend', 'visitor', 'mybag', 'mall'];

            $counters = collect($types)->mapWithKeys(function ($item) use ($userCounterServices, $user) {
                return [$item => $userCounterServices->getUserCounts($user, $item)];
            });
            $counters['message'] = $userCounterServices->getCountByType($user, 'message');
        }


        $ownerRoom = $this->ownerRoom;
        $pks = !is_null($ownerRoom?->id) ? $this->getRoomTwoLastPk($ownerRoom->id) : null;
        /**@var User $this
         * @var Room $ownerRoom*/
        $data = [
            'id' => @$this->id,
            'notification_id' => @$this->notification_id ?: "",
            'name' => @$this->name ?: 'user' . ' ' . '#' . @$this->uuid,
            'phone' => (string)@$this->phone ?: '',
            'frame' => $frame,
            'intro' => $intro,
            'intro_type' => @$this->dress3?->image_type ?? '',
            'bubble' => $bubble,
            'bubble_id' => @$bubble ? $this->dress_2 : 0,
            'frame_id' => $frame ? @$this->dress_1 : 0,
            'intro_id' => $intro ? @$this->dress_3 : 0,
            'is_first' => (bool)$this->is_points_first,
            'is_agency_request' => (bool)$this->agencyJoinRequest->where('status', '!=', 2)->count(),
            'has_room' => $this->hasRoom(),
            'google_bind' => (bool)@$this->google_id,
            'room' => [
                "id" => @$ownerRoom->id ?? 0,
                "owner_uuid" => @$this->uuid,
                "room_name" => @$ownerRoom->room_name ?? '',
                "room_cover" => @$ownerRoom->room_cover ?? '',
                "room_background" => @$ownerRoom->final_room_image ?? '',
                "mode" => @$ownerRoom->mode ?? 0,
                'giftPrice' => @$ownerRoom->session_string ?? 0,
                "is_pk"               => (@$pks[0]) && @$pks[0]->end_at >= now() ? @$pks[0]->status : 0,
                "show_pk"             => @$ownerRoom->is_show_pk ?? 0,
                'password_status'     => !(@$ownerRoom->room_pass == ""),

            ],
            'phone_bind' => (bool)@$this->phone,
            'vip' => Common::ovip_center($this),

            'family_id' => @$this->family_id,
            'uuid' => @$this->uuid,
            'special_color'    => @$this->color_id ?? '',
            'bio' => @$this->bio ?: '',
            'number_of_fans' => $this->followerss()->count(),
            'number_of_followings' => $this->following()->count(),
            'number_of_friends' => $this->friends()->count(),
            'profile_visitors' => $this->profileVisits()->count(),
            'profile' => new ProfileResource(@$this->profile),
            'level' => Common::level_center(@$this),
            'charge_level' => Common::chargeLevel(@$this->id),
            'game_available' => (bool)UserHandling::chickLevelToPlay($this->resource),
            $this->merge((new MyStoreResource($this->resource))),
            'family_data' => $f,
            'agency' => $agency_joined,
            'Last_seen' => @$time_log->time ?? 0,
            'type_user' => intval(@$this->type_user) ?: 0,
            'user_jobs' => $this->jobs,
            ///  'has_color_name' => $this->packs->where('type', 18)->count() >= 1,
            'has_color_name'       => Common::hasInPack($this->id, 18, true),
            'anonymous' => $this->packs->where('type', 17)->count() >= 1,
            'country' => $this->country ?? (object) [],
            'country_name' => $this->country ? (app()->getLocale() == 'en' ? $this->country->e_name : $this->country->name) : '',
            'country_hidden' => $isHideCountry,
            'gender' => @$this->gender == 1 ? "custom_image/male.png" : "custom_image/female.png",
            "change_room_effect" => new ShowUserSettingResource(@$show_user_setting),
            'user_agency_status' => $owner ? 2 : ($admin ? 1 : 3),
            'achievement_images' => $achievement_images,
            "multi_images" => $this->images?->select("img"),
            "family_price" =>  Common::getConfig('family_price') ?? 0,
            'image_color'          => @$this->color_image,
            $this->mergeWhen($request->show_counter == true, [
                'unread_counter'       =>  $counters,
            ]),
            'profile_frame' =>common::wareUserVip($this->id, 28, 'img2'),
            'profile_frame_id' =>common::wareUserVip($this->id, 28, 'id'),
            'company_number' => Common::getConfig('company_number'),
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,
            'special_id_image'          =>  @$this->specialId?->ware?->show_img ?? "",

        ];

        $data['auth_token'] = $this->auth_token;
        if (isset($this->is_mic)) {
            $data['is_mic'] = $this->is_mic;
        }
        if ($this->first_login_date) {
            $data['first_login_date'] = $this->first_login_date->format('Y-m-d H:i:s');
        }
        if ($pass_status) {
            $data['pass_status'] = $pass_status;
        }

        return $data;
    }
    private function getRoomTwoLastPk(int $roomId)
    {
        return Pk::query()
            ->where('room_id', $roomId)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();
    }

    public function getUserDress($type, $dress, $item = 'img1')
    {
        $pack = $this->packs
            ->where('type', $type)
            ->where('target_id', $dress)
            ->first();
        return $pack && $pack->ware ? $pack->ware->{$item} : '';
    }
}
