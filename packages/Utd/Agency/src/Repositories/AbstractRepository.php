<?php

namespace Utd\Agency\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    public function __construct(protected Model $model) {}

    final public function updateOrCreate(array $condition, array $data): mixed
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    final public function update(array $data, $id): mixed
    {
        if ($id instanceof Model) {
            return $id->update($data);
        }

        return $this->model->findOrFail($id)->update($data);
    }

    final public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    final public function insert(array $data): mixed
    {
        return $this->model->insert($data);
    }

    public function delete($id): mixed
    {
        if ($id instanceof Model) {
            return $id->delete();
        }

        return $this->model->findOrFail($id)->delete();
    }

    /**
     * @param  array  $data
     * @return Builder[]|Collection
     */
    final public function getAll(): Collection|array
    {
        return $this->model->all();
    }

    /**
     * @return Builder|Builder[]|Collection|Model|null
     */
    final public function findById(int $userId)
    {
        return $this->model->find($userId);
    }

    /**
     * @return Builder|Builder[]|Collection|Model
     */
    final public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param  int  $page
     * @param  int  $limit
     */
    final public function paginate($page, $limit = null): mixed
    {
        $query = $this->model->query();
        if ($limit) {
            $query->limit($limit);
        }

        return $query->paginate($page);
    }

    final public function paginateWhere(array $condition, int $per = 10): mixed
    {
        return $this->model->query()->where($condition)->paginate($per);
    }

    /**
     * @return Builder|Model|object|null
     */
    final public function findOrFailByWhere(array $where)
    {
        return $this->model->query()->where($where)->firstOrFail();
    }

    final public function exists(array $condition): bool
    {
        return $this->model->query()->where($condition)->exists();
    }

    /**
     * @return Builder|Model|object|null
     */
    final public function firstByWhere(array $where)
    {
        return $this->model->query()->where($where)->first();
    }
}
