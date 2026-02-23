<?php

namespace Utd\Family\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShortFamilyUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        if (! @$this->id) {
            return;
        }
        $mangerTypeResource = family_resource('manger_type');

        $data = [
            'id' => @$this->user?->id,
            // 'is_family_admin'=>@$this->user_type == 1 ? true : false,
            'is_family_admin' => @$this->user?->is_family_admin,
            'name' => $this->user?->name,
            //            'profile'=>new ProfileResource(@$this->profile),
            'profile' => [
                'image' => $this->user?->profile?->avatar,
            ],

            'family_status' => @$this->user_type,
            'type_user' => (int) (@$this->user?->type_user) ?: 0, // both
            'manger_type' => $mangerTypeResource ? new $mangerTypeResource(@$this->user?->mangerType) : null,
        ];

        return $data;
    }

    public function handelStatics($request)
    {
        $user = $request->user();
        $visitor = 0;
        $fans = 0;
        $friends = 0;
        $income = 0;
        $frame = 0;
        $enteirs = 0;
        $bubble = 0;
        if ($request->visitor !== null) {
            $visitor = (int) $user->profileVisits()->count() - (int) $request->visitor;
        }
        if ($request->fans !== null) {
            $fans = (int) $user->numberOfFans() - (int) $request->fans;
        }
        if ($request->friends !== null) {
            $friends = (int) $user->numberOfFriends() - (int) $request->friends;
        }
        if ($request->income !== null) {
            $income = (int) $user->coins - (int) $request->income;
        }
        if ($request->frame !== null) {
            $frame = (int) $user->frames_count() - (int) $request->frame;
        }
        if ($request->enteirs !== null) {
            $enteirs = (int) $user->intros_count() - (int) $request->enteirs;
        }
        if ($request->bubble !== null) {
            $bubble = (int) $user->bubble_count() - (int) $request->bubble;
        }

        return [
            'visitor' => $visitor,
            'fans' => $fans,
            'friends' => $friends,
            'income' => $income,
            'frame' => $frame,
            'enteirs' => $enteirs,
            'bubble' => $bubble,
        ];
    }
}
