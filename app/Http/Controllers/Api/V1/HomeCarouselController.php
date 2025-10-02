<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCarouselResource;
use App\Models\HomeCarousel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeCarouselController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $displayAt = request('display_at');

        if ($request->hasHeader('x-notification-id') && $user->notification_id !== $request->hasHeader('x-notification-id')) {
            $user->update(['notification_id' => $request->header('x-notification-id')]);
        }

        $items = HomeCarousel::query()->with('user','room','generalRole','countriesLite')
                ->when($displayAt, function ($q) use ($displayAt) {
                    $q->where(function ($sub) use ($displayAt) {            
                        $sub->where('display_at', $displayAt)
                            ->orWhere(function ($query) use ($displayAt) {
                                $query->whereRaw("JSON_VALID(display_at) and JSON_CONTAINS(display_at, '\"$displayAt\"')");
                            });
                    });
                })
            ->where('enable', 1)
            ->orderBy('sort')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->category === 'charge_event', fn($q) => $q->where('event_type', 'charge_event'))
            ->get();

        return Common::apiResponse(1, '', HomeCarouselResource::collection($items));
    }
}
