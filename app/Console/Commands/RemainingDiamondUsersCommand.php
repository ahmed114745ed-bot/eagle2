<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\UserSallary;
use App\Enums\UserCoinLogType;
use Illuminate\Console\Command;
use App\Helpers\UserCoinLogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MonthlyDiamondReceive;
use App\Traits\Salaries\UserSalaryTrait;


class ResetUserMonthlyDiamond extends Command
{
    use UserSalaryTrait;

    protected $signature = 'remaining-diamonds';
    protected $description = 'Process remaining diamonds after 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $setting = Common::getSettingValue('remaining_diamonds') ?? 'nothing';

            if ($setting === 'nothing') {
                $this->info('Remaining Diamonds Command Run Successfully !');
                return;
            }

            $timezone = getTimezone();
            $dt = Carbon::now($timezone);

            // Previous month
            $previous = $dt->copy()->subMonth();
            $month = $previous->month;
            $year  = $previous->year;

            $userSalaries = UserSallary::where(['month' => $month, 'year' => $year])
                ->with('user')
                ->get();

            // Cache exchange percentage to avoid repeated DB calls
            $exchangePercentage = $setting === 'coins'
                ? (Common::getSettingValue('exchange_coin_percentage') ?? 1)
                : 0;

            foreach ($userSalaries as $userSalary) {
                $user = $userSalary->user;
                $diamonds = $userSalary->remaining_diamond ?? 0;

                if (!$user || $diamonds <= 0) {
                    continue;
                }

                if ($setting === 'coins') {
                    $this->processCoins($user, $diamonds, $exchangePercentage);
                }

                if ($setting === 'diamonds') {
                    $this->processDiamonds($user, $diamonds, $dt);
                }
            }

            $this->info('Remaining Diamonds Command Run Successfully !');
        } catch (\Exception $exception) {
            $this->error('Remaining Diamonds failed: ' . $exception->getMessage());
        }
    }

    /**
     * Process remaining diamonds as coins
     */
    private function processCoins($user, int $diamonds, float $exchangePercentage)
    {
        $exchangeCoin = floor(($exchangePercentage / 100) * $diamonds);
        $amountBefore = $user->di;

        UserCoinLogHelper::logByType(
            $user->id,
            $exchangeCoin,
            $amountBefore,
            UserCoinLogType::REMAINING_DIAMONDS
        );

        $user->di += $exchangeCoin;
        $user->save();
    }

    /**
     * Process remaining diamonds as diamonds
     */
    private function processDiamonds($user, int $diamonds, Carbon $dt)
    {
        $monthDiamondReceive = MonthlyDiamondReceive::firstOrNew(
            [
                'user_id' => $user->id,
                'month'   => $dt->month,
                'year'    => $dt->year,
            ],
            [
                'monthly_diamond_received' => 0
            ]
        );

        $monthDiamondReceive->monthly_diamond_received += $diamonds;
        $monthDiamondReceive->save();

        GiftLog::create([
            'giftId' => 0,
            'roomowner_id' => 0,
            'giftPrice' => $diamonds,
            'giftNum' => 1,
            'sender_id' => 0,
            'receiver_id' => $user->id,
        ]);
    }
}
