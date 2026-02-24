<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FairLuckWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_type',
        'balance',
        'last_updated',
    ];

    protected $casts = [
        'balance' => 'integer',
        'last_updated' => 'datetime',
    ];

    // أنواع المحافظ
    const TYPE_GLOBAL_VAULT = 'global_vault';
    const TYPE_JACKPOT_WALLET = 'jackpot_wallet';
    const TYPE_MEDIUM_WALLET = 'medium_wallet';

    /**
     * الحصول على رصيد محفظة معينة
     */
    public static function getBalance(string $walletType): int
    {
        $wallet = self::where('wallet_type', $walletType)->first();
        return $wallet ? $wallet->balance : 0;
    }

    /**
     * زيادة رصيد محفظة معينة
     */
    public static function increaseBalance(string $walletType, int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($walletType, $amount) {
            $wallet = self::where('wallet_type', $walletType)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                // إنشاء المحفظة إذا لم تكن موجودة
                $wallet = self::create([
                    'wallet_type' => $walletType,
                    'balance' => $amount,
                    'last_updated' => now(),
                ]);
                return true;
            }

            $wallet->increment('balance', $amount);
            $wallet->update(['last_updated' => now()]);
            
            return true;
        });
    }

    /**
     * تقليل رصيد محفظة معينة
     */
    public static function decreaseBalance(string $walletType, int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($walletType, $amount) {
            $wallet = self::where('wallet_type', $walletType)
                ->lockForUpdate()
                ->first();

            if (!$wallet || $wallet->balance < $amount) {
                return false;
            }

            $newBalance = max(0, $wallet->balance - $amount);
            $wallet->update([
                'balance' => $newBalance,
                'last_updated' => now(),
            ]);

            return true;
        });
    }

    /**
     * تعيين رصيد محفظة معينة
     */
    public static function setBalance(string $walletType, int $balance): bool
    {
        return DB::transaction(function () use ($walletType, $balance) {
            $wallet = self::where('wallet_type', $walletType)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                self::create([
                    'wallet_type' => $walletType,
                    'balance' => max(0, $balance),
                    'last_updated' => now(),
                ]);
                return true;
            }

            $wallet->update([
                'balance' => max(0, $balance),
                'last_updated' => now(),
            ]);

            return true;
        });
    }

    /**
     * الحصول على معلومات جميع المحافظ
     */
    public static function getAllBalances(): array
    {
        $wallets = self::all()->pluck('balance', 'wallet_type')->toArray();
        
        return [
            'global_vault' => $wallets[self::TYPE_GLOBAL_VAULT] ?? 0,
            'jackpot_wallet' => $wallets[self::TYPE_JACKPOT_WALLET] ?? 0,
            'medium_wallet' => $wallets[self::TYPE_MEDIUM_WALLET] ?? 0,
        ];
    }
}