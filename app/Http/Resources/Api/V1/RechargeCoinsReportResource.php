<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class RechargeCoinsReportResource extends JsonResource
{


    public function toArray($request)
    {
        $method = match ($this->method) {
            "huawei_pay" => "huawei pay",
            "google_pay" => "google pay",
            "apple_pay" => "apple pay",
            default => "fawry",
        };
        $user = auth()->user();
        return [
            'id'          => $this->user_id,
            'uuid'          => $user->uuid,
            'diamonds'    => numToStringNew($this->obtained_coins),
            'operation_no' => (int)$this->trx,
            'created_at'  => Carbon::parse(@$this->created_at)->format('Y-m-d h:i:s A'),
            'type' => $method,
            'coins' => numToStringNew($this->obtained_coins)
        ];
    }
}
