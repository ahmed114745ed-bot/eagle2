<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Http\Resources\CountryResource;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\Country;
use App\Models\Family;
use App\Models\FamilyUser;
use App\Models\Pack;
use App\Models\Room;
use App\Models\Ware;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\MangerTypeResource;
class UserResourceSerche extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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
//        $reqs_count = AgencyJoinRequest::query ()->where ('user_id',@$this->id)->where ('status','!=',2)->count ();

        $agency_joined = $this->agency;
        if ($agency_joined != null) {
            $owner = $agency_joined->app_owner_id == $this->id ? new \stdClass() : new MiniUserResource($agency_joined->owner);
            if ($this->agency != null) {
                $agency_joined = [
                    'id'=>$this->agency->id,
                    'name'=>$this->agency->name,
                    'status'=>$this->agency->status,
                    'owner'=>$owner,
                ];
            } else {
                $agency_joined = null;
            }
        }

        $pass_status = false;
        $now_room = Room::query ()->where ('uid',$this->now_room_uid)->first ();
        if ($now_room){
            if($now_room->room_pass){
                $pass_status = true;
            }
        }
        $packSpecial = Pack::query()->where(function ($query) {
            $query->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->where('user_id', $this->id)->where("type", 25)->where('is_used', 1)->first();

        $data = [
            'id'=>@$this->id, // both
            'uuid'=>@$this->uuid, // both
            'is_gold_id'=>(bool) @$this->is_gold_id, // both
            'name'=>@$this->name?:'', // both
            'is_follow' => $this->is_follow,
            'now_room'=>[
                'is_in_room'=>@$this->now_room_uid != 0,
                'uid'=>@(integer)$this->now_room_uid,
                'is_mine'=>@$this->id == $this->now_room_uid,
                'password_status'=>$pass_status
            ], // user data

            'profile'=>new ProfileResourceSerche(@$this->profile), // both       ------- img type   oge    contry   reqouerd
            'level'=>Common::level_centerSerch (@$this->id), // both     ---- resever img   , sendr img  req
            'vip'=>@Common::ovip_center ($this->id), // both
            // 'frame'=>Common::getUserDress($this->id,$this->dress_1,4,'img2')?:Common::getUserDress($this->id,$this->dress_1,4,'img1'), // both
            // 'frame_id'=>@$this->dress_1, // both
            'is_agent'=>$this->is_agent, // both
            'has_color_name'=>Common::hasInPack ($this->id,18, true), // both
            'country_hidden'=>Common::hasInPack ($this->id,13,true), // both
            'type_user'            => intval(@$this->type_user) ?: 0, // both
            "manger_type"          =>new MangerTypeResource(@$this->mangerType),
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,
        ];


        // $data['auth_token'] = $this->auth_token;
        // if (@$this->is_mic == '0' || @$this->is_mic == '1'){
        //     $data['is_mic'] = $this->is_mic;
        // }
        // if ($this->pivot){
        //     $data['visit_time']=$this->pivot->updated_at;
        // }
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
