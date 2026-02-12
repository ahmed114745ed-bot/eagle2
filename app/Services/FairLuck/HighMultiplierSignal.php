<?php

namespace App\Services\FairLuck;

class HighMultiplierSignal
{
    public function __construct(
        public float $score,
        public ?int $rank,
        public ?int $preferredFloor,
        public array $priorityMultipliers,
        public float $probabilityFloor,
        public array $weightBoosts,
        public bool $forceJackpot
    ) {
    }

    public static function empty(float $score = 0.0, ?int $rank = null): self
    {
        return new self($score, $rank, null, [], 0.0, [], false);
    }

    public function isActive(): bool
    {
        return $this->preferredFloor !== null;
    }

    public function hasPriority(int $multiplier): bool
    {
        return in_array($multiplier, $this->priorityMultipliers, true);
    }

    public function weightFor(int $multiplier): float
    {
        return $this->weightBoosts[$multiplier] ?? 1.0;
    }
}
