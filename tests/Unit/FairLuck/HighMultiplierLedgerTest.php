<?php

namespace Tests\Unit\FairLuck;

use App\Services\FairLuck\HighMultiplierLedger;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HighMultiplierLedgerTest extends TestCase
{
    private RedisArrayFake $redisFake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redisFake = new RedisArrayFake();
        Redis::swap($this->redisFake);
        $this->setFairLuckConfig();
    }

    public function test_user_unlocks_high_multipliers_when_score_and_pool_sufficient(): void
    {
        $ledger = $this->app->make(HighMultiplierLedger::class);
        $userId = 321;

        $this->feedLosses($ledger, $userId, 3);

        $signal = $ledger->evaluateEligibility($userId, 100);

        $this->assertTrue($signal->isActive());
        $this->assertSame(1, $signal->rank);
        $this->assertSame(250, $signal->preferredFloor);
        $this->assertSame([100, 250], $signal->priorityMultipliers);
        $this->assertGreaterThan(0.0, $signal->probabilityFloor);
        $this->assertTrue($signal->forceJackpot);
    }

    public function test_big_multiplier_win_resets_score_and_vacates_rank(): void
    {
        $ledger = $this->app->make(HighMultiplierLedger::class);
        $userId = 654;

        $this->feedLosses($ledger, $userId, 4);
        $this->assertTrue($ledger->evaluateEligibility($userId, 100)->isActive());

        $ledger->recordOutcome($userId, 100, 24900.0, true, 250);

        $signal = $ledger->evaluateEligibility($userId, 100);
        $this->assertFalse($signal->isActive());
        $this->assertSame(0.0, (float) $this->redisFake->get('fairluck_high_multiplier_pool'));
        $this->assertSame([], $ledger->leaderboard());
    }

    private function feedLosses(HighMultiplierLedger $ledger, int $userId, int $times, float $bet = 100.0): void
    {
        for ($i = 0; $i < $times; $i++) {
            $ledger->recordOutcome($userId, $bet, -$bet, false, 0);
        }
    }

    private function setFairLuckConfig(): void
    {
        config()->set('fairluck.high_multiplier', [
            'tiers' => [
                100 => 800.0,
                250 => 1200.0,
                500 => 5000.0,
            ],
            'probability_floors' => [
                100 => 0.1,
                250 => 0.2,
                500 => 0.3,
            ],
            'weight_boosts' => [
                100 => 5,
                250 => 10,
                500 => 20,
            ],
            'loss_score_weight' => 5.0,
            'bet_score_weight' => 0.0,
            'pool_contribution_rate' => 1.0,
            'pool_cover_ratio' => 0.01,
            'pool_payout_ratio' => 1.0,
            'big_win_penalty_weight' => 2.0,
            'big_win_floor_penalty' => 100.0,
            'small_win_penalty' => 0.2,
            'rank_window' => 10,
            'user_ttl' => 600,
            'leaderboard_limit' => 50,
            'pool_alert_floor' => 0,
            'pool_alert_ttl' => 60,
        ]);
    }
}

class RedisArrayFake
{
    private array $values = [];
    private array $hashes = [];
    private array $sortedSets = [];

    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        $this->values[$key] = $value;
    }

    public function setex(string $key, int $ttl, $value): void
    {
        $this->set($key, $value);
    }

    public function del(string ...$keys): void
    {
        foreach ($keys as $key) {
            unset($this->values[$key], $this->hashes[$key]);
            foreach ($this->sortedSets as $setKey => $set) {
                if (isset($set[$key])) {
                    unset($this->sortedSets[$setKey][$key]);
                }
            }
        }
    }

    public function incrbyfloat(string $key, float $value): float
    {
        $this->values[$key] = (float) ($this->values[$key] ?? 0) + $value;
        return $this->values[$key];
    }

    public function zscore(string $key, $member)
    {
        return $this->sortedSets[$key][$member] ?? null;
    }

    public function zincrby(string $key, float $increment, $member): float
    {
        $this->sortedSets[$key][$member] = ($this->sortedSets[$key][$member] ?? 0) + $increment;
        return $this->sortedSets[$key][$member];
    }

    public function zrevrank(string $key, $member)
    {
        if (!isset($this->sortedSets[$key][$member])) {
            return null;
        }

        $ordered = $this->orderedSet($key);
        $members = array_keys($ordered);
        $index = array_search($member, $members, true);

        return $index === false ? null : $index;
    }

    public function zrevrange(string $key, int $start, int $stop, array $options = [])
    {
        $ordered = $this->orderedSet($key);
        $slice = array_slice($ordered, $start, ($stop - $start) + 1, true);

        if (($options['withscores'] ?? false) === true) {
            return $slice;
        }

        return array_keys($slice);
    }

    public function zrem(string $key, $member): void
    {
        unset($this->sortedSets[$key][$member]);
    }

    public function hmset(string $key, array $data): void
    {
        $this->hashes[$key] = array_merge($this->hashes[$key] ?? [], $data);
    }

    public function hincrbyfloat(string $key, string $field, float $value): void
    {
        $this->hashes[$key][$field] = (float) ($this->hashes[$key][$field] ?? 0) + $value;
    }

    public function hincrby(string $key, string $field, int $value): void
    {
        $this->hashes[$key][$field] = (int) ($this->hashes[$key][$field] ?? 0) + $value;
    }

    public function hset(string $key, string $field, $value): void
    {
        $this->hashes[$key][$field] = $value;
    }

    public function expire(string $key, int $seconds): void
    {
        // TTL is ignored for the fake store.
    }

    private function orderedSet(string $key): array
    {
        $set = $this->sortedSets[$key] ?? [];
        arsort($set, SORT_NUMERIC);
        return $set;
    }
}
