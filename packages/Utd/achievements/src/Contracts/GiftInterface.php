<?php

namespace Utd\Achievements\Contracts;

/**
 * Gift Model Contract
 *
 * If your application has gifts that can trigger achievements,
 * the Gift model should implement this interface.
 */
interface GiftInterface
{
    public function getId(): int;
    public function getAchievement();
}
