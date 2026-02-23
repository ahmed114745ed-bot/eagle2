<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface OvipRepositoryContract
{
    /**
     * Get list of all OVips with basic fields
     */
    public function getOvip(): Collection;

    /**
     * Get OVips by level with id, level, background_img
     */
    public function getOvipByLevel(): Collection;

    /**
     * Get OVips sorted by level with privileges relationship
     */
    public function getBySortLevel(): Collection;

    /**
     * Find Ovip by ID
     */
    public function findById(int $id);

    /**
     * Get all Ovips with privileges relationship
     */
    public function getAllWithPrivileges();

    /**
     * Find VIP by ID
     */
    public function findVipById($vipId);
}
