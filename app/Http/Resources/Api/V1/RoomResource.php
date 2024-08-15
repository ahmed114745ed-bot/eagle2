<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Http\Resources\CountryResource;
use App\Models\BoxUse;
use App\Models\Pk;
use App\Models\User;
use App\Models\Request;
use App\Models\RequestBackgroundImage;
use App\Repositories\User\UserRepo;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request)
    {
        // Common::setHourHot($this->uid);
        $pk = $this->lastPk;
        
        $have_luck_box = $this->boxUse;
        $data = [
            'id' => $this->id,
            'owner_id' => $this->uid ?: 0,
            'room_id' => $this->numid ?: 0,
            'name' => $this->room_name ?: '',
            'visitors_count' => $this->count_room_socket,
            'cover' => $this->room_cover ?: '',
            'class' => $this->myClass ?: new \stdClass(),
            'type' => $this->myType ?: new \stdClass(),
            'is_hot' => $this->hot ?: 0,
            'is_popular' => $this->is_popular ?: 0,
            'room_status' => $this->room_status,
            'password_status' => (bool) $this->room_pass,
            'room_intro' => $this->room_intro ?: '',
            'max_admin' => $this->max_admin ?: '',
            'is_recommended' => $this->is_recommended ?: 0,
            'lang' => $this->lang ?: '',
            'is_pk' => (bool) $pk,
            'country' => $this->country 
                ? new CountryResource($this->country) 
                : [
                    'id' => 0,
                    'name' => '',
                    'flag' => '',
                    'lang' => '',
                    'phone_code' => ''
                ],
            'have_luck_box' => (bool) $have_luck_box,
            'distance' => $this->distance,
        ];
        
        if ($request['show']) {
            $requestBackground = $this->backgroundImage;

                
            $data = array_merge($data, [
                'room_users' => Common::get_room_users($this->owner()?->id, $request->user()->id),
                'background' => $requestBackground?->img ?: $this->room_background,
                'mics' => $this->microphone ? explode(',', $this->microphone) : [],
                'is_mics_free' => $this->free_mic ?: 0,
                'owner' => $this->owner(),
                'admins' => $this->admins(),
                'admins_ids' => $this->admins()->pluck('id'),
                'speak_ban_list' => $this->banList(),
                'muted_list' => $this->muteList(),
                'black_list' => $this->blackList(),
                'created_at' => $this->created_at,
            ]);
        }
        return $data;
    }


    protected function owner(){
        return new UserResource(User::query ()->find ($this->uid));
    }

    protected function admins(){
        $ids = explode (',',$this->room_admin);
        $ids = $this->removeOwner ($ids);
        return UserResource::collection (User::query ()->whereIn ('id',$ids)->get ());
    }


    protected function visitors(){
        $ids = explode (',',$this->room_visitor);
        $ids = $this->removeOwner ($ids);
        return UserResource::collection (User::query ()->whereIn ('id',$ids)->get ());
    }

    protected function blackList(){
        $ids = explode (',',$this->room_black);
        return UserResource::collection (User::query ()->whereIn ('id',$ids)->get ());
    }

    protected function banList(){
        $ids = explode (',',$this->room_speak);
        return UserResource::collection (User::query ()->whereIn ('id',$ids)->get ());
    }

    protected function muteList(){
        $ids = explode (',',$this->room_sound);
        return UserResource::collection (User::query ()->whereIn ('id',$ids)->get ());
    }

    protected function removeOwner($ids){
        if (($key = array_search($this->uid, $ids)) !== false) {
            unset($ids[$key]);
        }
        return $ids;
    }
}
