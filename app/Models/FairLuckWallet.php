<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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

    const TYPE_UNIFIED_VAULT = 'unified_vault';
    /** @deprecated Use unified_vault for unified liquidity */
    const TYPE_GLOBAL_VAULT = 'global_vault';
    /** @deprecated Use unified_vault for unified liquidity */
    const TYPE_JACKPOT_WALLET = 'jackpot_wallet';
    /** @deprecated Use unified_vault for unified liquidity */
    const TYPE_MEDIUM_WALLET = 'medium_wallet';

    public static function getBalance(string $walletType): int
    {
        $wallet = self::where('wallet_type', $walletType)->first();
        return $wallet ? $wallet->balance : 0;
    }

    /**
     * Get the unified vault balance (The new Unified Pool).
     */
    public static function getVaultBalance(): int
    {
        return self::getRedisBalance(self::TYPE_UNIFIED_VAULT);
    }

    /**
     * Increase the unified vault balance.
     */
    public static function increaseVault(int $amount, ?string $description = null, ?int $userId = null): bool
    {
        self::incrementRedisBalance(self::TYPE_UNIFIED_VAULT, $amount);
        return self::increaseBalance(self::TYPE_UNIFIED_VAULT, $amount, $description, $userId);
    }

    /**
     * Decrease the unified vault balance.
     */
    public static function decreaseVault(int $amount, ?string $description = null, ?int $userId = null): bool
    {
        self::decrementRedisBalance(self::TYPE_UNIFIED_VAULT, $amount);
        return self::decreaseBalance(self::TYPE_UNIFIED_VAULT, $amount, $description, $userId);
    }

    /**
     * زيادة رصيد محفظة معينة
     */
    public static function increaseBalance(string $walletType, int $amount, ?string $description = null, ?int $userId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($walletType, $amount, $description, $userId) {
            $wallet = self::where('wallet_type', $walletType)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = self::create([
                    'wallet_type' => $walletType,
                    'balance' => $amount,
                    'last_updated' => now(),
                ]);
                self::logHistory($walletType, $amount, 0, $amount, $description, $userId);
                return true;
            }

            $before = $wallet->balance;
            $wallet->increment('balance', $amount);
            $wallet->update(['last_updated' => now()]);
            self::logHistory($walletType, $amount, $before, $wallet->balance, $description, $userId);

            return true;
        });
    }

    public static function decreaseBalance(string $walletType, int $amount, ?string $description = null, ?int $userId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        return DB::transaction(function () use ($walletType, $amount, $description, $userId) {
            $wallet = self::where('wallet_type', $walletType)
                ->lockForUpdate()
                ->first();

            $limit = ($walletType === self::TYPE_GLOBAL_VAULT) ? self::getNegativeLimit() : 0;

            if (!$wallet || ($wallet->balance + $limit) < $amount) {
                return false;
            }

            $before = $wallet->balance;
            $newBalance = $wallet->balance - $amount;
            $wallet->update([
                'balance' => $newBalance,
                'last_updated' => now(),
            ]);

            self::logHistory($walletType, -$amount, $before, $newBalance, $description, $userId);

            return true;
        });
    }

   
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
            'unified_vault' => $wallets[self::TYPE_UNIFIED_VAULT] ?? 0,
            'global_vault' => $wallets[self::TYPE_GLOBAL_VAULT] ?? 0,
            'jackpot_wallet' => $wallets[self::TYPE_JACKPOT_WALLET] ?? 0,
            'medium_wallet' => $wallets[self::TYPE_MEDIUM_WALLET] ?? 0,
        ];
    }

    /**
     * الحصول على الرصيد من Redis
     */
    public static function getRedisBalance(string $walletType): int
    {
        $key = "fairluck:wallet:{$walletType}";
        $balance = Redis::get($key);

        if ($balance === null) {
            $balance = self::getBalance($walletType);
            Redis::set($key, $balance);
            Redis::expire($key, 86400);
        }

        return (int) $balance;
    }

    /**
     * زيادة الرصيد في Redis (Atomic)
     */
    public static function incrementRedisBalance(string $walletType, int $amount): int
    {
        if ($amount <= 0) {
            return self::getRedisBalance($walletType);
        }

        $key = "fairluck:wallet:{$walletType}";

        // Initialize if not exists
        if (Redis::get($key) === null) {
            self::getRedisBalance($walletType);
        }

        return (int) Redis::incrby($key, $amount);
    }

    public static function decrementRedisBalance(string $walletType, int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }

        $key = "fairluck:wallet:{$walletType}";

        if (Redis::get($key) === null) {
            self::getRedisBalance($walletType);
        }
 
        $limit = ($walletType === self::TYPE_UNIFIED_VAULT) ? self::getNegativeLimit() : 0;

        $script = '
            local current = redis.call("get", KEYS[1])
            if not current then
                return 0
            end
            local new_balance = tonumber(current) - tonumber(ARGV[1])
            if new_balance < -tonumber(ARGV[2]) then
                return 0
            end
            redis.call("set", KEYS[1], new_balance)
            return 1
        ';

        return (bool) Redis::eval($script, 1, $key, $amount, $limit);
    }

    public static function getNegativeLimit(): int
    {
        return (int) FairLuckSetting::getByKey('global_vault_negative_limit', 30000);
    }

    public static function logHistory(string $walletType, int $amount, int $before, int $after, ?string $description = null, ?int $userId = null): void
    {
        FairLuckWalletHistory::create([
            'wallet_type' => $walletType,
            'amount' => $amount,
            'balance_before' => $before,
            'balance_after' => $after,
            'description' => $description,
            'user_id' => $userId,
        ]);
    }

    /**
     * مزامنة رصيد Redis إلى قاعدة البيانات
     */
    public static function syncToDatabase(string $walletType): bool
    {
        $balance = (int) Redis::get("fairluck:wallet:{$walletType}");
        return self::setBalance($walletType, $balance);
    }
}