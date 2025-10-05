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
        $displayAt = request('display_at');
        $timezone = getTimezone();

        $now = Carbon::now($timezone);



        if ($request->hasHeader('x-notification-id') && $user->notification_id !== $request->hasHeader('x-notification-id')) {
            $user->update(['notification_id' => $request->header('x-notification-id')]);
        }

        $items = HomeCarousel::query()->with('user', 'room', 'generalRole', 'countriesLite')
            ->when($displayAt, function ($q) use ($displayAt) {
                $q->where(function ($sub) use ($displayAt) {
                    $sub->where('display_at', $displayAt)
                        ->orWhere(function ($query) use ($displayAt) {
                            $query->whereRaw("JSON_VALID(display_at) and JSON_CONTAINS(display_at, '\"$displayAt\"')");
                        });
                })->orWhere("display_$displayAt", 1);
            })
            ->where('enable', 1)
            // ->where(function($q) use ($now) {
            //     $q->where(function($sub) use ($now) {
            //         $sub->where('form', 1) // ساعات
            //             ->whereRaw("DATE_ADD(created_at, INTERVAL input HOUR) > ?", [$now]);
            //     })
            //     ->orWhere(function($sub) use ($now) {
            //         $sub->where('form', 2) // أيام
            //             ->whereRaw("DATE_ADD(created_at, INTERVAL input DAY) > ?", [$now]);
            //     })
            //     ->orWhere(function($sub) use ($now) {
            //         $sub->where('form', 3) // شهور
            //             ->whereRaw("DATE_ADD(created_at, INTERVAL input MONTH) > ?", [$now]);
            //     })
            //     ->orWhere('form', 0); // إذا form = 0 اعتبرها أبدية
            // })

            ->where(function ($q) use ($now) {
                $q->where(function ($sub) use ($now) {
                    $sub->where('form', 1) // ساعات
                        ->where(function ($inner) use ($now) {
                            $inner->whereRaw("DATE_ADD(created_at, INTERVAL input HOUR) > ?", [$now])
                                ->orWhere('input', '>', $now->timestamp); // if input is timestamp
                        });
                })
                    ->orWhere(function ($sub) use ($now) {
                        $sub->where('form', 2) // أيام
                            ->where(function ($inner) use ($now) {
                                $inner->whereRaw("DATE_ADD(created_at, INTERVAL input DAY) > ?", [$now])
                                    ->orWhere('input', '>', $now->timestamp); // if input is timestamp
                            });
                    })
                    ->orWhere(function ($sub) use ($now) {
                        $sub->where('form', 3) // شهور
                            ->where(function ($inner) use ($now) {
                                $inner->whereRaw("DATE_ADD(created_at, INTERVAL input MONTH) > ?", [$now])
                                    ->orWhere('input', '>', $now->timestamp); // if input is timestamp
                            });
                    })
                    ->orWhere('form', 0); // أبدية
            })

            ->orderBy('sort')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->category === 'charge_event', fn($q) => $q->where('event_type', 'charge_event'))
            ->get();

        return Common::apiResponse(1, '', HomeCarouselResource::collection($items));
    }
}
