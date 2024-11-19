<?php

namespace Modules\Moment\Entities;

use App\Models\Gift;
use App\Models\User;
use Database\Factories\MomentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','description','img'];
    protected $table = 'moment';
    protected $guarded = ['id'];



    public function comments()
    {
        return $this->hasMany( MomentCommint::class, 'moment_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany( MomentLikes::class, 'moment_id', 'id');
    }
    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'moment_user_gifts');
    }


    public function user()
    {
        return $this->belongsTo(User::class,);
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'moment_user_gifts')->withPivot('num', 'created_at','updated_at');
    // }

    public function scopeLikeExists($query, $userId)
    {
        return $query->withExists(['likes' => function($query) use($userId){
            $query->where('user_id', $userId);
        } ]);
    }

   // public
   protected static function newFactory()
   {
       return MomentFactory::new();
   }


}
