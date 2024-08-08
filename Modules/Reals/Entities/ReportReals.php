<?php

namespace Modules\Reals\Entities;

use Illuminate\Database\Eloquent\Model;

class ReportReals extends Model
{
    protected $fillable = [];
    protected $guarded = [];
    public function reel()
    {
        return $this->hasOne(Real::class, 'id', 'real_id');
    }
    // protected $table = ['Report_reals'];
}
