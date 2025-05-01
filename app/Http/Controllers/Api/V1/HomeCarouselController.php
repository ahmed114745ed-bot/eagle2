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
        if ($request->has('notification_id')) {  // تحقق من وجود notification_id في الطلب
            $user->notification_id = $request->notification_id; // تعيين الـ notification_id للمستخدم
            $user->save();  // حفظ التغييرات
            return response()->json(['success' => true, 'message' => 'Notification ID updated successfully']);
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
