<?php

namespace Modules\RoomCup\Http\Controllers\web;

use Carbon\Carbon;
use Encore\Admin\Grid;
use App\Models\GiftLog;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use App\Admin\Controllers\MainController;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomCup\Entities\RoomCupTarget;
use Modules\RoomCup\Entities\RoomCupReward;

class RoomCupDebugController extends MainController
{
    protected $title = 'Room Cup Debug';
    public $permission_name = 'room-cup-report';

    public function index(Content $content)
    {
        return parent::index($content
            ->header(__('Room Cup Debug - TotalRoomGift vs GiftLogs'))
            ->description(__('Compare TotalRoomGift with actual gift_logs to verify data accuracy'))
            ->body($this->grid()));
    }

    protected function grid()
    {
        $grid = new Grid(new TotalRoomGift());

        $grid->model()
            ->with([
                'room:id,room_name,room_cover,uid',
                'room.owner:id,name,uuid',
                'room.owner.profile:id,user_id,avatar',
            ])
            ->orderBy('created_at', 'desc');

        // Room Column
        $grid->column('room_id', __('Room'))->display(function () {
            $room = $this->room;
            if (!$room) {
                return "<span style='color:red'>Room Not Found (ID: {$this->room_id})</span>";
            }

            $path = $room->room_cover;
            $defaultImage = asset("images/room.jpg");
            $url = getImagePath($path) ?? $defaultImage;
            if (!isImageExists($url)) {
                $url = $defaultImage;
            }

            $showUrl = url("admin/rooms/{$room->id}");
            $ownerName = $room->owner->name ?? 'N/A';

            return "<div style='display: flex; align-items: center; gap: 10px;'>
                <img src='$url' alt='Room' style='width: 50px; height: 50px; object-fit: cover; border-radius: 6px;'>
                <div>
                    <a href='{$showUrl}' style='text-decoration: underline;'>{$room->room_name}</a><br>
                    <span style='color: #888; font-size: 12px;'>ID: {$room->id} | Owner: {$ownerName}</span>
                </div>
            </div>";
        });

        // TotalRoomGift current_total
        $grid->column('current_total', __('TotalRoomGift'))->display(function () {
            return "<strong style='color: #2196F3;'>" . number_format($this->current_total) . "</strong> 💎";
        });

        // Sum from gift_logs
        $grid->column('gift_logs_sum', __('GiftLogs Sum'))->display(function () {
            $tz = getTimezone();
            $createdAt = Carbon::parse($this->created_at);
            
            // Get the day boundaries in UTC
            $dayStart = $createdAt->copy()->startOfDay();
            $dayEnd = $createdAt->copy()->endOfDay();

            $sum = GiftLog::where('room_id', $this->room_id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('giftPrice');

            return "<strong style='color: #4CAF50;'>" . number_format($sum) . "</strong> 💎";
        });

        // Difference
        $grid->column('difference', __('Difference'))->display(function () {
            $tz = getTimezone();
            $createdAt = Carbon::parse($this->created_at);
            $dayStart = $createdAt->copy()->startOfDay();
            $dayEnd = $createdAt->copy()->endOfDay();

            $giftLogsSum = GiftLog::where('room_id', $this->room_id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('giftPrice');

            $diff = $this->current_total - $giftLogsSum;

            if ($diff == 0) {
                return "<span style='color: green;'>✅ Match</span>";
            } elseif ($diff > 0) {
                return "<span style='color: orange;'>⚠️ +" . number_format($diff) . "</span>";
            } else {
                return "<span style='color: red;'>❌ " . number_format($diff) . "</span>";
            }
        });

        // Visitors
        $grid->column('number_of_visitors', __('Visitors'))->display(function () {
            return $this->number_of_visitors ?? 0;
        });

        // Target Achieved
        $grid->column('target_achieved', __('Target Check'))->display(function () {
            $target = RoomCupTarget::where('total', '<=', $this->current_total)
                ->where('number_of_visitors', '<=', ($this->number_of_visitors ?? 0))
                ->orderByDesc('total')
                ->first();

            if ($target) {
                return "<span style='color: green;'>✅ Target #{$target->id}<br>
                    <small>Total: {$target->total}<br>
                    Owner: {$target->owner_profit}<br>
                    Admin: {$target->admin_profit}</small></span>";
            }

            return "<span style='color: gray;'>❌ No Target</span>";
        });

        // Rewards Distributed
        $grid->column('rewards', __('Rewards'))->display(function () {
            $rewards = RoomCupReward::where('total_room_gift_id', $this->id)->get();

            if ($rewards->isEmpty()) {
                return "<span style='color: gray;'>No Rewards</span>";
            }

            $html = "<div style='font-size: 11px;'>";
            foreach ($rewards as $reward) {
                $icon = $reward->type === 'owner' ? '👑' : '🛡️';
                $html .= "{$icon} User #{$reward->user_id}: <strong>{$reward->amount}</strong><br>";
            }
            $html .= "</div>";

            return $html;
        });

        // Created At
        $grid->column('created_at', __('Date'))->display(function () {
            return Carbon::parse($this->created_at)->format('Y-m-d H:i:s');
        });

        // Filters
        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->column(1 / 3, function ($filter) {
                $filter->equal('room_id', __('Room ID'));
            });

            $filter->column(1 / 3, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input) {
                        $tz = getTimezone();
                        $start = Carbon::parse(convertArabicToEnglishNumbers($this->input), $tz)
                            ->startOfDay()
                            ->setTimezone('UTC');
                        $query->where('created_at', '>=', $start);
                    }
                }, __('From Date'), 'from_date')->date();
            });

            $filter->column(1 / 3, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input) {
                        $tz = getTimezone();
                        $end = Carbon::parse(convertArabicToEnglishNumbers($this->input), $tz)
                            ->endOfDay()
                            ->setTimezone('UTC');
                        $query->where('created_at', '<=', $end);
                    }
                }, __('To Date'), 'to_date')->date();
            });
        });

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * API endpoint to get raw comparison data
     */
    public function compare(Request $request)
    {
        $tz = getTimezone();

        $fromDate = $request->input('from_date')
            ? Carbon::parse($request->input('from_date'), $tz)->startOfDay()->setTimezone('UTC')
            : Carbon::yesterday($tz)->startOfDay()->setTimezone('UTC');

        $toDate = $request->input('to_date')
            ? Carbon::parse($request->input('to_date'), $tz)->endOfDay()->setTimezone('UTC')
            : Carbon::yesterday($tz)->endOfDay()->setTimezone('UTC');

        $roomId = $request->input('room_id');

        $query = TotalRoomGift::query()
            ->with(['room:id,room_name,uid', 'room.owner:id,name'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($roomId, fn($q) => $q->where('room_id', $roomId))
            ->orderByDesc('current_total');

        $results = $query->get()->map(function ($gift) use ($fromDate, $toDate) {
            $dayStart = Carbon::parse($gift->created_at)->startOfDay();
            $dayEnd = Carbon::parse($gift->created_at)->endOfDay();

            $giftLogsSum = GiftLog::where('room_id', $gift->room_id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('giftPrice');

            $target = RoomCupTarget::where('total', '<=', $gift->current_total)
                ->where('number_of_visitors', '<=', ($gift->number_of_visitors ?? 0))
                ->orderByDesc('total')
                ->first();

            $rewards = RoomCupReward::where('total_room_gift_id', $gift->id)->get();

            return [
                'id' => $gift->id,
                'room_id' => $gift->room_id,
                'room_name' => $gift->room->room_name ?? 'N/A',
                'owner_name' => $gift->room->owner->name ?? 'N/A',
                'total_room_gift_value' => $gift->current_total,
                'gift_logs_sum' => $giftLogsSum,
                'difference' => $gift->current_total - $giftLogsSum,
                'is_match' => $gift->current_total == $giftLogsSum,
                'visitors' => $gift->number_of_visitors ?? 0,
                'target_achieved' => $target ? [
                    'id' => $target->id,
                    'total' => $target->total,
                    'owner_profit' => $target->owner_profit,
                    'admin_profit' => $target->admin_profit,
                ] : null,
                'rewards_distributed' => $rewards->map(fn($r) => [
                    'user_id' => $r->user_id,
                    'type' => $r->type,
                    'amount' => $r->amount,
                ]),
                'created_at' => $gift->created_at,
            ];
        });

        return response()->json([
            'period' => [
                'from' => $fromDate->toDateTimeString(),
                'to' => $toDate->toDateTimeString(),
                'timezone' => $tz,
            ],
            'summary' => [
                'total_records' => $results->count(),
                'total_with_targets' => $results->filter(fn($r) => $r['target_achieved'])->count(),
                'total_with_rewards' => $results->filter(fn($r) => $r['rewards_distributed']->isNotEmpty())->count(),
                'mismatches' => $results->filter(fn($r) => !$r['is_match'])->count(),
            ],
            'data' => $results,
        ]);
    }
}
