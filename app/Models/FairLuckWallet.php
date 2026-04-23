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
        return self::increaseBalance(self::TYPE_UNIFIED_VAULT, $amount, $description, $userId);
    }

    /**
     * Decrease the unified vault balance.
     */
    public static function decreaseVault(int $amount, ?string $description = null, ?int $userId = null): bool
    {
        return self::decreaseBalance(self::TYPE_UNIFIED_VAULT, $amount, $description, $userId);
    }

    /**
     */
    public static function increaseBalance(string $walletType, int $amount, ?string $description = null, ?int $userId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        if ($walletType === self::TYPE_UNIFIED_VAULT) {
            self::incrementRedisBalance($walletType, $amount);

            try {
                $wallet = self::where('wallet_type', $walletType)->first();
                if (!$wallet) {
                    self::create([
                        'wallet_type' => $walletType,
                        'balance' => $amount,
                        'last_updated' => now(),
                    ]);

                    self::logHistory($walletType, $amount, 0, $amount, $description, $userId);
                    return true;
                }

                $before = $wallet->balance;
                self::where('wallet_type', $walletType)
                    ->increment('balance', $amount, ['last_updated' => now()]);
                $after = self::where('wallet_type', $walletType)->value('balance');

                self::logHistory($walletType, $amount, $before, $after, $description, $userId);
                return true;
            } catch (\Throwable $e) {
                self::decrementRedisBalance($walletType, $amount);
                throw $e;
            }
        }

        $wallet = self::where('wallet_type', $walletType)->first();

        if (!$wallet) {
            self::create([
                'wallet_type' => $walletType,
                'balance' => $amount,
                'last_updated' => now(),
            ]);
            self::logHistory($walletType, $amount, 0, $amount, $description, $userId);
            return true;
        }

        $before = $wallet->balance;
        self::where('wallet_type', $walletType)
            ->increment('balance', $amount, ['last_updated' => now()]);
        $after = self::where('wallet_type', $walletType)->value('balance');

        self::logHistory($walletType, $amount, $before, $after, $description, $userId);

        return true;
    }

    public static function decreaseBalance(string $walletType, int $amount, ?string $description = null, ?int $userId = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        if ($walletType === self::TYPE_UNIFIED_VAULT) {
            $limit = self::getNegativeLimit();
            if (!self::decrementRedisBalance($walletType, $amount)) {
                return false;
            }

            try {
                $wallet = self::where('wallet_type', $walletType)->first();
                if (!$wallet) {
                    self::create([
                        'wallet_type' => $walletType,
                        'balance' => -$amount,
                        'last_updated' => now(),
                    ]);

                    self::logHistory($walletType, -$amount, 0, -$amount, $description, $userId);
                    return true;
                }

                $before = $wallet->balance;
                $updated = self::where('wallet_type', $walletType)
                    ->where('balance', '>=', $amount - $limit)
                    ->decrement('balance', $amount, ['last_updated' => now()]);

                if (!$updated) {
                    self::incrementRedisBalance($walletType, $amount);
                    return false;
                }

                $after = self::where('wallet_type', $walletType)->value('balance');
                self::logHistory($walletType, -$amount, $before, $after, $description, $userId);
                return true;
            } catch (\Throwable $e) {
                self::incrementRedisBalance($walletType, $amount);
                throw $e;
            }
        }

        $wallet = self::where('wallet_type', $walletType)->first();
        if (!$wallet) {
            return false;
        }

        $limit = ($walletType === self::TYPE_GLOBAL_VAULT || $walletType === self::TYPE_UNIFIED_VAULT) ? self::getNegativeLimit() : 0;
        if (($wallet->balance + $limit) < $amount) {
            return false;
        }

        $before = $wallet->balance;
        $updated = self::where('wallet_type', $walletType)
            ->where('balance', '>=', $amount - $limit)
            ->decrement('balance', $amount, ['last_updated' => now()]);

        if (!$updated) {
            return false;
        }

        $after = self::where('wallet_type', $walletType)->value('balance');
        self::logHistory($walletType, -$amount, $before, $after, $description, $userId);

        return true;
    }

    public static function persistDecreaseBalance(string $walletType, int $amount, ?string $description = null, ?int $userId = null): bool
    {
        $wallet = self::where('wallet_type', $walletType)->first();
        $limit = ($walletType === self::TYPE_GLOBAL_VAULT || $walletType === self::TYPE_UNIFIED_VAULT) ? self::getNegativeLimit() : 0;

        if (!$wallet) {
            self::create([
                'wallet_type' => $walletType,
                'balance' => -$amount,
                'last_updated' => now(),
            ]);
            self::logHistory($walletType, -$amount, 0, -$amount, $description, $userId);
            return true;
        }

        $before = $wallet->balance;
        $updated = self::where('wallet_type', $walletType)
            ->where('balance', '>=', $amount - $limit)
            ->decrement('balance', $amount, ['last_updated' => now()]);

        if (!$updated) {
            return false;
        }

        $after = self::where('wallet_type', $walletType)->value('balance');
        self::logHistory($walletType, -$amount, $before, $after, $description, $userId);

        return true;
    }
   
    public static function setBalance(string $walletType, int $balance): bool
    {
        $maxRetries = 5;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
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
            } catch (\Exception $e) {
                $retryCount++;
                
                if (strpos($e->getMessage(), '1205') !== false && $retryCount < $maxRetries) {
                    // Exponential backoff: 100ms, 200ms, 400ms, 800ms, 1600ms
                    usleep(pow(2, $retryCount - 1) * 100 * 1000);
                    continue;
                }
                
                // If max retries exceeded or not a lock timeout, throw exception
                throw $e;
            }
        }
        
        return false;
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


    public static function incrementRedisBalance(string $walletType, int $amount): int
    {
        if ($amount <= 0) {
            return self::getRedisBalance($walletType);
        }

        $key = "fairluck:wallet:{$walletType}";

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
 
        $limit = ($walletType === self::TYPE_UNIFIED_VAULT || $walletType === self::TYPE_GLOBAL_VAULT) ? self::getNegativeLimit() : 0;

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