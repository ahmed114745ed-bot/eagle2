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
        $user =Auth::user();
        logger('Headers:', $request->headers->all());
        logger('Request Data:', $request->all());
    
        if ($request->hasHeader('x-notification-id')) {
            $notificationId = $request->header('x-notification-id');
            $user->notification_id = $notificationId;
            $user->save();
        }

        $items = HomeCarousel::query()
        
       
        ->where('enable', 1)->orderBy('sort');
        if ($request->type != null) {
            $items = $items->where('type', $request->type);
        }
        if ($request->category == 'charge_event') {
            $items = $items->where('event_type', 'charge_event');
        }
        $items = $items->get();
        $data = HomeCarouselResource::collection($items);
        return Common::apiResponse(1, '', $data);
    }
}
