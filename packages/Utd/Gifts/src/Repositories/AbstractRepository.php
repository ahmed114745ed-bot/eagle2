<?php

namespace Utd\Gifts\Repositories;

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

    final public function update($id, array $data): mixed
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

    final public function findOrFail(int $id, array $relations = []): Model|Collection|Builder|array|null
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    final public function getPaginate(array $conditions = [], array $with = [], array $select = ['*'], array $orderBy = ['id' => 'desc'], array $withCount = []): mixed
    {
        return $this->prepareQuery($conditions, $with, $select)->withCount($withCount)
            ->when(! empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $key => $value) {
                    $query->orderBy($key, $value);
                }
            })->paginate(config('app.element_number_per_page'));
    }

    final public function getAll(array $conditions = [], array $with = [], array $select = ['*'], array $orderBy = ['id' => 'desc']): mixed
    {
        return $this->prepareQuery($conditions, $with, $select)
            ->when(! empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $key => $value) {
                    $query->orderBy($key, $value);
                }
            })->get();
    }

    final public function prepareQuery(array $conditions = [], array $with = [], array $select = []): Builder
    {
        return $this->model->with($with)->where($conditions)->select($select);
    }

    final public function searchWith(string $search, $column = 'name'): mixed
    {
        return $this->model->where($column, 'like', '%'.$search.'%')->select(['name as text', 'id'])->take(10)->get();
    }

    final public function delete($id)
    {
        $data = $this->model->find($id);
        if (! $data) {
            return false;
        }
        $data->delete();

        return true;
    }

    final public function deleteAth($id, $user_id = 0)
    {
        $data = $this->model->find($id);
        if (! $data) {
            return false;
        }

        $data->delete();

        return true;
    }
}
