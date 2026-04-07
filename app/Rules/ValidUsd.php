<?php

namespace App\Rules;

use App\Helpers\Common;
use Illuminate\Contracts\Validation\Rule;

class ValidUsd implements Rule
{
    protected $diamonds;
    protected $appTarget;
    protected $dollar;

    public function __construct($diamonds)
    {
        $this->diamonds = $diamonds;
        $this->appTarget = \App\Services\CoinRateService::getUserTransferRate();
        $this->dollar = $this->appTarget > 0 ? ($this->diamonds / $this->appTarget) * 0.6 : 0;
        $this->dollar   =     number_format((float)$this->dollar, 2, '.', '');
    }

    public function passes($attribute, $value)
    {
        return $value <= $this->dollar;
    }

    public function message()
    {
        return 'The usd value must be ' . $this->dollar;
    }
}