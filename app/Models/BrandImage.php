<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandImage extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'brand_images';
}
