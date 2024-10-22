<?php

namespace App\Http\Resources\Api\V1;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Models\FamilyLevel;
use App\Models\FamilyUser;
use App\Models\GiftLog;
use App\Models\Pack;
use App\Models\Room;
use App\Models\UserSetting;
use App\Models\Ware;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MyDataResource extends JsonResource
{
    public function toArray($request)
    {
        $family = DB::table('families')->where('id', @$this->family_id)->first();
        $f = null;

        if ($family) {
            $fu = FamilyUser::where('family_id', $family->id)->where('status', 1)->count();
            $giftLogs = GiftLog::where(function ($q) {
                $q->where('receiver_family_id', $this->id)->orWhere('sender_family_id', $this->id);
            })->sum('giftPrice');

            $cur_level = FamilyLevel::where('exp', '<=', $giftLogs)->orderByDesc('exp')->first();
            $next_level = FamilyLevel::where('exp', '>', $giftLogs)->orderBy('exp')->first();

            $min_exp = @$cur_level->exp ?: 0;
            $over = $giftLogs - $min_exp;
            $diff = @$next_level->exp - @$cur_level->exp;
            $lev = [
                'level_exp' => (integer)$cur_level->exp ?: 0,
                'level_name' => @$cur_level->name ?: '',
                'level_img' => @$cur_level->img ?: '',
                'family_exp' => (integer)$giftLogs,
                'over_current_level_exp' => (integer)$over,
                'next_exp' => (integer)$next_level->exp,
                'next_name' => @$next_level->name,
                'next_img' => @$next_level->img,
                'per' => $diff == 0 ? 0 : (double)($over / $diff),
                'rem' => (integer)($diff - $over)
            ];

            $f = [
                'owner_id' => $family->user_id,
                'family_name' => $family->name,
                'max_num' => $family->num,
                'img' => $family->image,
                'members_num' => $fu,
                'level' => $lev
            ];
        }

        $user_id = $this->id;

        $pack = $this->packs
            ->whereIn('type', [18, 21, 17, 20, 19, 13, 16, 9, 11, 14, 15])
            ->where('is_used', 1);

        Pack::where('expire', '!=', 0)->where('expire', '<', time())->delete();

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
        $now_room = Room::where('uid', $this->now_room_uid)->first();
        if ($now_room && $now_room->room_pass) {
            $pass_status = true;
        }

        $admin = $this->agencyUserJob;
        $owner = $this->ownAgency;

        $dress_1_data = Common::getUserDress($this->id, $this->dress_1, 4, 'img2', true);
        $dress_1_fallback = Common::getUserDress($this->id, $this->dress_1, 4, 'img1', true);
        $frame = $dress_1_data ?: $dress_1_fallback;

        $bubble = Common::getUserDress($this->id, $this->dress_2, 5, 'show_img', true);

        $dress_3_data = Common::getUserDress($this->id, $this->dress_3, 6, 'img2', true);
        $dress_3_fallback = Common::getUserDress($this->id, $this->dress_3, 6, 'img1', true);
        $intro = $dress_3_data ?: $dress_3_fallback;

        $TypeIntro = Ware::where('id', $this->dress_3)->first();

        $isHideCountry = Common::hasInPack($this->id, 13, true);

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

        $data = [
            'id' => @$this->id,
            'notification_id' => @$this->notification_id ?: "",
            'name' => @$this->name ?: 'user' . ' ' . '#' . @$this->uuid,
            'phone' => (string)@$this->phone ?: '',
            'frame' => $frame,
            'intro' => $intro,
            'intro_type' => @$TypeIntro?->image_type ?? '',
            'bubble' => $bubble,
            'bubble_id' => @$bubble ? $this->dress_2 : 0,
            'frame_id' => $frame ? @$this->dress_1 : 0,
            'intro_id' => $intro ? @$this->dress_3 : 0,
            'is_first' => (bool)$this->is_points_first,
            'is_agency_request' => (bool)$this->agencyJoinRequest->where('status', '!=', 2)->count(),
            'has_room' => $this->hasRoom(),
            'google_bind' => (bool)@$this->google_id,
            'room' => [
                "id" => @$this->ownerRoom->id,
                "room_name" => @$this->ownerRoom->room_name,
                "room_cover" => @$this->ownerRoom->room_cover,
                "room_background" => @$this->ownerRoom->final_room_image,
                "mode" => @$this->ownerRoom->mode,

            ],
            'phone_bind' => (bool)@$this->phone,
            'vip' => Common::ovip_center($this),
            'family_id' => @$this->family_id,
            'uuid' => @$this->uuid,
            'bio' => @$this->bio ?: '',
            'number_of_fans' => $this->followers_ids()->count(),
            'number_of_followings' => $this->followeds_ids()->count(),
            'number_of_friends' => $this->numberOfFriends(),
            'profile_visitors' => $this->profileVisits()->count(),
            'profile' => new ProfileResource(@$this->profile),
            'level' => Common::level_center(@$this->id),
            'charge_level' => Common::chargeLevel(@$this->id),
            'game_Available' => (bool)UserHandling::chickLevelToPlay($this->resource),
            'my_store' => [
                'id' => $this->id,
                'coins' => $this->di,
                'diamonds' => $this->monthly_diamond_received,
                'silver_coins' => $this->gold,
                'usd' => (double)$this->sallary,
            ],
            'family_data' => $f,
            'agency' => $agency_joined,
            'Last_seen' => @$time_log->time ?? 0,
            'type_user' => intval(@$this->type_user) ?: 0,
            'user_jobs' => $this->jobs,
            'has_color_name' => $pack->where('type', 18)->count() >= 1,
            'anonymous' => $pack->where('type', 17)->count() >= 1,
            'country' => $this->country,
            'country_hidden' => $isHideCountry,
            'gender' => @$this->gender == 1 ? "custom_image/male.png" : "custom_image/female.png",
            "change_room_effect" => new ShowUserSettingResource(@$show_user_setting),
            'user_agency_status' => $owner ? 2 : ($admin ? 1 : 3),
            'achievement_images' => $achievement_images,
            "multi_images" => $this->images?->select("img"),
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

}
