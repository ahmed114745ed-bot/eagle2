<?php


namespace App\Services;

use App\Models\UserWallet;
use Illuminate\Support\Facades\Auth;

class BDChargeService
{
    /**
    
     *
     * @param float $amount
     * @param int|null $bdId
     * @return bool
     */
    public function charge(float $amount, ?int $bdId = null,$type): bool
    {

        
    }
}
