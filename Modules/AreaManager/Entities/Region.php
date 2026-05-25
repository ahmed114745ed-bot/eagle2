<?php
namespace Modules\AreaManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name', 'manager_id'];

    /**
     * Validation rules for creating/updating regions
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255|unique:regions,name',
            'manager_id' => 'required|exists:area_managers,id',
        ];
    }

    /**
     * Validation rules for updating (excluding unique check on same record)
     */
    public static function updateValidationRules(int $id): array
    {
        return [
            'name' => 'required|string|min:2|max:255|unique:regions,name,' . $id,
            'manager_id' => 'required|exists:area_managers,id',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(AreaManager::class, 'manager_id');
    }

    public function regionCountries(): HasMany
    {
        return $this->hasMany(RegionCountry::class, 'region_id');
    }

    public function countries()
    {
        return $this->hasManyThrough(
            \App\Models\Country::class, 
            RegionCountry::class,       
            'region_id',               
            'id',                       
            'id',                       
            'country_id'                
        );
    }
}
