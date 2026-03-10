<?php

use Modules\RoomCup\Http\Controllers\web\RoomCupTargetController;
use Modules\RoomCup\Http\Controllers\web\RoomCupSettingsController;
use Modules\RoomCup\Http\Controllers\web\RoomCupReportsController;
use  Modules\RoomCup\Console\CalculateRoomCupRewards;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(
    [
        'prefix'     => config('admin.route.prefix'),
        'middleware' => [
            'web',
            'admin',
            'adminIp',
            'multiLanguage',
             'check.allowed.app',
            // 'room.cup'
        ],
        'as'         => config('admin.route.prefix') . 'routes',
    ],
    function () {
        Route::resource('room-cup-target', RoomCupTargetController::class);
        Route::resource('room-cup-settings', RoomCupSettingsController::class);
        Route::post('room-cup-settings/save', [RoomCupSettingsController::class, 'save'])->name('room-cup-settings.save');

        Route::resource('room-cup-reports', RoomCupReportsController::class);
        
 
        
   
    }
);

Route::get('cup-targets-view', [RoomCupTargetController::class,'cupTargetHtml']);

Route::get('/roomcup/calculate-rewards', function () {

    Artisan::call('roomcup:calculate-rewards');

    $output = Artisan::output();

    return response()->json([
        'status'  => 'success',
        'message' => 'RoomCup rewards calculated',
        'output'  => $output,
    ]);
});

