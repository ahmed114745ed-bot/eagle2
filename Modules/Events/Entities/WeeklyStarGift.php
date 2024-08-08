<?php

namespace Modules\Events\Entities;

use App\Models\Gift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklyStarGift extends Model
{
    use HasFactory;
    protected $fillable=['id','gift_id','weekly_star_id'];

    public function gifts()
    {
        return $this->hasMany(Gift::class,'gift_id');
    }
}
