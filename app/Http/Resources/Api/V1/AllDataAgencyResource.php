<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Models\Agency;
use App\Models\Target;
use App\Models\GiftLog;
use App\Models\UserSallary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\MyDataForAgancyResource;

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


        $authUser = Auth::user();
        $owner = @$authUser->ownAgency;
        $admin = @$authUser->agencyUserJob;
        $type = '';
        if (($this->Shipping_agency == 1) && ($this->Host_agency == 1)) {
            $type = 'hosts and shipping';
        } elseif (($this->Shipping_agency == 0) && ($this->Host_agency == 1)) {
            $type = 'hosts';
        } elseif (($this->Shipping_agency == 1) && ($this->Host_agency == 0)) {
            $type = 'shipping';
        }

        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;

        $giftLog = $this->getTopGiftLogsByUserType(
            year: $year,
            month: $month,
            userType: 'receiver',
            limit: 5
        );

        $heroGiftLog = $this->getTopGiftLogsByUserType(
            year: $year,
            month: $month,
            userType: 'sender',
            limit: 5
        );
        // $target =Target::where('level',$this->id)->first();
        // // $target_usd =Agency::where('id',$this->id)->sum('target_usd');
        // $Theratio=$target->agency_share /100;
        // $All=$target->usd* $Theratio ;
        /** @var Agency $this*/
        return [
            'id' => $this->id ?: 0,
            'target' => $result ?: 0,
            'name' => $this->name ?: '',
            'notice' => $this->notice ?: '',
            // 'status'=>$this->status,
            'phone' => $this->phone ?: 0,
            // 'url'=>$this->url,
            'img' => $this->img ?: '',
            'agency_type' => $type,
            'num_of_hosts'      => $this->mempers->count(),
            // 'contents'=>$this->contents,
            'owner' => new MyDataForAgancyResource($this->owner) ?: [
                "id" => 0,
                "uuid" => '',
                "target_usd" => 0,
                'name' => '',
                "profile" => [
                    "image" => ''
                ]
            ],
            'mempers_count' => $this->mempers_count,
            // 'mempers'=>$this->mempers ?? (object)[], 
            'members' => MyDataForAgancyNewResource::collection(@$this->mempers),
            'user_agency_status' => $owner ? 2 : ($admin ? 1 : 3),
            'admins' => AdminsAgencyResource::collection($this->admins),
            'star' => ReceiverGiftLogResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
        ];
    }

    public function getTopGiftLogsByUserType(int $year, int $month, string $userType, int $limit = null)
    {
        $userRelation = $userType; // 'receiver' or 'sender'
        $userColumn   = $userType . '_id'; // receiver_id or sender_id

        $query = GiftLog::where('agency_id', $this->id)
            ->selectRaw("SUM(giftPrice) as exp, {$userColumn}")
            ->with($userRelation)
            ->groupBy($userColumn)
            ->whereHas($userRelation)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('exp');

        if ($limit) {
            $query->take($limit);
        }

        return $query->get();
    }
}
