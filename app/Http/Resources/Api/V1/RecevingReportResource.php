<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class RecevingReportResource extends JsonResource
{
   
    public function toArray($request)
    {
        $name = '';
        $image = '';
        if($this->charger_type == 'dash'){
            // admin
            $name = $this->admin?->name;
            $image = $this->admin?->avatar;
        } else if($this->charger_type == 'agency'){
            $name = $this->senderAgency?->name;
            $image = $this->senderAgency?->img;
        }
        return [
            'id'          => $this->user_id,
            'diamonds'    => numToStringNew($this->amount),
            'operation_no'=> (int)$this->id,
            'created_at'  => Carbon::parse( $this->created_at)->format('Y-m-d h:i:s A'),
            'name' => $name,
            'image' => $image,
        ];
    }
}
