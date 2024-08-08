<?php

namespace Modules\SalaryTransaction\Entities;

use App\Classes\Facades\Agency;
use App\Models\Agency as ModelsAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeAgency extends Model
{
    use HasFactory;

    protected $guarded = ['id'] ;

    public function agency()
    {
        return $this->belongsTo(ModelsAgency::class);
    }
}
