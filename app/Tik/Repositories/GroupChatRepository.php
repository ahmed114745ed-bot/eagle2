<?php

namespace App\Tik\Repositories;


use App\Models\GroupChat;

class GroupChatRepository extends AbstractRepository
{

    
    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new GroupChat());
    }

    public function getWithPaginate()
    {
        return $this->model->whereHas('user')->with('user','children')->orderBy('created_at', 'DESC')->paginate(10);
    }
}
