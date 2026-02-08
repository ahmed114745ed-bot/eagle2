<?php

namespace Utd\Agency\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


abstract class AbstractRepository
{
    /**
     * @param Model $model
     */
    public function __construct(protected Model $model)
    {
    }

    /**
     * @param array $condition
     * @param array $data
     * @return mixed
     */
    public function updateOrCreate(array $condition, array $data): mixed
    {
        return $this->model->updateOrCreate($condition,$data);
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id): mixed
    {
        if ($id instanceof Model){
            return $id->update($data);
        }
        return $this->model->findOrFail($id)->update($data);
    }


    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data): mixed
    {
        return $this->model->insert($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id): mixed
    {
        if ($id instanceof Model){
            return $id->delete();
        }
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * @param array $data
     * @return Builder[]|Collection
     */
    public function getAll(): Collection|array
    {
        return $this->model->all();
    }

    /**
     * @param int $userId
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function findById(int $userId)
    {
        return $this->model->find($userId);
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model
     */
    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function paginate($page, $limit = null): mixed
    {
        $query = $this->model->query();
        if ($limit) $query->limit($limit);
        return $query->paginate($page);
    }

    /**
     * @param array $condition
     * @param int $per
     * @return mixed
     */
    public function paginateWhere(array $condition, int $per = 10): mixed
    {
        return $this->model->query()->where($condition)->paginate($per);
    }

    /**
     * @param array $where
     * @return Builder|Model|object|null
     */
    public function findOrFailByWhere(array $where)
    {
        return $this->model->query()->where($where)->firstOrFail();
    }

    /**
     * @param array $condition
     * @return bool
     */
    public function exists(array $condition): bool
    {
        return $this->model->query()->where($condition)->exists();
    }

    /**
     * @param array $where
     * @return Builder|Model|object|null
     */
    public function firstByWhere(array $where)
    {
        return $this->model->query()->where($where)->first();
    }
}
