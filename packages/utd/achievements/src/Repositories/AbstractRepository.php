<?php

namespace Utd\Achievements\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Repository for Achievement Package
 *
 * This is a STANDALONE copy - no dependency on base project.
 * The package is completely independent.
 */
abstract class AbstractRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id, array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(array $data, int|Model $id): bool
    {
        if ($id instanceof Model) {
            return $id->update($data);
        }
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function updateOrCreate(array $condition, array $data): Model
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function paginate(int $perPage = 15, array $with = []): mixed
    {
        return $this->model->with($with)->paginate($perPage);
    }

    public function where(array $conditions): Builder
    {
        return $this->model->where($conditions);
    }
}
