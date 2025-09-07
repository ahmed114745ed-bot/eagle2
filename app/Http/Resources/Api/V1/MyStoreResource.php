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
use Illuminate\Support\Facades\Log;

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
        /** @var User $this */

        $hostSalary = null;
        $salary     = round($this->salary, 2);
        $userSalary = $salary;
        
        if (in_array($this->type_user, [2, 4])) {
            $hostSalary = number_format((float)($this->agency?->salary ?? 0), 3, '.', '');
            $userSalary = $salary;
            $salary     = $hostSalary;
        }
        
        $totals = [
            'pending' => $this->totalUserSalary->sum('pending_dollar'),
            'paid'    => $this->totalUserSalary->sum('cut_amount'),
        ];
        
        $roomSalary = $this->ownerRoom?->roomSalary->sum(fn($r) => $r->salary - $r->cut_amount);
        
        $diamonds = in_array($this->type_user, [0, 3])
            ? $this->exchange_diamonds
            : $this->monthly_diamond_received;

        return [
            'my_store' => [
                'id'             => $this->id,
                'coins_new'      => $this->di,
                'coins'          => (string) $this->di,
                'diamonds'       => (string) $diamonds,
                'silver_coins'   => (string) $this->gold,
                'usd'            => (double) $salary,
                'user_usd'       => (string) truncateAndTrim($userSalary),
                'user_usd_new'   => (string) round($userSalary, 0),
                'host_usd'       => (string) $hostSalary,
                'pending_dollar' => (string) $totals['pending'],
                'room_salary'    => (string) $roomSalary,
                'paid'           => $totals['paid'],
                'wallet_balance' => $this->wallet?->current_balance ?? 0,
            ],
        ];
    }

}
