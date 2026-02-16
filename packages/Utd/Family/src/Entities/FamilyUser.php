<?php

namespace Utd\Family\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Utd\Family\Traits\TimestampsWithTimezone;

class FamilyUser extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $table = 'family_user';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(family_model('user'));
    }
}
