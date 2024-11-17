<?php

namespace Modules\Tasks\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Request;

class DailyTask extends Model
{
    protected $fillable = ['day_id','title_ar','type','sub_type','count','total_points','created_at', 'title_en'];
    use HasFactory;
    public function getTitleAttribute()
    {
        $locale = Request::header('X-Localization','en');
        return $locale === 'ar' ? $this->title_ar  : $this->title_en;
    }
}
