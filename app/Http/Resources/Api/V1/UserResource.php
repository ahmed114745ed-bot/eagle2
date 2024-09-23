<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Models\Family;
use App\Models\Pack;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ImageColor;
use App\Http\Resources\Api\V1\ChatSettingResource;
use App\Http\Resources\Api\V1\MangerTypeResource;

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
        //        $wapel = Pack::query ()
        //            ->where ('type',12)
        //            ->where ('expire','>=',time ())
        //            ->where ('user_id',$this->id)
        //            ->where ('use_num','>',0)
        //            ->first ();

        Pack::query()
            ->where('expire', '!=', 0)
            ->where('expire', '<', time())->delete();
        //        $reqs_count = AgencyJoinRequest::query ()->where ('user_id',@$this->id)->where ('status','!=',2)->count ();

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
        $now_room    = Room::query()->where('uid', $this->now_room_uid)->first();
        if ($now_room) {
            if ($now_room->room_pass) {
                $pass_status = true;
            }
        }

        $f      = null;
        $family = Family::query()->where('id', @$this->family_id)->first();
        if ($family) {
            $f = [
                'owner_id'    => $family->user_id,
                'family_name' => $family->name,
                'max_num'     => $family->num,
                'img'         => $family->image,
                'members_num' => $family->members_count,
                'level'       => $family->level
            ];
        }
        if ($request->user()) {
            $fArr = $request->user()->friends_ids()->toArray();
            // $ferArr = $request->user()->followers()->toArray();
        } else {
            $fArr = [];
            // $ferArr = [];
        }

        $onlineTime      = Carbon::createFromTimestamp($this->online_time);
        $currentDateTime = Carbon::now();

        if ($onlineTime->isPast()) {
            $timeDifferenceFormatted = $onlineTime->diffForHumans($currentDateTime);
        } else {
            $timeDifferenceFormatted = "In the future";
        }

        $frame  =
            Common::getUserDress($this->id, $this->dress_1, 4, 'img2', true) ?: Common::getUserDress($this->id, $this->dress_1, 4, 'img1', true);
        $bubble =
            Common::getUserDress($this->id, $this->dress_2, 5, 'show_img', true);
        $intro  =
            Common::getUserDress($this->id, $this->dress_3, 6, 'img2', true) ?: Common::getUserDress($this->id, $this->dress_3, 6, 'img1', true);

        $isHideCountry = Common::hasInPack($this->id, 13, true);

        $color_image=ImageColor::select("id","image","color")->find($this->image_color_id);

        $chat_setting=\App\Models\ChatSetting::where("user_id",$this->id)->first();
        $userHandling=new \App\Classes\UserHandling();
        if ($chat_setting == null) {
            $chat_setting=\App\Models\ChatSetting::create([
                'user_id'     => $this->id,
                'chat_with_friends'     =>  1,
                // 'chat_with_followers'   => 0,
                // 'chat_with_following'   => 0,
                'chat_with_all'         =>  0,
            ]);
        }

        $show_user_setting=\App\Models\UserSetting::where("user_id",$this->id)->first();
        if ($show_user_setting == null) {
            $show_user_setting=\App\Models\UserSetting::create([
                'user_id'     => $this->id,
                'show_git'    =>1,
                'show_intro'  =>1,
                'show_banner' =>1,
            ]);
        }

        $data      = [
            'id'      => @$this->id, // both
            'uuid'    => @$this->uuid, // both
            'uuid'                 => @$this->uuid, // both
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,

            'chat_id' => @$this->chat_id ?: "", // both                     ///////

            'notification_id'      => @$this->notification_id ?: "", // both      ///////
            // 'is_gold'=>@$this->is_gold_id, // both
            'name'                 =>@$this->name?:'user'.' '.'#'.@$this->uuid, // both
            'nick_name'            => @$this->nick_name, // both                      ////
            // 'email'=>@$this->email?:"", // both          -----
            // 'phone'=>@$this->phone?:'',// both       -------
            'number_of_fans'       => $this->numberOfFans(), // both   ---
            'number_of_followings' => $this->numberOfFollowings(), // both  ---
            'number_of_friends'    => $this->numberOfFriends(), // both  ------
            'profile_visitors'     => $this->profileVisits()->count(), // both  -------
            'is_followed'            => (@(bool)Common::IsFollow(@$request->user()->id, $this->id) == true && in_array($this->id, $fArr) == true ? true : false),
            'is_follow'            => @(bool)Common::IsFollow(@$request->user()->id, $this->id), // user data  ----
            'is_friend'            => in_array($this->id, $fArr),  //  -------
            'is_in_live'           => $this->is_in_live(), // user data
            //            'is_first'=>@(bool)$this->is_points_first, // my data
            'now_room'             => [
                'is_in_room'      => @$this->now_room_uid != 0,
                'uid'             => @(integer)$this->now_room_uid,
                'is_mine'         => @$this->id == $this->now_room_uid,
                'password_status' => $pass_status
            ], // user data
            'agency'               => @$agency_joined, // both  -------
            //            'is_agency_request'=>(bool)AgencyJoinRequest::where('user_id',@$this->id)->where ('status','!=',2)->count (), // my
            //            'is_family_admin'=>@$this->is_family_admin, //
            //            'is_family_member'=>@$this->family_id?true:false,
            'family_id'            => @$this->family_id, // both   ----
            //            'is_family_owner'=>@Family::query ()->where ('user_id',$this->id)->exists (), // refactor
            //            'family_name'=>@$fn, // refactor
            'family_data'          => @$f, // refactor   --------
            'profile'              => new ProfileResource(@$this->profile),
            // both       ------- img type   oge    contry   reqouerd
            'level'                => Common::level_center(@$this->id), // both     ---- resever img   , sendr img  req
            'diamonds'             => @$this->total_diamond_received ?: 0, // both  ---------------------
            //            'usd'=>@$this->salary, // my
            'vip'                  => @Common::ovip_center($this->id), // both
            //            'income'=>@Common::user_income ($this->id),
            //            'my_store'=>@$this->my_store, // my
            'lang'                 => @$this->lang, // both        --------------
            /*'country'              => !Common::hasInPack($this->id, 13, true) ? ($this->country ?: '') : '',*/
            'country'              => !$isHideCountry ? ($this->country ?? '') : '',
            'have_country'         => ($this->country != null),
            'medals'               => $this->medals()->where('is_enable', true)->get(),
            // both    --------------
            'frame'                => $frame, // both
            'intro'                => $intro,// both
            'bubble'               => $bubble,// both
            'bubble_id'            => $bubble != '' ? $this->dress_2 : 0, // both
            'frame_id'             => $frame != '' ? @$this->dress_1 : 0, // both
            'intro_id'             => $intro != '' ? @$this->dress_3 : 0, // both

            'bio'                  => @$this->bio ?: '', // both  -------------

            'is_agent'             => $this->is_agent, // both
            'is_gold_id'           => $color_image ? true : false,
            'image_color'          => $color_image,
            'my_agency'            => $this->ownAgency()->select('id', 'name', 'notice', 'status', 'phone', 'url', 'img', 'contents')->first(),
            // both ------------
            //            'prev'=>$previliges, // my
            'online_time'          => !Common::hasInPack($this->id, 20, true) ? ($this->online_time ? $timeDifferenceFormatted : '') : '',
            // both - calculated when get my data only     ----------
            'has_color_name'       => Common::hasInPack($this->id, 18, true), // both
            'anonymous'            => Common::hasInPack($this->id, 17, true), // both   --------
            'country_hidden'       => $isHideCountry, // both
            'last_active_hidden'   => Common::hasInPack($this->id, 20, true), // both --------
            'visit_hidden'         => Common::hasInPack($this->id, 19, true), // both ------------
            'room_hidden'          => Common::hasInPack($this->id, 16, true), // both ------------
            //            'wapel_num'=>@(integer)$wapel->use_num?:0,
            //            'salary'=>$this->salary,
            'type_user'            => intval($this->type_user) ?: 0, // both
            'my_store'             => [
                'id'           => $this->id,
                'coins'        => $this->di,
                'diamonds'     => $this->total_diamond_received,
                'silver_coins' => $this->gold,
                'usd'          => (double)$this->sallary,
            ], // my
            //            'old'=>$this->old
            "change_room_effect"   =>new ShowUserSettingResource(@$show_user_setting),
            "chat_setting"=>new ChatSettingResource($chat_setting),
            "manger_type"          =>new MangerTypeResource(@$this->mangerType),
            "top_three_support"    =>$userHandling->getTopThreeSupport($this->id)

        ];


        // $data['auth_token'] = $this->auth_token;
        if (@$this->is_mic == '0' || @$this->is_mic == '1') {
            $data['is_mic'] = $this->is_mic;
        }
        if ($this->pivot) {
            $data['visit_time'] = $this->pivot->updated_at;
        }
        return $data;
    }

    //    public function handelStatics($request){
    //        $user = $request->user();
    //        $visitor = 0;
    //        $fans = 0;
    //        $friends = 0;
    //        $income = 0;
    //        $frame = 0;
    //        $enteirs = 0;
    //        $bubble = 0;
    //        if ($request->visitor != null){
    //            $visitor = (integer)$user->profileVisits()->count() - (integer)$request->visitor;
    //        }
    //        if ($request->fans != null){
    //            $fans = (integer)$user->numberOfFans() - (integer)$request->fans;
    //        }
    //        if ($request->friends != null){
    //            $friends = (integer)$user->numberOfFriends() - (integer)$request->friends;
    //        }
    //        if ($request->income != null){
    //            $income = (integer)$user->coins - (integer)$request->income;
    //        }
    //        if ($request->frame != null){
    //            $frame = (integer)$user->frames_count() - (integer)$request->frame;
    //        }
    //        if ($request->enteirs != null){
    //            $enteirs = (integer)$user->intros_count() - (integer)$request->enteirs;
    //        }
    //        if ($request->bubble != null){
    //            $bubble = (integer)$user->bubble_count() - (integer)$request->bubble;
    //        }
    //
    //        return [
    //            'visitor' => $visitor,
    //            'fans' => $fans,
    //            'friends' => $friends,
    //            'income' => $income,
    //            'frame' => $frame,
    //            'enteirs' => $enteirs,
    //            'bubble' => $bubble
    //        ];
    //    }
}
