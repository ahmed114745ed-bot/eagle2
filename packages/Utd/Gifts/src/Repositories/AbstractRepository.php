<?php

namespace Utd\Gifts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    public function __construct(protected Model $model) {}

    public function updateOrCreate(array $condition, array $data): mixed
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    public function update($id, array $data): mixed
    {
        if ($id instanceof Model) {
            return $id->update($data);
        }

        return $this->model->findOrFail($id)->update($data);
    }

    public function create(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function insert(array $data): mixed
    {
        return $this->model->insert($data);
    }

    public function findOrFail(int $id, array $relations = []): Model|Collection|Builder|array|null
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    public function getPaginate(array $conditions = [], array $with = [], array $select = ['*'], array $orderBy = ['id' => 'desc'], array $withCount = []): mixed
    {
        return $this->prepareQuery($conditions, $with, $select)->withCount($withCount)
            ->when(! empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $key => $value) {
                    $query->orderBy($key, $value);
                }
            })->paginate(config('app.element_number_per_page'));
    }

    public function getAll(array $conditions = [], array $with = [], array $select = ['*'], array $orderBy = ['id' => 'desc']): mixed
    {
        return $this->prepareQuery($conditions, $with, $select)
            ->when(! empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $key => $value) {
                    $query->orderBy($key, $value);
                }
            })->get();
    }

    public function prepareQuery(array $conditions = [], array $with = [], array $select = []): Builder
    {
        return $this->model->with($with)->where($conditions)->select($select);
    }

    public function delete($id)
    {
        $data = $this->model->find($id);
        if (! $data) {
            return false;
        }
        $data->delete();

        return true;
    }
}
