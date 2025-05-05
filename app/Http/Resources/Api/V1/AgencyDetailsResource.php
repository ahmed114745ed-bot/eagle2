<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\MyDataForAgancyResource;
use App\Models\Agency;
use App\Models\Target;
use App\Models\UserSallary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AgencyDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */



    public function toArray($request)
    {
        $target = UserSallary::where('user_agency_id', $this->agency_id)->sum('agency_sallary');

        // Calculate the agency share threshold based on the target

        $minValue = Target::where('usd', '<', $target)
            ->orderBy('usd', 'desc')
            ->first();

        $result = (@$minValue->agency_share / 100) * @$target;


        $authUser = Auth::user();
        $owner = @$authUser->ownAgency;
        $admin = @$authUser->agencyUserJob;

        
        return [
            'id' => $this->id ?: 0, 
            'name' => $this->name ?: '',
            'img' => $this->img ?: '',
            'owner' => new MyDataForAgancyResource($this->owner) ?: [
                "id" => 0,
                "uuid" => '',
                "target_usd" => 0,
                'name' => '',
                "profile" => [
                    "image" => ''
                ]
            ],
            'admins' => AdminsAgencyResource::collection($this->admins),
            'target' => $result ?: 0,
            'mempers_count' => $this->mempers_count,
            // 'mempers'=>$this->mempers ?? (object)[], 
            'members' => MyDataForAgancyNewResource::collection($this->mempers),
          
        ];
    }
}
