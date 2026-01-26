<?php

namespace Modules\SalaryTransaction\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChargeCountryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->country_id,
            'name'          => app ()->getLocale () == 'ar' ? ($this->country?->name?:'') : ($this->country?->e_name?:''),
            'flag'          =>$this->country?->flag?:'',
            'lang'          =>$this->country?->language?:'',
            'phone_code'    =>$this->country?->phone_code?:''
        ];
    }
}
