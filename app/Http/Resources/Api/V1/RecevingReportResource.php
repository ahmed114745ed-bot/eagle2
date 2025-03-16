<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class RecevingReportResource extends JsonResource
{

    public function toArray($request)
    {
        $user = auth()->user();
        if ($this->charger_type == 'dash') {
            $name = $this->admin->name ?? '';
            $image = $this->admin->avatar ?? '';
        } else {
            $name = $this->sender->name ?? '';
            $image = $this->sender->profile->avatar ?? '';
        }

        return [
            'id'          => $this->user_id,
            'uuid'          => $user->uuid,
            'diamonds'    => numToStringNew($this->amount),
            'operation_no' => (int)$this->id,
            'created_at'  => Carbon::parse($this->created_at)->format('Y-m-d h:i:s A'),
            'name' => $name ?? '',
            'image' => $image ?? '',
        ];
    }
}
