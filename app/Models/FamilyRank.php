<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyRank extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "family_ranks";

    public function family()
    {
        return $this->belongsTo(Family::class, 'family_id');
    }
}
