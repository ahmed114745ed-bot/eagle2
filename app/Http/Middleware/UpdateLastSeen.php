<?php

namespace App\Http\Middleware;

use App\Support\PackageHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Utd\Chat\Entities\ChatMessage;
use Utd\Chat\Http\Repositories\ChatRepository;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = 'user_last_seen_' . $user->id;

            // Update chat message statuses if chat package is installed
            if (PackageHelper::isInstalled('chat')) {
                $chatRepository = new ChatRepository();
                $chatsId = $chatRepository->getUserChatRooms($user->id);
                ChatMessage::whereIn('chat_room_id', $chatsId)
                    ->where('user_id', '!=', $user->id)
                    ->where('status', 'sended')
                    ->update(['status' => 'received']);
            }

            if (!Cache::has($cacheKey)) {
                $user->update([
                    'last_seen_at' => now(),
                    'online' => true,
                ]);

                Cache::put($cacheKey, true, now()->addMinutes(2));
            }
        }

        return $next($request);
    }
}
