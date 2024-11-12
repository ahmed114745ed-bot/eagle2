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
        $now_room = $this->room;
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
            'is_in_live'=>$this->is_in_live(), // user data
            'is_follow' => $this->is_follow,
            'now_room'=>[
                'is_in_room'=>@$this->now_room_uid != 0,
                'uid'=>@(integer)$this->now_room_uid,
                'is_mine'=>@$this->id == $this->now_room_uid,
                'password_status'=>$pass_status
            ], // user data

            'profile'=>new ProfileResourceSerche(@$this->profile), // both       ------- img type   oge    contry   reqouerd
            'level'=>Common::level_centerSerch (@$this->id), // both     ---- resever img   , sendr img  req
            'vip'=>@Common::ovip_center ($this), // both
            'is_agent'=>$this->is_agent, // both
            'has_color_name'=>$this->getPackWithType(18), // both
            'country_hidden'=>$this->getPackWithType(13), // both
            'type_user'            => intval(@$this->type_user) ?: 0, // both
            "manger_type"          =>new MangerTypeResource(@$this->mangerType),
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'special_id'          =>  @$this->specialId?->ware?->id ?? 0,
        ];
        return $data;
    }

}
