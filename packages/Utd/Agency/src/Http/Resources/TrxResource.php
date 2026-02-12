<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TrxResource extends JsonResource
{
    public function toArray($request)
    {
        $statuses = [
            0 => 'pending',
            1 => 'success',
            2 => 'canceled',
            3 => 'failed',
        ];

        return [
            'id' => $this->id,
            'usd' => $this->paid_usd,
            'coins' => $this->obtained_coins,
            'method' => $this->method,
            'status' => $statuses[$this->status],
            'trx_num' => $this->trx,
            'date' => Carbon::parse($this->created_at)->format('Y/m/d H:i:s'),
        ];
    }
}