Route::get('/roomcup/diagnostic', function () {
    $settings = [];
    $default = [
        'enabled' => true,
        'interval' => 1,
        'type' => 'daily',
        'time' => '00:00',
        'day' => 0,
    ];

    foreach ($default as $key => $defaultValue) {
        $cacheKey = 'roomcup_' . $key;
        $value = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($value === null) {
            $setting = \App\Models\Setting::where('key', $cacheKey)->first();
            $value = $setting ? $setting->value : $defaultValue;
        }
        $settings[$key] = $value;
    }

    $type = $settings['type'] ?? 'daily';
    $tz = getTimezone();

    // Calculate the period (same logic as the command)
    switch ($type) {
        case 'weekly':
            $start = \Carbon\Carbon::now($tz)->subWeek()->startOfWeek();
            $end = \Carbon\Carbon::now($tz)->subWeek()->endOfWeek();
            break;
        case 'monthly':
            $start = \Carbon\Carbon::now($tz)->subMonth()->startOfMonth();
            $end = \Carbon\Carbon::now($tz)->subMonth()->endOfMonth();
            break;
        default: // daily
            $start = \Carbon\Carbon::yesterday($tz)->startOfDay();
            $end = \Carbon\Carbon::yesterday($tz)->endOfDay();
            break;
    }

    // Get all targets ordered by total
    $targets = \Modules\RoomCup\Entities\RoomCupTarget::orderBy('total')->get();

    if ($targets->isEmpty()) {
        return response()->json(['error' => 'No targets configured'], 400);
    }

    $minTarget = $targets->first();

    // Get all rooms' aggregated gifts in this period
    if ($type === 'daily') {
        $roomGifts = \Modules\RoomBoom\Entities\TotalRoomGift::whereBetween('created_at', [$start, $end])
            ->select(
                'room_id',
                \DB::raw('SUM(current_total) as total_gifts'),
                \DB::raw('SUM(number_of_visitors) as total_visitors'),
                \DB::raw('COUNT(*) as gift_records')
            )
            ->groupBy('room_id')
            ->orderByDesc('total_gifts')
            ->get();
    } else {
        $roomGifts = \Modules\RoomBoom\Entities\TotalRoomGift::whereBetween('created_at', [$start, $end])
            ->select(
                'room_id',
                \DB::raw('SUM(current_total) as total_gifts'),
                \DB::raw('SUM(number_of_visitors) as total_visitors'),
                \DB::raw('COUNT(*) as gift_records')
            )
            ->groupBy('room_id')
            ->orderByDesc('total_gifts')
            ->get();
    }

    // Get existing rewards in the CURRENT period (duplicate check uses current period)
    switch ($type) {
        case 'weekly':
            $rewardStart = \Carbon\Carbon::now($tz)->startOfWeek();
            $rewardEnd = \Carbon\Carbon::now($tz)->endOfWeek();
            break;
        case 'monthly':
            $rewardStart = \Carbon\Carbon::now($tz)->startOfMonth();
            $rewardEnd = \Carbon\Carbon::now($tz)->endOfMonth();
            break;
        default:
            $rewardStart = \Carbon\Carbon::now($tz)->startOfDay();
            $rewardEnd = \Carbon\Carbon::now($tz)->endOfDay();
            break;
    }

    $existingRewards = \Modules\RoomCup\Entities\RoomCupReward::whereBetween('created_at', [$rewardStart, $rewardEnd])
        ->get()
        ->groupBy('room_id');

    // Also get rewards from the gift period (in case they were created during that time)
    $periodRewards = \Modules\RoomCup\Entities\RoomCupReward::whereBetween('created_at', [$start, $end])
        ->get()
        ->groupBy('room_id');

    $results = [];
    $shouldHaveWon = [];
    $actuallyWon = [];
    $almostWon = [];

    foreach ($roomGifts as $rg) {
        $room = \App\Models\Room::find($rg->room_id);
        $roomName = $room ? ($room->room_name ?? $room->name ?? 'N/A') : 'ROOM NOT FOUND';
        $ownerId = $room ? $room->uid : null;
        $ownerUser = $ownerId ? \App\Models\User::find($ownerId) : null;
        $ownerName = $ownerUser ? ($ownerUser->name ?? $ownerUser->nickname ?? 'N/A') : 'N/A';

        // Find matching target (same logic as command)
        $matchedTarget = \Modules\RoomCup\Entities\RoomCupTarget::where('total', '<=', $rg->total_gifts)
            ->where('number_of_visitors', '<=', $rg->total_visitors)
            ->orderByDesc('total')
            ->first();

        // Check if rewards were actually given
        $hasReward = isset($existingRewards[$rg->room_id]) || isset($periodRewards[$rg->room_id]);
        $rewardDetails = $existingRewards[$rg->room_id] ?? $periodRewards[$rg->room_id] ?? collect();

        // Find why it might have failed
        $failReasons = [];
        if (!$room) {
            $failReasons[] = 'Room not found in rooms table';
        }
        if (!$matchedTarget) {
            // Find the closest target they didn't reach
            $closestByTotal = \Modules\RoomCup\Entities\RoomCupTarget::where('total', '>', $rg->total_gifts)
                ->orderBy('total')
                ->first();
            $closestByVisitors = \Modules\RoomCup\Entities\RoomCupTarget::where('number_of_visitors', '>', $rg->total_visitors)
                ->where('total', '<=', $rg->total_gifts)
                ->orderBy('number_of_visitors')
                ->first();

            if ($closestByTotal) {
                $diff = $closestByTotal->total - $rg->total_gifts;
                $failReasons[] = "Needs {$diff} more coins to reach target #{$closestByTotal->id} (required: {$closestByTotal->total})";
            }
            if ($closestByVisitors) {
                $diff = $closestByVisitors->number_of_visitors - $rg->total_visitors;
                $failReasons[] = "Needs {$diff} more visitors to reach target #{$closestByVisitors->id} (required: {$closestByVisitors->number_of_visitors} visitors)";
            }
            if (empty($failReasons)) {
                $failReasons[] = "Below minimum target: needs total >= {$minTarget->total} AND visitors >= {$minTarget->number_of_visitors}";
            }
        }
        if ($matchedTarget && !$hasReward) {
            $failReasons[] = 'TARGET MET but NO REWARD found - possible bug or command not run';
        }

        $entry = [
            'room_id' => $rg->room_id,
            'room_name' => $roomName,
            'owner_id' => $ownerId,
            'owner_name' => $ownerName,
            'total_gifts' => round($rg->total_gifts, 2),
            'total_visitors' => (int) $rg->total_visitors,
            'gift_records_count' => (int) $rg->gift_records,
            'matched_target_id' => $matchedTarget ? $matchedTarget->id : null,
            'matched_target_total' => $matchedTarget ? $matchedTarget->total : null,
            'matched_target_visitors' => $matchedTarget ? $matchedTarget->number_of_visitors : null,
            'should_have_won' => $matchedTarget !== null,
            'actually_got_reward' => $hasReward,
            'reward_count' => $rewardDetails->count(),
            'reward_total_amount' => $rewardDetails->sum('amount'),
            'fail_reasons' => $failReasons,
        ];

        $results[] = $entry;

        if ($matchedTarget) {
            $shouldHaveWon[] = $entry;
            if ($hasReward) {
                $actuallyWon[] = $entry;
            }
        } else {
            $almostWon[] = $entry;
        }
    }

    // Sort almost won by total_gifts descending (closest to winning first)
    usort($almostWon, fn($a, $b) => $b['total_gifts'] <=> $a['total_gifts']);

    return response()->json([
        'diagnostic_info' => [
            'period_type' => $type,
            'gift_period' => ['start' => $start->toDateTimeString(), 'end' => $end->toDateTimeString()],
            'reward_check_period' => ['start' => $rewardStart->toDateTimeString(), 'end' => $rewardEnd->toDateTimeString()],
            'timezone' => $tz,
            'settings' => $settings,
            'targets_configured' => $targets->map(fn($t) => [
                'id' => $t->id,
                'required_total' => $t->total,
                'required_visitors' => $t->number_of_visitors,
                'owner_profit' => $t->owner_profit,
                'admin_profit' => $t->admin_profit,
                'number_of_admins' => $t->number_of_admins,
            ]),
        ],
        'summary' => [
            'total_rooms_with_gifts' => count($results),
            'rooms_should_have_won' => count($shouldHaveWon),
            'rooms_actually_got_reward' => count($actuallyWon),
            'rooms_missed_reward' => count($shouldHaveWon) - count($actuallyWon),
            'rooms_did_not_qualify' => count($almostWon),
        ],
        'rooms_should_have_won_but_didnt' => array_values(array_filter($shouldHaveWon, fn($r) => !$r['actually_got_reward'])),
        'rooms_that_won' => $actuallyWon,
        'rooms_almost_won' => array_slice($almostWon, 0, 20),
        'all_rooms' => $results,
    ]);
});
