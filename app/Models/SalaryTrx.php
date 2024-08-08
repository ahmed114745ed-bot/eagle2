<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryTrx extends Model
{
    protected $table = 'salary_trxs';

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo (User::class,'oid');
    }

    public function agency(){
        return $this->belongsTo (Agency::class,'oid');
    }
}
