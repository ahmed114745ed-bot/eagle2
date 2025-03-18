<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

// composer require matanyadaev/laravel-eloquent-spatial
class Zone extends Model
{
    use HasSpatial;

    protected $fillable = ['name', 'coordinates'];

    protected $casts = [
        'coordinates' => Polygon::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($zone) {
            // Check if coordinates are provided as a string (GeoJSON)
            if (is_string($zone->coordinates)) {
                // Decode the GeoJSON string into an array
                $geoJson = json_decode($zone->coordinates, true);

                // Check if GeoJSON is valid and a LineString
                if ($geoJson['type'] === 'LineString') {
                    // Close the polygon by adding the first coordinate at the end
                    $geoJson['coordinates'][] = $geoJson['coordinates'][0]; // Close the polygon
                    $geoJson['type'] = 'Polygon';
                }

                // Ensure the type is Polygon and coordinates are set
                if (!$geoJson || !isset($geoJson['coordinates']) || $geoJson['type'] !== 'Polygon') {
                    throw new \Exception('❌ الإحداثيات غير صالحة. يجب أن تكون من نوع Polygon.');
                }

                // Convert coordinates to Polygon format (lat, lng => lng, lat)
                $polygonCoordinates = [];
                foreach ($geoJson['coordinates'][0] as $coords) {
                    $polygonCoordinates[] = new Point($coords[1], $coords[0]); // Convert [lat, lng] to [lng, lat]
                }

                // Create a new Polygon object with the converted coordinates
                $zone->coordinates = new Polygon([new LineString($polygonCoordinates)]);
            }
        });
    }

}