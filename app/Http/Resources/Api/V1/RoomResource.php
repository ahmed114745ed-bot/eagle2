<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Http\Resources\CountryResource;
use App\Models\Room;
use App\Models\User;
use App\Models\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request)
    {
        // Common::setHourHot($this->uid);
        $pk = $this->lastPk;
        $achievement_images = [];
        if ($this->owner->medals) {
            foreach ($this->owner->medals as $medal) {
                if ($medal->achievementLevel && $medal->achievementLevel->achievement && $medal->achievementLevel->achievement->type?->value == 'room_target') {
                    $achievement_images[] = $medal->achievementLevel->valid_image;
                }
            }
        }
        $isParty = $this->roomCategory && $this->roomCategory->type === 'party';
        $have_luck_box = $this->boxUse;
        /**@var Room $this*/
        $data = [
            'id' => $this->id,
            'owner_id' => $this->uid ?: 0,
            'owner_uuid' => $this->owner?->uuid ?: 0,
            'room_id' => (string)($this->id ?: 0),
            'name' => $this->room_name ?: '',
            "mode" => $this->mode,
            'visitors_count' => $this->count_room_socket,
            'cover' => $this->room_cover ?: '',
            'class' => $this->myClass ?: new \stdClass(),
            'type' => $this->myType ?: new \stdClass(),
            'is_hot' => $this->hot ?: 0,
            'session' => $this->session_string,
            'giftPrice' => $this->session_string,
            'is_popular' => $this->is_popular ?: 0,
            'room_status' => $this->room_status,
            'password_status' => (bool) $this->room_pass,
            'room_intro' => $this->room_intro ?: '',
            'max_admin' => $this->max_admin ?: '',
            'is_recommended' => $this->is_recommended ?: 0,
            'lang' => $this->lang ?: '',
            'is_pk' => (bool) $pk,
            'is_party' => $isParty,
            'room_background' => $this->final_room_image,
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
           // 'achievement_images' => $achievement_images,
            //'medals'               => @$this->owner?->medals()?->where('is_enable', true)->get() ?? [],
            $this->mergeWhen($this->distance, [
                'distance' => $this->distance,
            ]),
            $this->mergeWhen($this->relationLoaded('game'), [
                'game' => $this->mode == 4 && $this->game ? new \App\Http\Resources\AllGameResource($this->game) : new \stdClass(),
            ]),
            $this->mergeWhen($this->relationLoaded('roomVisitorUsers'), [
                'visitors_images' => $this->getVisitorsImages(),
            ]),
        ];

        if ($request['show']) {


            $data = array_merge($data, [
                'room_users' => Common::get_room_users($this->owner()?->id, $request->user()->id),
                'background' => $this->final_room_image ?: $this->room_background,
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


    protected function owner()
    {
        return new UserResource(User::query()->find($this->uid));
    }

    protected function admins()
    {
        $ids = explode(',', $this->room_admin);
        $ids = $this->removeOwner($ids);
        return UserResource::collection(User::query()->whereIn('id', $ids)->get());
    }


    protected function visitors()
    {
        $ids = explode(',', $this->room_visitor);
        $ids = $this->removeOwner($ids);
        return UserResource::collection(User::query()->whereIn('id', $ids)->get());
    }

    protected function blackList()
    {
        $ids = explode(',', $this->room_black);
        return UserResource::collection(User::query()->whereIn('id', $ids)->get());
    }

    protected function banList()
    {
        $ids = explode(',', $this->room_speak);
        return UserResource::collection(User::query()->whereIn('id', $ids)->get());
    }

    protected function muteList()
    {
        $ids = explode(',', $this->room_sound);
        return UserResource::collection(User::query()->whereIn('id', $ids)->get());
    }

    protected function removeOwner($ids)
    {
        if (($key = array_search($this->uid, $ids)) !== false) {
            unset($ids[$key]);
        }
        return $ids;
    }
}
