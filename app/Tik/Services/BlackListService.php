<?php

namespace App\Tik\Services;

use App\Tik\Repositories\BlackLisRepository;

class BlackListService
{
    public function __construct(
        private readonly BlackLisRepository $Repository,

    ) {}


    public function list($key,$perPage,$page){
        return $this->Repository->list($key,$perPage,$page);
    }

    // public function search($key){
    //     return $this->Repository->search($key);
    // }
    // public function blocked_search($user_id,$key){
    //     return $this->Repository->blocked_search($user_id,$key);
    // }

    public function store(array  $data){
        return $this->Repository->store($data);
    }

    public function black_lists($userid,$key,$perPage,$page){
        return $this->Repository->black_lists($userid,$key,$perPage,$page);
    }

    public function delete($id){
        return $this->Repository->delete($id);
    }

}
