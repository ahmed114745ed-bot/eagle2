<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use App\Models\Agency;
use App\Models\AgencyJoinRequest;
use App\Models\Family;
use App\Models\Pack;
use App\Models\Room;
use App\Models\RoomSalary;
use App\Models\Target;
use App\Models\UserSallary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MyStoreResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $agency_owner = Agency::where('id',$this->agency_id)->first();
        $salary       = $this->salary;
        $sallary      = $salary;//
        $userSalary   = $sallary;
        if(($this->type_user == 2 || $this->type_user == 4)){
            $userSalary = $salary;//
            $hostSalary = floor($agency_owner?->salary ?? 0);
            $sallary = $hostSalary;
        }

        $pendingDollar = UserSallary::where("user_id",$this->id)->sum("pending_dollar");
        $roomSalary = RoomSalary::where("room_id",$this->ownerRoom?->id)->sum(DB::raw("salary - cut_amount"));

        $data = [

            'my_store'=> [
                'id'=>$this->id,
                'coins'=>$this->di,
                'diamonds'=>$this->user_diamond,
                'silver_coins'=>$this->gold,
                'usd' => $sallary ,
                'user_usd' => $userSalary ?? 0,
                'host_usd' => $hostSalary ?? 0,
                'pending_dollar' => $pendingDollar ?? 0,
                'pending_dollar' => $pendingDollar ?? 0,
                'room_salary' =>(int) $roomSalary ?? 0,
            ], // my

        ];

        return $data;
    }
}
