<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Helpers\UserPackHelper;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

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
        $packsByType = $this->packs->groupBy('type');
        if ($this->relationLoaded('chatRoomsAsUser') || $this->relationLoaded('chatRoomsAsUser2')) {
            $chatRoom = $this->chatRoomsAsUser->first() ?? $this->chatRoomsAsUser2->first();
            $data['chat_id'] = $chatRoom?->id;
            $data['unread_messages_count'] = $chatRoom?->unread_messages ?? 0;
        }

        $data = [
            'id'                   => $this->id,
            'uuid'                 => $this->uuid,
            'special_color'        => $this->color_id ?? '',
            'id_image'             => $this->specialId?->ware?->show_img ?? '',
            'special_id'           => $this->specialId?->ware?->id ?? 0,
            'notification_id'      => $this->notification_id ?: '',
            'name'                 => $this->name ?: "user #{$this->uuid}",
            'deleted_at'           => $this->deleted_at,
            'nick_name'            => $this->nick_name,
            'number_of_fans'       => $this->number_of_fans,
            'number_of_followings' => $this->number_of_followings,
            'number_of_friends'    => $this->number_of_friends,
            'profile_visitors'     => $this->profile_visitors,
            'is_followed'          => $this->is_followed,
            'is_follow'            => $this->is_follow,
            'is_friend'            => $this->isFriends(),
            'room'                 => ($this->room && ! ((bool) $packsByType->get(16)?->firstWhere('is_used', 1))) ? new UserDataRoomResource($this->room) : (object)[],
            'now_room'             => $this->formatNowRoom(),
            'agency'               => $this->formatAgency(),
            'family_id'            => $this->family_id,
            'family_data'          => $this->formatFamily(),
            'profile'              => new ProfileResource($this->profile),
            'diamonds'             => $this->total_diamond_received ?: 0,
            'vip' =>  [
                'img_old'     => $this->UserVip?->OVip->img ?? '',
                'vip_img'      => UserPackHelper::getVipIcon($this->resource),
                'colored_name' => UserPackHelper::getColorName($this->resource),
            ],
            'lang'                 => $this->lang,
            'country'              => ! (bool) $packsByType->get(13)?->firstWhere('is_used', 1) ? ($this->country ?? (object)[]) : (object)[],
            'have_country'         => !is_null($this->country),
            'medals'               => (object)[],
            'frame' => UserPackHelper::getFrameImage($this->resource),
            'frame_id' => UserPackHelper::getFrameId($this->resource),
            'intro' => UserPackHelper::getIntroFile($this->resource),
            'intro_type' => UserPackHelper::getIntroType($this->resource),
            'intro_id' => UserPackHelper::getIntroId($this->resource),
            'bubble' => UserPackHelper::getBubbleImage($this->resource),
            'bubble_id' => UserPackHelper::getBubbleId($this->resource),
            'bio'                  => $this->bio ?: '',
            'is_agent'             => $this->is_agent,
            'is_gold_id'           => (bool) $this->color_image,
            'image_color'          => $this->color_image,
            'my_agency'            => [],
            'online_time'          => !$this->getPackWithType(20) ? $this->formatOnlineTime() : '',
            'has_color_name'       => $this->getPackWithType(18),
            'anonymous'            => $this->getPackWithType(17),
            'country_hidden'       => $this->getPackWithType(13),
            'last_active_hidden'   => $this->getPackWithType(19),
            'visit_hidden'         => $this->getPackWithType(19),
            'room_hidden'          => $this->getPackWithType(16),
            'type_user'            => intval($this->type_user) ?: 0,
            'change_room_effect'   => new ShowUserSettingResource($this->userDataSetting),
            'chat_setting'         => new ChatSettingResource($this->chatSetting),
            'manger_type'          => new MangerTypeResource($this->manager),
            'top_three_support'    => [],
            'level' => [
                'receiver_img' => $this->receiverLevel?->img ?? '',
                'sender_img'   => $this->senderLevel?->img  ?? '',
            ],
            'profile_frame'        => $this->profile_frame,
            'profile_frame_id'     => $this->getProfileFrame()?->id ?? '',
            'multi_images'         => MultiImageUserResource::collection($this->images),
            'user_types'           => $this->user_types,
            'shipping_agency'      => $this->formatShippingAgency(),
            'has_anti_ban'         => $this->getPackWithType(15),

            'avatar' => [
                'original' => $this->profile->avatar ?? '',
                'thumbnail' =>  $this->profile->avatar_thumb ?? Common::getImageUrl($this->avatar, 'thumbnail'),
                'medium' => $this->profile->avatar_medium ?? Common::getImageUrl($this->avatar, 'medium'),
                'large' => $this->profile->avatar_large ?? Common::getImageUrl($this->avatar, 'large'),
            ],

        ];
        if (in_array($this->is_mic, ['0', '1'])) {
            $data['is_mic'] = $this->is_mic;
        }

        if ($this->pivot) {
            $data['visit_time'] = $this->pivot->updated_at;
        }

        return $data;
    }

    private function formatAgency()
    {
        if (!$this->agency) return null;
        return [
            'id'           => $this->agency->id,
            'name'         => $this->agency->name,
            'status'       => $this->agency->status,
            'image'        => $this->agency->img,
            'member_count' =>  0,
            'owner'        => [],
        ];
    }

    private function formatFamily()
    {
        if (!$this->family) return null;

        return [
            'owner_id'       => $this->family->user_id,
            'family_name'    => $this->family->name,
            'img'            => $this->family->image,
            'num_of_members' =>  0,
        ];
    }

    private function formatOnlineTime()
    {
        if (!$this->online_time) return '';
        if ($this->online) return __('online');

        $onlineTime = Carbon::createFromTimestamp($this->online_time);

        return $onlineTime->isPast()
            ? $onlineTime->diffForHumans(now())
            : 'In the future';
    }

    private function formatNowRoom()
    {
        if (!$this->now_room_uid) return (object)[];

        $nowRoomOwner = $this->nowRoomOwner;

        if (!$nowRoomOwner) return (object)[];

        if ($nowRoomOwner->getPackWithTypeV3(16)) return (object)[];

        $resource = (new NowRoomResource($this))->toArray(request());

        return empty($resource) ? (object)[] : $resource;
    }


    private function formatShippingAgency()
    {
        if (!$this->shippingAgency) return null;

        return [
            "id"                   => $this->shippingAgency->id,
            "name"                 => $this->shippingAgency->name ?? '',
            "image"                => $this->shippingAgency->img ?? '',
            "complete-transactions" =>  0,
        ];
    }



    public function getUserDress($type, $dress, $item = 'img1')
    {
        $pack = $this->packsByType->get($type)?->firstWhere('target_id', $dress);

        return $pack?->ware?->{$item} ?? '';
    }


    public function ovip_center_user_data_v2()
    {
        $packsByType = $this->packs->groupBy('type');

        $getPackImage = fn($type, $item) =>
        $packsByType->get($type)?->firstWhere('is_used', 1)?->ware?->{$item} ?? '';

        $uvip = $this->UserVip;
        $vip  = $uvip?->OVip;

        if (!$vip) {
            return new \stdClass();
        }

        $cacheKey = "ware_icon_{$vip->level}_10";
        $vipIcon  = Common::getCachedWares($cacheKey, $vip, 10);

        $hasColor = $packsByType->get(18)?->firstWhere('is_used', 1);

        return [
            'vip_img'      => $vipIcon->show_img ?? $vip->img ?? $vip->image ?? '',
            'colored_name' => $hasColor
                ? $getPackImage(18, 'color')
                : '',
        ];
    }
}
