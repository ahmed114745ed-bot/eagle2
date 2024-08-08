<?php

namespace App\Tik\Repositories;

use App\Models\RequestBackgroundImage;

class RequestBackgroundImageRepository extends AbstractRepository
{
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new RequestBackgroundImage());
    }

    // public function create($data)
    // {
    //     return $this->model->create([
    //         'owner_room_id' => $data['owner_room_id'],
    //         'img' => $data['img'],
    //         'status' => $data['status'],
    //         'price' => $data['price'],
    //     ]);
    // }

    public function findByUserId($userId)
    {
        return $this->model->query()->where('owner_room_id',$userId)->whereIn('status',[1,3])->select('id','img')->get();
    }
}
