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
            'level'=>Common::level_centerSerch (@$this), // both     ---- resever img   , sendr img  req
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
        return $data;
    }

}
