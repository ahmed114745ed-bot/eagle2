<?php

namespace Utd\Family\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TimestampsWithTimezone;

class FamilyRank extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    protected $table = 'family_ranks';

    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }
}
