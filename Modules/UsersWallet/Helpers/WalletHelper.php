<?php

namespace Modules\UsersWallet\Helpers;

use Exception;
use Modules\UsersWallet\Entities\UserWallet;
use Modules\UsersWallet\Entities\WalletLog;
use App\Models\Agency;
use Modules\UsersWallet\Entities\UserWithdrawal;

class WalletHelper
{
    /**
     *
     * @param int $userId
     * @param array $newData  ['sallary'=>..,'agency_sallary'=>..,'dB'=>..]
     * @param array|null $oldData ['sallary'=>..,'agency_sallary'=>..,'dB'=>..]
     * @param int|null $agencyId
     * @param string $type
     */
    public static function addAllBalancesByDiffs(int $userId, array $newData, ?array $oldData = null, ?int $agencyId = null, string $type = 'system')
    {
        $user_diff   = ($newData['sallary'] ?? 0) - ($oldData['sallary'] ?? 0);
        $agency_diff = ($newData['agency_sallary'] ?? 0) - ($oldData['agency_sallary'] ?? 0);
        $bd_diff     = ($newData['dB'] ?? 0) - ($oldData['dB'] ?? 0);

        if (!$userId) {
            throw new Exception('معرف المستخدم غير موجود.');
        }

        if ($user_diff != 0) {
            self::addBalance($userId, $user_diff, 'user');
        }

        $ownerId = null;
        $bdId    = null;

        if ($agencyId) {
            $agency = Agency::find($agencyId);
            if ($agency) {
                $ownerId = $agency->app_owner_id;
                $bdId    = $agency->bd_id;
            }
        }
        if ($ownerId && $agency_diff != 0) {
            self::addBalance($ownerId, $agency_diff, 'agency_owner');
        }

        if ($bdId && $bd_diff != 0) {
            self::addBalance($bdId, $bd_diff, 'bd');
        }
    }


    private static function addBalance($userId, $amount, $type )
    {
        $wallet = UserWallet::firstOrCreate(['user_id' => $userId]);

        $before = $wallet?->balance ?? 0 ;
        $wallet->balance += $amount;
        $wallet->save();

        WalletLog::create([
            'wallet_id' => $wallet->id,
            'user_id' => $userId,
            'amount' => $amount,
            'operation' => 'add',
            'type' => $type,
            'before_amount' => $before ,
            'after_amount' => $wallet->balance,
        ]);
    }


    public static function createWithdrawal($userId, $amount, array $meta = [])
    {
        $wallet = UserWallet::firstOrCreate(['user_id' => $userId]);
    
        $currentBalance = $wallet->balance;
        $currentPending  = $wallet->pending_amount;
    
        $available = $currentBalance - $currentPending;
    
        if ($available < $amount) {
            throw new \Exception('الرصيد غير كافٍ لإجراء السحب.');
        }
    
        $wallet->pending_amount += $amount;
        $wallet->save();
    
        return UserWithdrawal::create([
            'user_id' => $userId,
            'amount'  => $amount,
            'status'  => 'pending',
            'meta'    => $meta,
        ]);
    }
    

}
