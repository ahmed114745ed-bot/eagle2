<?php

namespace Modules\SalaryTransaction\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SalaryTransaction\Database\factories\AdminCheckFactory;

class AdminCheck extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function request()
    {
        return $this->belongsTo(SalaryRequest::class,'request_id');
    }
}
