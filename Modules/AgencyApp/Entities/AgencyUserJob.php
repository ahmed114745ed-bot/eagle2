<?php

namespace Modules\AgencyApp\Entities;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyUserJob extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }
}
