<?php

namespace Modules\Public\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCounter extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

}
