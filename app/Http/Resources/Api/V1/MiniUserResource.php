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

class MiniUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if(!@$this->id){
            return ;
        }
        $data = [
            'id'=>@$this->id,
            'uuid'=>@$this->uuid,
            'name'=>@$this->name?:'',
            'profile' => [
                'image' =>@ $this->profile->avatar?:'',
                'image_id' => @$this->profile->image_id?:'',
            ],
            'frame'=> $this->getUserDress(4, $this->dress_1, 'img2') ?: $this->getUserDress(4, $this->dress_1, 'img1'),
            'frame_id'=>@$this->dress_1,
            'has_color_name'=>$this->getPackWithType(18),
        ];

        return $data;
    }

    public function getUserDress($type, $dress, $item = 'img1')
    {

        $packs = $this->packs;
        /** @var \Illuminate\Database\Eloquent\Collection $packs */
        $pack = $packs->where('type', $type)->where('target_id', $dress)->first();
        if ($pack) {
            if ($pack->ware) {
                return $pack->ware->{$item};
            }
        }
        return '';
    }


}
