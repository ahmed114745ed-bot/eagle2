<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCarouselResource;
use App\Models\HomeCarousel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeCarouselController extends Controller
{
    public function index(Request $request)
    {
       
        $user = Auth::user();
        $displayType = $request->get('display_at'); 
        $timezone = getTimezone();
        $now = Carbon::now($timezone);
        $offset = $now->format('P');
        $country = request('country_id');
        if ($request->hasHeader('x-notification-id') && $user->notification_id !== $request->header('x-notification-id')) {
            $user->update(['notification_id' => $request->header('x-notification-id')]);
        }

        dd($country);
        $items = HomeCarousel::query()
        ->with(['user', 'room', 'generalRole', 'countriesLite', 'displays'])
        ->where('enable', 1)
    
        ->whereHas('displays', function ($sub) use ($now, $offset) {
            $sub->where(function ($inner) use ($now, $offset) {
                $inner->whereRaw("
                    CONVERT_TZ(home_carousel_displays.end_at, '+00:00', ?) > ?
                ", [$offset, $now])
                ->orWhere('home_carousel_displays.duration', 0);
            });
        })
    
        ->when($displayType, function ($q) use ($displayType) {
            $q->whereHas('displays', function ($sub) use ($displayType) {
                $sub->where('display_type', $displayType);
            });
        })
    
        ->when($country, function ($q) use ($country) {
            $q->whereHas('countries', function ($sub) use ($country) {
                $sub->where('country_id', $country);
            });
        })
    
        ->when($request->type, fn($q) => $q->where('type', $request->type))
        ->when($request->category === 'charge_event', fn($q) => $q->where('event_type', 'charge_event'))
    
        ->orderBy('sort')
        ->get();
    

        return Common::apiResponse(1, '', HomeCarouselResource::collection($items));
    
    }
}

