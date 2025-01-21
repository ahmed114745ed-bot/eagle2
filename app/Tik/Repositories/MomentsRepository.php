<?php

namespace App\Tik\Repositories;

use App\Models\Interest;
use Modules\Moment\Entities\Moment;


class MomentsRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new Moment());
    }

    public function all($id)
    {
       return $this->model->when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar'])->get();
    }





    public function search($input)
    {
        $query = $this->model->query();

        $query->whereHas('user', function ($query) use ($input) {
            $query->where('uuid', trim($input)) ;
                 
        })->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar']);
        
        $result = $query->get(); 
        
        return $result ;
     
    }
}
