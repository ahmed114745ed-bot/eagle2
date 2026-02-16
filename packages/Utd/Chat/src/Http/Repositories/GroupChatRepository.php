<?php

namespace Utd\Chat\Http\Repositories;

use Utd\Chat\Entities\GroupChat;

class GroupChatRepository
{
    protected GroupChat $model;

    public function __construct()
    {
        $this->model = new GroupChat();
    }

    public function create(array $data): GroupChat
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?GroupChat
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): GroupChat
    {
        return $this->model->findOrFail($id);
    }

    public function update(array $data, int $id): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getWithPaginate(int $perPage = 10)
    {
        return $this->model->whereHas('user')->with([
            'user.profile:id,user_id,avatar,gender',
            'user.UserVip',
            'user.receiverLevel',
            'user.senderLevel',
            'user.packs.ware',
            'user.packs' => fn ($q) => $q->whereIn('type', [25, 18, 4])->where('is_used', true)->with('ware:id,value'),
            'parent.user.profile:id,user_id,avatar',
            'parent.user.UserVip',
            'parent.user.receiverLevel',
            'parent.user.senderLevel',
            'parent.user.packs.ware',
            'parent.user.packs' => fn ($q) => $q->whereIn('type', [25, 18, 4])->where('is_used', true)->with('ware:id,value'),
        ])->orderBy('created_at', 'DESC')->paginate($perPage);
    }

    public function getFiltered(array $filters = [], int $perPage = 10)
    {
        $query = $this->model->with(['user.profile', 'parent.user']);

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['user_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['user_name']}%");
            });
        }

        if (! empty($filters['uuid'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('uuid', 'like', "%{$filters['uuid']}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['country_id'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('country_id', $filters['country_id']);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
