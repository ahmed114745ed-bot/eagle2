<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyJoinRequest extends Model
{
    protected $table = 'agency_join_requests';

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo (User::class);
    }

    public function agency(){
        return $this->belongsTo (Agency::class);
    }
    public function requsers()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }


    public function update(array $attributes = [], array $options = [])
    {
        if ($this->agency_id == 0) {
            $attributes['type_user'] = 0;
        }
    
        return parent::update($attributes, $options);
    }

    public function admin()
    {
        return $this->hasOn(Agent::class,"change_status_admin_id");
    }
}
