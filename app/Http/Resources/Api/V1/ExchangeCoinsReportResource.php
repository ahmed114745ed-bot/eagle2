<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeCoinsReportResource extends JsonResource
{
   
    public function toArray($request)
    {
        return [
            'id'          => $this->user_id, 
            'diamonds'    => $this->diamonds,
            'value'       => $this->value,
            'operation_no'=> (int)$this->operation_no,
            'created_at'  => $this->created_at->format('Y-m-d h:i:s A')
        ];
    }
}
