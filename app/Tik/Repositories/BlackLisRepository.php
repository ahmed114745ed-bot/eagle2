<?php

namespace App\Tik\Repositories;

use App\Models\BlackList;
use App\Models\Interest;
use Illuminate\Support\Facades\DB;
use Modules\Moment\Entities\Moment;


class BlackLisRepository extends AbstractRepository
{
    
    public function __construct()
    {
        parent::__construct(new BlackList());
    }


    public function list(){
        return $this->model->select('id','user_id')->with('user:id,name,uuid')->paginate(10);
   
    }
   

    public function search($key){
        return $this->model->with('user')->whereHas('user', function ($query) use ($key) {
            $query->where('uuid', 'like', "%$key%")
            ->orWhere('name', 'like', "%$key%");
        })->get()->unique('user_id')->values();
        
    }

    public function blocked_search($key){
        return $this->model->with('blockedPerson')->whereHas('blockedPerson', function ($query) use ($key) {
            $query->where('uuid', 'like', "%$key%")
            ->orWhere('name', 'like', "%$key%");
        })->get()->unique('user_id')->values();
        
    }
    
    public function store( array $data){
           return $this->create($data);   
    }
    public function black_lists($userid){
        return $this->model->with('blockedPerson')->whereHas('user', function ($query) use ($userid) {
            $query->where('id', 'like', "%$userid%");
        })->get();   
    }

    public function delete($id){
        return $this->delete($id);   
    }
    
}
