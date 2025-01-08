<?php

namespace App\Http\Resources\Api\V1;

use App\Models\FamilyUser;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::find($this->user_id);
        if ($user) {
            $owner = [
                'id'        => $user->id,
                'is_family_admin' => @$this->is_family_admin,
                'family_id' => (string)$user->family_id,
                'name'  => $user->name,
                'profile' => [
                    'image' => $user->profile->avatar,
                ],
                'country' => [
                    'id' => @$user->country->id,
                    'name' => @$user->country->name,
                    'flag' => @$user->country->flag,
                ],
                'family_status' => 2,
                'type_user'            => intval(@$user->type_user) ?: 0, // both
                "manger_type"          => new MangerTypeResource(@$user->mangerType),
                'uuid'                 => @$user->uuid, // both
                'id_image'             => @$user->specialId?->ware?->show_img ?? '',
                'special_id'          =>  @$user->specialId?->ware?->id ?? 0,
            ];
        } else {
            $owner = new \stdClass();
        }

        $mems = FamilyUser::query()->with("user")->where('family_id', @$this->id)->where('status', 1)->where("user_type",'!=',2)->get();


        return [

            'id' => @$this->id,
            'name' => @$this->name ?: '',
            'introduce' => @$this->introduce ?: '',
            'image' => @$this->image ?: '',
            'max_num_of_members' => @$this->num ?: 0,
            'max_num_of_admins' => @$this->num_admins ?: 0,
            'owner' => $owner,
            'am_i_member' => FamilyUser::query()->where('user_id', $request->user()->id)->where('family_id', $this->id)->where('status', 1)->exists(),
            'am_i_owner' => (@$this->user_id == $request->user()->id) ? true : false,
            'am_i_admin' => $request->user()->is_family_admin ? true : false,
            'members' => ShortFamilyUserResource::collection($mems),
            'num_of_requests' => FamilyUser::query()->where('family_id', $this->id)->where('status', 0)->count(),
            'num_of_members' => ($this->members_count + 1),
            'level' => @$this->level ?: '',
        ];
    }
}
