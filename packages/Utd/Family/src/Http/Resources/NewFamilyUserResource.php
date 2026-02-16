<?php

namespace Utd\Family\Http\Resources;

use Carbon\Carbon;
use Utd\Family\Entities\Family;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class NewFamilyUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $mangerTypeResource = family_resource('manger_type');
        return [
            'id'    => $this->id,
            'user'  => [

                'id'        => @$this->user->id ?? 0,
                'uuid'        => (string)(@$this->user->uuid ?? 0),
                'name'      => @$this->user->name,
                'id_image'             => @$this->user->specialId?->ware?->show_img ?? '',
                'special_id'          =>  @$this->user->specialId?->ware?->id ?? 0,
                'profile'   => [
                    'image'     => @$this->user->profile->avatar?:'',
                    'gender'    => @$this->user->profile->gender ?? 0,
                    'age'       => @Carbon::parse ($this->user?->profile?->birthday)->age ?? 0,
                    'country'   => @$this->user->country?:'',
                    // 'sender_img' => $this->user->getImageReceiverOrSender('sender_id',2)->img,

                ],

                'vip'=>family_helper('common')::ovip_center ($this), // both
                'level'=> $this->user ? family_helper('common')::level_center (@$this->user) : new stdClass(), // both
                'frame'     => family_helper('common')::getUserDress(@$this->user?->id,@$this->user?->dress_1,4,'img2', true)?:family_helper('common')::getUserDress(@$this->user?->id,@$this->user?->dress_1,4,'img1', true),
                'frame_id'  => @$this->dress_1,
                'type_user'            => intval(@$this->user->type_user) ?: 0, // both
                'manger_type'          => $mangerTypeResource ? new $mangerTypeResource(@$this->user->mangerType) : null
            ],
            'time'  =>\Carbon\Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
