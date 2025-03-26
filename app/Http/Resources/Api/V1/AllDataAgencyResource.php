<?php

namespace App\Http\Resources\Api\V1;
use App\Http\Resources\Api\V1\MyDataForAgancyResource;
use App\Models\Agency;
use App\Models\Target;
use App\Models\UserSallary;
use Illuminate\Http\Resources\Json\JsonResource;

class AllDataAgencyResource extends JsonResource
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
        $authUser = request()->user;
        $owner = @$authUser->ownAgency;
        $admin = @$authUser->agencyUserJob;

        // $target =Target::where('level',$this->id)->first();
        // // $target_usd =Agency::where('id',$this->id)->sum('target_usd');
        // $Theratio=$target->agency_share /100;
        // $All=$target->usd* $Theratio ;
        /** @var Agency $this*/
        return [
            'id'=>$this->id?:0,
            'target'=>$result?:0,
            'name'=>$this->name?:'',
            'notice'=>$this->notice?:'',
            // 'status'=>$this->status,
            'phone'=>$this->phone?:0,
            // 'url'=>$this->url,
            'img'=>$this->img?:'',
            'num_of_hosts'      => $this->mempers->count(),
            // 'contents'=>$this->contents,
            'owner'=>new MyDataForAgancyResource($this->owner)?:[
                "id" => 0,
                "uuid" => '',
                "target_usd" => 0,
                'name' => '',
                "profile" => [
                "image" => ''
                ]
            ],
            'mempers_count'=>$this->mempers_count,
            // 'mempers'=>$this->mempers ?? (object)[], 
            'members'=>MyDataForAgancyNewResource::collection($this->mempers),
            'user_agency_status' => $owner ? 2 : ($admin ? 1 : 3),
        ];
    }
}
