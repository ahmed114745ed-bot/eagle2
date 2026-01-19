<?php

namespace Utd\Moments\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentGallery extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];
}
