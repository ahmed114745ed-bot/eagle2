<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface VipPrivilegeRepositoryContract
{
    /**
     * Get all vip privileges
     */
    public function all(): Collection;

    /**
     * Find vip privilege by id
     */
    public function findById(int $id): mixed;

    /**
     * Get list of VIP privileges for select/search components
     */
    public function listVip(?string $search = null): Collection;

    public function getAllPrivileges();
}
