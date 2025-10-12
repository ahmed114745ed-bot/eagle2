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

        if ($request->hasHeader('x-notification-id') && $user->notification_id !== $request->header('x-notification-id')) {
            $user->update(['notification_id' => $request->header('x-notification-id')]);
        }

        $items = HomeCarousel::query()
            ->with(['user', 'room', 'generalRole', 'countriesLite'])
            ->where('enable', 1)

            ->when($displayType, function ($q) use ($displayType, $now, $offset) {
                $q->whereHas('displays', function ($sub) use ($displayType, $now, $offset) {
                    $sub->where('display_type', $displayType)
                        ->where(function ($inner) use ($now, $offset) {
                            // $inner->whereRaw("
                            //     CONVERT_TZ(end_at, '+00:00', ?) > ?
                            // ", [$offset, $now]);
                            $inner->whereRaw("
                                CONVERT_TZ(end_at, '+00:00', ?) > ?
                                ", [$offset, $now])
                                ->orWhere('duration', 0); 
                        });
                });
            })

            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->category === 'charge_event', fn($q) => $q->where('event_type', 'charge_event'))
            ->orderBy('sort')
            ->get();

        return Common::apiResponse(1, '', HomeCarouselResource::collection($items));
    
    }
}

