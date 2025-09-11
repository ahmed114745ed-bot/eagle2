<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ChatSettingResource;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Models\Follow;
use App\Models\User;

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
         $userHandling = new \App\Classes\UserHandling();
     
         $packsByType = $this->packs->groupBy('type');

         $getPackImage = fn($type, $target_id, $item) => 
         $packsByType->get($type)?->firstWhere('target_id', $target_id)?->ware?->{$item} ?? '';

         
         $data = [
             'id'                   => $this->id,
             'uuid'                 => $this->uuid,
             'special_color'        => $this->color_id ?? '',
             'id_image'             => $this->specialId?->ware?->show_img ?? '',
             'special_id'           => $this->specialId?->ware?->id ?? 0,
             'chat_id'              => $this->chat_id ?: '',
             'notification_id'      => $this->notification_id ?: '',
             'name'                 => $this->name ?: "user #{$this->uuid}",
             'nick_name'            => $this->nick_name,
             'number_of_fans'       => $this->number_of_fans,
             'number_of_followings' => $this->number_of_followings,
             'number_of_friends'    => $this->number_of_friends,
             'profile_visitors'     => $this->profile_visitors,
             'is_followed'          => $this->is_followed,
             'is_follow'            => $this->is_follow,
             'is_friend'            => $this->isFriends(),
             'room'                 => !$this->getPackWithType(16) ? new UserDataRoomResource($this->room) : (object)[],
             'now_room'             => $this->formatNowRoom(),
             'agency'               => $this->formatAgency(),
             'family_id'            => $this->family_id,
             'family_data'          => $this->formatFamily(),
             'profile'              => new ProfileResource($this->profile),
             'diamonds'             => $this->total_diamond_received ?: 0,
             'vip'                  => Common::ovip_center($this),
             'lang'                 => $this->lang,
             'country'              => !$this->getPackWithType(13) ? ($this->country ?? (object)[]) : (object)[],
             'have_country'         => !is_null($this->country),
             'medals'               => (object)[],
             'frame'                => $getPackImage(4, $this->dress_1, 'img2') ?: $getPackImage(4, $this->dress_1, 'img1'),
             'intro'                => $getPackImage(6, $this->dress_3, 'img2') ?: $getPackImage(6, $this->dress_3, 'img1'),
             'intro_type'           => $getPackImage(6, $this->dress_3, 'image_type'),
             'bubble'               => $getPackImage(5, $this->dress_2, 'show_img'),
             'bubble_id'            => $this->dress_2 ?: 0,
             'frame_id'             => $this->dress_1 ?: 0,
             'intro_id'             => $this->dress_3 ?: 0,
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
             'top_three_support'    => $userHandling->getTopThreeSupport($this->id),
             'level'                => Common::level_center(@$this),
             'profile_frame'        => $this->profile_frame,
             'profile_frame_id'     => $this->profile_frame_id,
             'multi_images'         => $this->images?->pluck("img"),
             'user_types'           => $this->user_types,
             'shipping_agency'      => $this->formatShippingAgency(),
             'has_anti_ban'         => $this->getPackWithType(15),
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
 
        //  $owner = $this->agency->app_owner_id == $this->id
        //      ? new \stdClass()
        //      : new MiniUserResource($this->agency->owner);
 
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
     
         return new NowRoomResource($this) ?? (object)[];
     }
     
 
     private function formatShippingAgency()
     {
         if (!$this->shippingAgency) return null;
 
         return [
             "id"                   => $this->shippingAgency->id,
             "name"                 => $this->shippingAgency->name ?? '',
             "image"                => $this->shippingAgency->img ?? '',
             "complete-transactions"=>  0,
         ];
     }
 


     public function getUserDress($type, $dress, $item = 'img1')
     {
         $pack = $this->packsByType->get($type)?->firstWhere('target_id', $dress);
     
         return $pack?->ware?->{$item} ?? '';
     }
     

}
