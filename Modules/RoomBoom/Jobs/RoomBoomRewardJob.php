<?php

namespace Modules\RoomBoom\Jobs;

use App\Events\RoomBoomRewardsEvent;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\GiftLog;
use App\Models\Room;
use App\Models\User;
use App\Models\UserGift;
use App\Models\Ware;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Achievement\Entities\UserAchievementLevel;
use Modules\RoomBoom\Entities\RoomBoom;
use Modules\RoomBoom\Entities\RoomBoomReward;
use Modules\RoomBoom\Transformers\RoomBoomRewardResource;

class RoomBoomRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $boomId;
    protected Collection $users;
    protected array $giftInsertData = [];
    protected array $achievementInsertData = [];
    protected array $assignedUserIds = [];
    protected array $assignments = [];
    protected array $winnerData = [];
    protected array $achievementNotifications = [];
    protected array $giftNotifications = [];
    public function __construct($boomId)
    {
        $this->boomId = $boomId;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        info('in room boom reward job');

        $boom = RoomBoom::with(['roomBoomLevel', 'totalRoomGift'])->find($this->boomId);
        $level = $boom->roomBoomLevel;
        $roomId = $boom->totalRoomGift->room_id;
        if (!$boom || !$boom->roomBoomLevel || !$boom->totalRoomGift || !$roomId) return;

        $rewards = RoomBoomReward::where('room_boom_level_id', $level->id)->orderBy('priority')->get();

        $rewardItems = [];

        $this->getRewardItems($rewards, $rewardItems);

        $topContributorIds = $this->getTopContributorIds($roomId, $level->level);

        $lastTriggerSenderId = GiftLog::where('id', $boom->final_gift_id)->value('sender_id');

        $room = Room::select('id')->with('roomVisitors:id,user_id,room_id')->find($roomId);

        if ($room) {
            $allUserIds = array_merge($topContributorIds, [$lastTriggerSenderId], $room->roomVisitors->pluck('user_id')->toArray());
        }

        $this->users = User::whereIn('id', $allUserIds)->select(['id', 'notification_id'])->get()->keyBy('id');

        $this->distributeTopContributors($topContributorIds, $rewardItems);

        $this->distributeLastTriggerSender($lastTriggerSenderId, $topContributorIds, $rewards);

        $this->distributeVisitorRewards($rewardItems, $room);

        $this->sendEvent($level->level, $roomId);

        $this->bulkInsertGiftsAchievements();

        $this->dispatchPendingNotifications();
    }

    /**
     * @throws \Exception
     */
    public function distributeTopContributors($topContributorIds, &$rewardItems): void
    {
        foreach ($topContributorIds as $i => $userId) {
            $reward = $this->getNextAvailableReward($rewardItems);
            if (!$reward) break;

            $this->distributeBoomRewards($userId, $reward);
            $this->assignWinnerData($userId, $reward);
        }
    }

    /**
     * @throws \Exception
     */
    public function distributeLastTriggerSender($lastTriggerSenderId, $topContributorIds, $rewards): void
    {
        if ($lastTriggerSenderId && !in_array($lastTriggerSenderId, $topContributorIds)) {
            if ($rewards->isNotEmpty()){
                $randomReward = $rewards->random();

                $rewardArray = [
                    'id'          => $randomReward->id,
                    'target_type' => $randomReward->target_type,
                    'target'      => $randomReward->target,
                    'expire_days' => $randomReward->expire_days,
                    'priority'    => $randomReward->priority,
                    'quantity'    => 1,
                ];

                $this->distributeBoomRewards($lastTriggerSenderId, $rewardArray);
                $this->assignWinnerData($lastTriggerSenderId, $rewardArray);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function distributeVisitorRewards(&$rewardItems, $room): void
    {
        $visitorIds = $room->roomVisitors()
            ->whereNotIn('user_id', $this->assignedUserIds)
            ->inRandomOrder()
            ->pluck('user_id')
            ->toArray();

        foreach ($visitorIds as $i => $visitorId){
            $reward = $this->getNextAvailableReward($rewardItems);
            if (!$reward) break;

            $this->distributeBoomRewards($visitorId, $reward);

            $this->assignWinnerData($visitorId, $reward);
        }
    }

    /**
     * @throws \Exception
     */
    public function distributeBoomRewards($userId, $reward): void
    {
        info($userId);
        $user = $this->users[$userId] ?? null;
        $token = $user->notification_id;

        if ($user){
            $expire = $reward['expire_days'];
            if ($reward['target_type'] == 'ware') {
                $ware = Ware::find($reward['target']);
                UserCommon::addEvintsWareToUser($user, $ware, $expire);
            }
            if ($reward['target_type'] == 'achieve') {
                $this->achievementRewards($reward['target'], $expire, $userId, $token);
            }

            if ($reward['target_type'] == 'gift') {
                $this->giftRewards($reward, $userId, $expire, $token);
            }
        }
    }

    public function achievementRewards($rewardTarget, $expire, $userId, $token): void
    {
        $dateTimestamp = $expire ? Carbon::parse($expire)->format('Y-m-d H:i:s') : null;
        $title = __('Achievement Reward');
        $body = __('You have received a new achievement.');
        $this->achievementInsertData[] = [
            'user_id' => $userId,
            'custom_image' => $rewardTarget,
            'end_at' => $dateTimestamp,
            'created_at' => now(),
            'updated_at' => now()
        ];
        Common::sendOfficialMessage($userId, $title, $body);

        if ($token) {
            $this->achievementNotifications['title'] = $title;
            $this->achievementNotifications['body'] = $body;
            $this->achievementNotifications['tokens'][] = $token;
        }
    }

    public function giftRewards($reward, $userId, $expire, $token): void
    {
        $title = __('Gift Reward');
        $body = __('You have received a new gift.');

        $giftData = [
            'gift_id' => $reward['target'],
            'user_id' => $userId,
            'quantity' => 1,
            'expire' => $expire ?? 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $this->giftInsertData[] = $giftData;
        Common::sendOfficialMessage($userId, $title, $body);
        if ($token) {
            $this->giftNotifications[$reward['target']]['title'] = $title;
            $this->giftNotifications[$reward['target']]['body'] = $body;
            $this->giftNotifications[$reward['target']]['tokens'][] = $token;
        }
    }

    public function getRewardItems($rewards, &$rewardItems): void
    {
        foreach ($rewards as $reward) {
            $rewardItems[] = [
                'id'          => $reward->id,
                'target_type' => $reward->target_type,
                'target'      => $reward->target,
                'expire_days' => $reward->expire_days,
                'priority'    => $reward->priority,
                'quantity'    => $reward->quantity,
            ];
        }
    }

    protected function getNextAvailableReward(&$rewardItems)
    {
        foreach ($rewardItems as &$reward) {
            if ($reward['quantity'] > 0) {
                $reward['quantity']--;
                return $reward;
            }
        }
        return null;
    }

    public function getTopContributorIds($roomId, $levelColumn): array
    {
        return GiftLog::query()
            ->select('sender_id',
                DB::raw('SUM(giftPrice) as total_gift'),
                DB::raw('MIN(created_at) as first_contribution')
            )
            ->where('room_id', $roomId)
            ->where('room_boom_level', $levelColumn)
            ->where('start_boom_ranking', 1)
            ->where('created_at', '>=', Carbon::today())
            ->groupBy('sender_id')
            ->orderByDesc('total_gift')
            ->orderBy('first_contribution', 'asc')
            ->limit(3)
            ->pluck('sender_id')
            ->toArray();
    }

    public function assignWinnerData($userId, $reward): void
    {
        $this->assignedUserIds[] = $userId;
        $this->assignments[] = $reward;

        $this->winnerData[] = [
            'user_id' => $userId,
            'image'   => (new RoomBoomRewardResource((object)$reward))->getImageUrl(),
            'image_type' => (new RoomBoomRewardResource((object)$reward))->getGiftImageType(),
        ];
    }

    public function sendEvent($levelColumn, $roomID): void
    {
        $data = [
            "message" => "roomBoomEnded",
            'roomBoomLevel' => $levelColumn,
            'duration' => 10,
            'winners' => $this->winnerData
        ];

        event(new RoomBoomRewardsEvent($data, $roomID));
    }

    public function bulkInsertGiftsAchievements(): void
    {
        if (!empty($this->giftInsertData)) {
            UserGift::insert($this->giftInsertData);
        }
        if (!empty($this->achievementInsertData)) {
            UserAchievementLevel::insert($this->achievementInsertData);
        }
    }

    protected function dispatchPendingNotifications(): void
    {
        foreach ($this->achievementNotifications as $achievementId => $notification) {
            if (!empty($notification['tokens'])) {
                Common::send_firebase_notification(
                    $notification['tokens'],
                    $notification['title'],
                    $notification['body']
                );
            }
        }

        info($this->giftNotifications);
        foreach ($this->giftNotifications as $giftId => $notification) {
            if (!empty($notification['tokens'])) {
                Common::send_firebase_notification(
                    $notification['tokens'],
                    $notification['title'],
                    $notification['body']
                );
            }
        }
    }

}

//        $lastTriggerSenderId = GiftLog::where('room_id', $roomId)
//            ->where('room_boom_level', $level->level)
//            ->where('start_boom_ranking', 1)
//            ->orderByDesc('created_at')
//            ->value('sender_id');
