<?php

namespace Utd\Vip\Repositories;

use App\Contracts\OvipRepositoryContract;
use App\Tik\Repositories\AbstractRepository;
use Illuminate\Support\Collection;
use Utd\Vip\Entities\OVip;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
class OvipRepository extends AbstractRepository implements OvipRepositoryContract
{
    public function __construct()
    {
        parent::__construct(new OVip());
    }

    /**
     * Get list of all OVips with basic fields
     */
    public function getOvip(): Collection
    {
        return $this->model
            ->newQuery()
            ->select('id', 'name', 'level', 'img', 'background_img')
            ->get();
    }

    public function getOvipByLevel(): Collection
    {
        return $this->model
            ->select('id', 'level', 'background_img')->orderBy('level', 'asc')
            ->get();
    }

    /**
     * Get OVips sorted by level with privileges relationship
     */
    public function getBySortLevel(): Collection
    {
        return $this->model
            ->newQuery()
            ->with('privilegs')
            ->orderBy('level')
            ->get();
    }

    /**
     * Find Ovip by ID with privileges
     */
    public function findById(int $id)
    {
        return $this->model->newQuery()->find($id);
    }

    public function getAllWithPrivileges()
    {
        return OVip::with('privilegs')->orderBy('level')->get();
    }

    public function findVipById($vipId)
    {
        return OVip::find($vipId);
    }
}
