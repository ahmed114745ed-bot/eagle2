<?php

namespace Modules\Moment\Entities;

use Illuminate\Database\Eloquent\Model;

class ReportMoment extends Model
{
    protected $fillable = [];
    protected $guarded = [];

    // protected $table = ['Report_reals'];

    public function moment()
    {
        return $this->hasOne(Moment::class, 'id', 'moment_id');
    }
}

