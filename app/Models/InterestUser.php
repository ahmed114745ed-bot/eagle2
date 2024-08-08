<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestUser extends Model
{
    use HasFactory;
    protected $table = 'AddinterestsForUsers';
    protected $fillable = [
        'user_id',
        'interests'
    ];


    public function interests2()
    {
        return $this->belongsTo(Interest::class, 'interests', 'id'); // Correct the relationship keys
    }


}
