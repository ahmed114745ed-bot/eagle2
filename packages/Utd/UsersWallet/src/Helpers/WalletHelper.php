<?php

namespace Utd\UsersWallet\Helpers;

use Exception;
use Utd\UsersWallet\Entities\UserWallet;
use Utd\UsersWallet\Entities\WalletLog;
use App\Models\Agency;
use Utd\UsersWallet\Entities\UserWithdrawal;

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
    public static function addAllBalancesByDiffs(int $userId, array $newData, ?array $oldData = null, ?int $agencyId = null, string $type = 'system' ,$target_id = null)
    {

        $user_diff   = ($newData['sallary'] ?? 0) - ($oldData['sallary'] ?? 0);
        $agency_diff = ($newData['agency_sallary'] ?? 0) - ($oldData['agency_sallary'] ?? 0);
        $bd_diff     = ($newData['dB'] ?? 0) - ($oldData['dB'] ?? 0);

        if (!$userId) {
            throw new Exception('معرف المستخدم غير موجود.');
        }

        if ($user_diff != 0) {
            self::addBalance($userId, $user_diff, 'user' ,$target_id);
        }

        $ownerId = null;
        $bdId    = null;

        if ($agencyId) {
            $agency = Agency::find($agencyId);
            if ($agency) {
                $ownerId = $agency->app_owner_id;
                $bdId    = @$agency?->bd?->app_id;
            }
        }
        if ($ownerId && $agency_diff != 0) {
            self::addBalance($ownerId, $agency_diff, 'agency_owner',$target_id);
        }

        if ($bdId && $bd_diff != 0) {
            self::addBalance($bdId, $bd_diff, 'bd' ,$target_id);
        }
    }


    private static function addBalance($userId, $amount, $type ,$target_id)
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
            'related_id' => $target_id,
        ]);
    }


    public static function createWithdrawal($userId, $amount, array $meta = [])
    {
        $wallet = UserWallet::firstOrCreate(['user_id' => $userId]);
        $before = $wallet?->balance ?? 0 ;
        $available = wallet_available_by_wallet($wallet);

        if ($available < $amount) {
            throw new Exception('Insufficient balance.');
        }
    
        $wallet->pending_amount += $amount;
        $wallet->save();
    
        $userWithdrawal = UserWithdrawal::create([
            'user_id' => $userId,
            'amount'  => $amount,
            'status'  => 'pending',
            'meta'    => $meta,
        ]);

         WalletLog::create([
            'wallet_id' => $wallet->id,
            'user_id' => $userId,
            'amount' => $amount,
            'operation' => 'withdrawal_pending',
            'type' => 'user',
            'before_amount' => $before ,
            'after_amount' => $wallet->balance,
            'related_id' => $userWithdrawal->id,
        ]);

        return $userWithdrawal;
    }
    

}
