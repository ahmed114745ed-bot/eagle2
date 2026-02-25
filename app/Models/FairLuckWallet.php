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

    /**
     * تقليل الرصيد في Redis (Atomic with Check)
     */
    public static function decrementRedisBalance(string $walletType, int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }

        $key = "fairluck:wallet:{$walletType}";

        // Initialize if not exists
        if (Redis::get($key) === null) {
            self::getRedisBalance($walletType);
        }

        $script = '
            local current = redis.call("get", KEYS[1])
            if not current or tonumber(current) < tonumber(ARGV[1]) then
                return 0
            end
            redis.call("decrby", KEYS[1], ARGV[1])
            return 1
        ';

        return (bool) Redis::eval($script, 1, $key, $amount);
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