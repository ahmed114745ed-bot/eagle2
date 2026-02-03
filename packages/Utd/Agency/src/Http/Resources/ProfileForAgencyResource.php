<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileForAgencyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'image'=>$this->avatar?:'',
        ];
    }
}
