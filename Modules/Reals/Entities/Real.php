<?php

namespace Modules\Reals\Entities;

use App\Models\Interest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Real extends Model
{
    protected $fillable = [];

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Real $real){
            if ($real->description == null){
                $real->description = '';
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Interest::class, RealCategory::class, 'real_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany( RealUserComment::class, 'real_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany( RealUserLike::class, 'real_id', 'id');
    }
    public function Views()
    {
        return $this->hasMany( RealUserView::class, 'real_id', 'id');
    }


    public function user()
    {

        return $this->belongsTo(User::class,  'user_id');

    }
}
