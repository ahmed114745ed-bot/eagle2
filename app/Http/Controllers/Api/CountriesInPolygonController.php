<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CountriesInPolygonController extends Controller
{
    public function getCountriesInPolygon(Request $request)
    {
        $coordinates = $request->input('coordinates');
        
        if (empty($coordinates) || count($coordinates) < 3) {
            return response()->json(['countries' => []]);
        }

        $countries = $this->detectCountriesInPolygon($coordinates);

        return response()->json(['countries' => $countries]);
    }

  
    private function detectCountriesInPolygon($polygon)
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $detectedCountries = [];
        $countryCodes = [];

        $bounds = $this->calculateBounds($polygon);
        
        $testPoints = $this->generateTestPoints($polygon, $bounds, 20);

        foreach ($testPoints as $point) {
            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'latlng' => $point['lat'] . ',' . $point['lng'],
                    'key' => $apiKey,
                    'result_type' => 'country'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (!empty($data['results'])) {
                        foreach ($data['results'] as $result) {
                            foreach ($result['address_components'] as $component) {
                                if (in_array('country', $component['types'])) {
                                    $countryCode = $component['short_name'];
                                    
                                    if (!in_array($countryCode, $countryCodes)) {
                                        $countryCodes[] = $countryCode;
                                    }
                                }
                            }
                        }
                    }
                }
                
                usleep(100000); 
                
            } catch (\Exception $e) {
                continue;
            }
        }

        if (!empty($countryCodes)) {
            $countries = Country::whereIn('iso2', $countryCodes)
                ->orWhereIn('iso3', $countryCodes)
                ->get();

            foreach ($countries as $country) {
                $detectedCountries[] = [
                    'id' => $country->id,
                    'name' => $country->name,
                    'e_name' => $country->e_name ?? $country->name,
                    'iso2' => $country->iso2 ?? '',
                    'iso3' => $country->iso3 ?? '',
                    'phone_code' => $country->phone_code ?? ''
                ];
            }
        }

        return $detectedCountries;
    }

 
    private function calculateBounds($polygon)
    {
        $minLat = $maxLat = $polygon[0]['lat'];
        $minLng = $maxLng = $polygon[0]['lng'];

        foreach ($polygon as $point) {
            $minLat = min($minLat, $point['lat']);
            $maxLat = max($maxLat, $point['lat']);
            $minLng = min($minLng, $point['lng']);
            $maxLng = max($maxLng, $point['lng']);
        }

        return [
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng
        ];
    }

  
    private function generateTestPoints($polygon, $bounds, $gridSize = 20)
    {
        $testPoints = [];
        $latStep = ($bounds['maxLat'] - $bounds['minLat']) / $gridSize;
        $lngStep = ($bounds['maxLng'] - $bounds['minLng']) / $gridSize;

        $centerLat = ($bounds['minLat'] + $bounds['maxLat']) / 2;
        $centerLng = ($bounds['minLng'] + $bounds['maxLng']) / 2;
        if ($this->isPointInPolygon($centerLat, $centerLng, $polygon)) {
            $testPoints[] = ['lat' => $centerLat, 'lng' => $centerLng];
        }

        for ($lat = $bounds['minLat']; $lat <= $bounds['maxLat']; $lat += $latStep) {
            for ($lng = $bounds['minLng']; $lng <= $bounds['maxLng']; $lng += $lngStep) {
                if ($this->isPointInPolygon($lat, $lng, $polygon)) {
                    $testPoints[] = ['lat' => $lat, 'lng' => $lng];
                    
                    if (count($testPoints) >= 25) {
                        return $testPoints;
                    }
                }
            }
        }

        return $testPoints;
    }

 
    private function isPointInPolygon($lat, $lng, $polygon)
    {
        $vertices = count($polygon);
        $inside = false;

        for ($i = 0, $j = $vertices - 1; $i < $vertices; $j = $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}