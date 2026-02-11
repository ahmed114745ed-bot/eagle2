<?php

namespace Utd\Family\Repositories;

use Utd\Family\Entities\Family;
use App\Tik\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;

class FamilyRepository extends AbstractRepository
{

    public function __construct()
    {
        $model = new Family;
        parent::__construct($model);

        if (!$this->model instanceof Family) return;
    }

    public function getWithSearch($search = null)
    {
        $conditions = [['status', 1]];
        if (!is_null($search))   $conditions[] =  ['name', 'like', "%$search%"];
        return $this->getPaginate(
            conditions: $conditions,
            with: ['members' => fn ($q) => $q->limit(10)],
            withCount: ['members', 'usersRequests']
        );
    }

    public function findById($id)
    {
        return $this->model->query()->find($id);
    }

    // public function create($data)
    // {
    //     return $this->model->create([
    //         'name' => $data['name'],
    //         'introduce' => $data['introduce'],
    //         'notice' => $data['notice'],
    //         'user_id' => $data['user_id'],
    //         'num'     => $data['num'],
    //         'image' => $data['image'],
    //         'is_success' => $data['is_success'],

    //     ]);
    // }

    public function findByUserId($userId)
    {
      return  $this->model->query()->where('user_id', $userId)->first();
    }

    public function searchUserFamily($key, $page, $perPage)
    {
        return \App\Models\User::whereHas('family')
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%')
                    ->orWhere('user_id', 'like', '%' . $key . '%');
            })
            ->select('id', 'name', 'user_id', 'avatar')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function delete($family)
    {
        $family->delete();
        return true;
    }
}
