<?php

namespace Utd\AreaManager\Entities;

use Illuminate\Database\Eloquent\Model;

class AreaPolygon extends Model
{
    protected $fillable = [
        'area_manager_id',
        'coordinates',
        'covered_countries'
    ];

    protected $casts = [
        'coordinates' => 'array',
        'covered_countries' => 'array'
    ];

    public function areaManager()
    {
        return $this->belongsTo(AreaManager::class, 'area_manager_id');
    }
}