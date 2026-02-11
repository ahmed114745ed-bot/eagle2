<?php

namespace Utd\Family\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyView extends Model
{
    use HasFactory, TimestampsWithTimezone;
}
