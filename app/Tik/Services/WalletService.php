<?php
namespace App\Tik\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\WalletTransaction;

class WalletService
{
    public function credit(User $user, float $amount, string $type, string $desc = null, array $data = []) {
        \DB::transaction(function () use ($user, $amount, $type, $desc, $data) {
            $wallet = UserWallet::firstOrCreate(['user_id' => $user->id]);
            $wallet->increment('value', $amount);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'value' => $amount,
                'description' => $desc,
                'description_data' => json_encode($data),
            ]);
        });
    }

    public function debit(User $user, float $amount, string $type, string $desc = null, array $data = []) {
        \DB::transaction(function () use ($user, $amount, $type, $desc, $data) {
            $wallet = UserWallet::firstOrCreate(['user_id' => $user->id]);
            if ($wallet->value < $amount) {
                throw new \Exception("الرصيد غير كافٍ");
            }

            $wallet->decrement('value', $amount);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'value' => -$amount,
                'description' => $desc,
                'description_data' => json_encode($data),
            ]);
        });
    }
}
