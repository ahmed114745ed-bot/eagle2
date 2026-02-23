<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckLossLedger;
use App\Models\FairLuckLossPoolTotal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LossLedger
{
    public function recordSnapshot(
        int $userId,
        float $lossScore,
        int $contributionBank,
        int $jackpotPity,
        int $lossMomentum
    ): void {
        FairLuckLossLedger::updateOrCreate(
            ['user_id' => $userId],
            [
                'loss_score' => $lossScore,
                'contribution_bank' => $contributionBank,
                'jackpot_pity' => $jackpotPity,
                'loss_momentum' => max(0, $lossMomentum),
            ]
        );
    }

    public function addToGlobalPool(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount) {
            $pool = $this->poolRowForUpdate();
            $pool->balance += $amount;
            $pool->lifetime_contributed += $amount;
            $pool->save();
        });
    }

    public function removeFromGlobalPool(int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($amount) {
            $pool = $this->poolRowForUpdate();
            $deducted = min($amount, max(0, $pool->balance));
            if ($deducted <= 0) {
                return 0;
            }

            $pool->balance -= $deducted;
            $pool->lifetime_paid_out += $deducted;
            $pool->save();

            return (int) $deducted;
        });
    }

    public function poolBalance(): int
    {
        return (int) $this->poolRow()->balance;
    }

    public function poolCanCover(int $amount): bool
    {
        if ($amount <= 0) {
            return true;
        }

        return $this->poolBalance() >= $amount;
    }

    public function isPriorityHolder(int $userId): bool
    {
        return in_array($userId, $this->priorityWindowUserIds(), true);
    }

    public function currentPriorityHolder(): ?FairLuckLossLedger
    {
        return $this->eligibleQuery()
            ->orderBy('loss_score')
            ->orderByDesc('loss_momentum')
            ->orderBy('rotation_count')
            ->orderBy('updated_at')
            ->first();
    }

    private function priorityWindowUserIds(): array
    {
        $limit = $this->priorityWindow();

        return $this->eligibleQuery()
            ->orderBy('loss_score')
            ->orderByDesc('loss_momentum')
            ->orderBy('rotation_count')
            ->orderBy('updated_at')
            ->limit($limit)
            ->pluck('user_id')
            ->all();
    }

    public function markHighMultiplierAwarded(int $userId, int $payoutAmount): void
    {
        $cooldownUntil = Carbon::now()->addHours($this->cooldownHours());

        FairLuckLossLedger::where('user_id', $userId)->update([
            'cooldown_until' => $cooldownUntil,
            'loss_score' => 0,
            'loss_momentum' => 0,
            'rotation_count' => DB::raw('rotation_count + 1'),
            'last_high_multiplier_at' => Carbon::now(),
            'contribution_bank' => DB::raw('contribution_bank - ' . max(0, $payoutAmount)),
        ]);

        $this->removeFromGlobalPool($payoutAmount);
    }

    private function eligibleQuery(): Builder
    {
        return FairLuckLossLedger::query()
            ->where(function (Builder $query) {
                $query->whereNull('cooldown_until')
                    ->orWhere('cooldown_until', '<=', Carbon::now());
            });
    }

    private function poolRow(): FairLuckLossPoolTotal
    {
        return FairLuckLossPoolTotal::query()->firstOrCreate([], [
            'balance' => 0,
            'lifetime_contributed' => 0,
            'lifetime_paid_out' => 0,
        ]);
    }

    private function poolRowForUpdate(): FairLuckLossPoolTotal
    {
        $this->poolRow();

        return FairLuckLossPoolTotal::query()->lockForUpdate()->first();
    }

    private function cooldownHours(): int
    {
        return max(1, (int) config('fairluck.loss_rotation.cooldown_hours', 6));
    }

    private function priorityWindow(): int
    {
        return max(1, (int) config('fairluck.loss_rotation.priority_window', 1));
    }

    /**
     */
    public function resetPool(): int
    {
        return DB::transaction(function () {
            $pool = $this->poolRowForUpdate();
            $oldBalance = $pool->balance;
            $pool->balance = 0;
            $pool->save();
            
            return (int) $oldBalance;
        });
    }
}
