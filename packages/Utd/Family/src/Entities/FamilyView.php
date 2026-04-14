<?php

namespace Utd\Family\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TimestampsWithTimezone;

class FamilyView extends Model
{
    use HasFactory, TimestampsWithTimezone;
}
