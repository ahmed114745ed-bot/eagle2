<?php

namespace App\Classes\Gifts;

use App\Models\Pk;
use Carbon\Carbon;
use App\Models\Gift;
use App\Models\Room;
use App\Models\User;
use App\Models\Family;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\AppFeature;
use App\Models\FamilyRank;
use App\Models\FamilyLevel;
use Illuminate\Support\Facades\DB;
use App\Classes\Enums\NotificationType;
use App\Services\RoomCalculationService;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\SendCustomOfficialMessageToUser;

class SendGiftService
{

    public function sendGift($number, Room $room, Gift $gift, User $senderUser, User $receivedUser, $isPlay = 0, $isPK = 0, $totalPrice = null, $platformObtain = null,)
    {

        if ($totalPrice == null) $totalPrice = $gift->price * $number;

        $info = $this->getGiftLogData($gift, $room, $number, $totalPrice, $senderUser, $receivedUser, $isPlay, isPk: $isPK);
        GiftLog::query()->create($info);



    }

    public function sendGift2($number, Room $room, Gift $gift, User $senderUser, Collection $receivedUsers, $isPlay = 0, $totalPrice = null, $isPk = false, $cpId = null)
    {
        if ($totalPrice == null) $totalPrice = $gift->price * $number;
        $data = [];
        foreach ($receivedUsers as $receivedUser) {
            $info = $this->getGiftLogData($gift, $room, $number, $totalPrice, $senderUser, $receivedUser, $isPlay, isPk: $isPk, cpId: $cpId);
            $data[] = $info;
        }
        DB::table('gift_logs')->insert($data);
    }

    public function sendGift3($number, Room $room, Gift $gift, User $senderUser, Collection $receivedUsers, $isPlay = 0, $totalPrice = null, $isPk = false, array $cpIds = null)
    {
        if ($totalPrice == null) $totalPrice = $gift->price * $number;
        $data = [];
        foreach ($receivedUsers as $receivedUser) {
            $cpId = @$cpIds[$receivedUser->id] ?? null;
            $info = $this->getGiftLogData($gift, $room, $number, $totalPrice, $senderUser, $receivedUser, $isPlay, isPk: $isPk, cpId: $cpId);
            $data[] = $info;
        }
        DB::table('gift_logs')->insert($data);
    }

    public function calculate($uid, $toUid, $total)
    {
        $room_user = DB::table('users')->select(['id', 'is_sign', 'scale', 'is_leader'])->where('id', $uid)->first();
        if (!$room_user) {
            throw new \Exception('room owner not found');
        }
        $room_scale = Common::getConfig('platform_share');
        $room_scale = $room_scale ? $room_scale : 0;//Platform share
        if (!$room_user->is_sign) {                                                                         //non-contract homeowner
            $data['uid']      =
                0;                                                                                          //Room running water
            $data['toUid']    =
                $total * ((100 - $room_scale) / 100);                                                       //recipient
            $data['platform'] = $total * ($room_scale / 100);                                               //Platform flow
            $data['uid_yj']   = 0;                                                                          //homeowner
        } else {
            //Room running water
            $stream = $total * $room_user->scale / 100;
            //platform
            $platform = $total * ($room_scale - $room_user->scale) / 100;
            if ($room_user->is_leader) {
                $scale =
                    DB::table('leaders')->where('uid', $uid)->where('user_id', $toUid)->where('status', 2)->value('scale') ?: 100;
            } else {
                $scale = 100;
            }
            //recipient
            $room_scale_sign = (100 - $room_scale) / 100;
            $get_gift        = $total * ($room_scale_sign * $scale / 100);
            $uid_yj          = $total * ($room_scale_sign * (100 - $scale) / 100);

            $data['uid']      = $stream;  //Room running water
            $data['toUid']    = $get_gift;//recipient
            $data['platform'] = $platform;//Platform flow
            $data['uid_yj']   = $uid_yj;  //homeowner
        }
        $data = array_map(function ($val) {
            //            $gvic = Common::getConf ('gift_value_in_coins')?:0.1;
            $gvic = 1;
            return round($val * $gvic, 2);
        }, $data);
        return $data;



    }
    public function updateFamilyLevel(Family &$family, $totalCoins)
{
    //get total giftlogs
    $this->updateFamilyModel($totalCoins, $family);

}

    /**
     * @param $totalCoinsPerUser
     * @param mixed $family
     * @return void
     */
    public function updateFamilyModel($totalCoinsPerUser, Family $family): void
    {
        $family->total_diamond += $totalCoinsPerUser;
        $familyId              = $family->id;
        $this->updateOrCreateFamilyRank($familyId, $totalCoinsPerUser);

        $level =
            FamilyLevel::query()->where('exp', '<=', $family->total_diamond)->orderByDesc('exp')->first();
        if ($level) {
            if ($level->id != $family->current_level_id) {
                dispatch(new SendCustomOfficialMessageToUser($familyId, NotificationType::FAMILY))->onQueue('notification_heavy');
            }
        }
        $family->save();
    }

    /**
     * @param mixed $familyId
     * @param $totalCoinsPerUser
     * @return void
     */
    public function updateOrCreateFamilyRank(mixed $familyId, $totalCoinsPerUser, ?Carbon $day = null): void
    {
        if ($day == null) $day = now();
        $familyRank = FamilyRank::query()->where('family_id', $familyId)
                                ->where('day', $day->day)
                                ->where('month', $day->month)
                                ->where('year', $day->year)
                                ->first();

        if ($familyRank) {
            $familyRank->coins += $totalCoinsPerUser;
            $familyRank->save();
        } else {
            $this->createFamilyRank($familyId, $totalCoinsPerUser);
        }
    }

    /**
     * @param mixed $familyId
     * @param $totalCoinsPerUser
     * @return void
     */
    public function createFamilyRank(mixed $familyId, $totalCoinsPerUser): void
    {
        FamilyRank::query()->create([
                                        'family_id' => $familyId,
                                        'day'       => now()->day,
                                        'month'     => now()->month,
                                        'year'      => now()->year,
                                        'coins'     => $totalCoinsPerUser
                                    ]);
    }

    public function updateFamilyLevelForReceiver(\Illuminate\Database\Eloquent\Collection $users, $totalCoinsPerUser): bool
    {
        $families    = $users->pluck('family')->where('id', '!=', null);
        $familiesIds = $families->pluck('id')->toArray();
        if (count($familiesIds) == 0) return false;
        $repeatedData = $this->getDuplication($familiesIds);

        foreach ($repeatedData as $data) {
            $family = $families->where('id', $data['id'])->first();
            $this->updateFamilyModel($totalCoinsPerUser * $data['count'], $family);
        }

        return true;
    }

    /**
     * @param array $familiesIds
     * @return array
     */
    public function getDuplication(array $familiesIds): array
    {
        $repeatedData = [];

        foreach ($familiesIds as $familiesId) {
            $isFound = false;
            foreach ($repeatedData as $index => $data) {
                if ($data['id'] == $familiesId) {
                    $isFound                       = true;
                    $repeatedData[$index]['count'] += 1;
                    break;
                }
            }

            if (!$isFound) {

                $repeatedData[] = ['id' => $familiesId, 'count' => 1];
            }
        }
        return $repeatedData;
    }

    public function updatePkScoresAndSendToZego($pk, $userId, $roomId, $receivedIds, $giftPrice, $microphone)
    {
        if (!($pk instanceof Pk)) return;

        $m      = explode(',', $microphone);
        $mic_1  = isset($m[1]) ? $m[1] : 0;
        $mic_2  = isset($m[2]) ? $m[2] : 0;
        $mic_3  = isset($m[3]) ? $m[3] : 0;
        $mic_4  = isset($m[4]) ? $m[4] : 0;
        $mic_5  = isset($m[5]) ? $m[5] : 0;
        $mic_6  = isset($m[6]) ? $m[6] : 0;
        $mic_7  = isset($m[7]) ? $m[7] : 0;
        $mic_8  = isset($m[8]) ? $m[8] : 0;
        $team_1 = [$mic_1, $mic_2, $mic_5, $mic_6];
        $team_2 = [$mic_3, $mic_4, $mic_7, $mic_8];
        $t1     = implode(',', $team_1);
        $t2     = implode(',', $team_2);

        foreach ($receivedIds as $toUid) {
            if (in_array($toUid, $team_1)) {
                $pk->t1_score += $giftPrice;
            } elseif (in_array($toUid, $team_2)) {
                $pk->t2_score += $giftPrice;
            }
        }
        $pk->team_1 = $t1;
        $pk->team_2 = $t2;
        $pk->save();

        $ms = [
            'messageContent' => [
                "message"            => "updatePk",
                "PkTime"             => Carbon::parse($pk->end_at)->diffInMinutes(now()),
                "scoreTeam1"         => $pk->t1_score,
                "scoreTeam2"         => $pk->t2_score,
                "percentagepk_team1" => $pk->t1_per,
                "percentagepk_team2" => $pk->t2_per
            ]
        ];

        return json_encode($ms);
    }

    public function updatePkScoresAndSendToZegoJob($pk, $userId, $roomId, $receivedIds, $giftPrice, $microphone)
    {
        if (!($pk instanceof Pk)) return;

        $m      = explode(',', $microphone);
        $mic_1  = @$m[1] ?? 0;
        $mic_2  = @$m[2] ?? 0;
        $mic_3  = @$m[3] ?? 0;
        $mic_4  = @$m[4] ?? 0;
        $mic_5  = @$m[5] ?? 0;
        $mic_6  = @$m[6] ?? 0;
        $mic_7  = @$m[7] ?? 0;
        $mic_8  = @$m[8] ?? 0;
        $team_1 = [$mic_1, $mic_2, $mic_5, $mic_6];
        $team_2 = [$mic_3, $mic_4, $mic_7, $mic_8];
        $t1     = implode(',', $team_1);
        $t2     = implode(',', $team_2);

        foreach ($receivedIds as $toUid) {
            if (in_array($toUid, $team_1)) {
                $pk->t1_score += $giftPrice;
            } elseif (in_array($toUid, $team_2)) {
                $pk->t2_score += $giftPrice;
            }
        }
        $pk->team_1 = $t1;
        $pk->team_2 = $t2;
        $pk->save();

        $ms   = [
            'messageContent' => [
                "message"            => "updatePk",
                "PkTime"             => Carbon::parse($pk->end_at)->diffInMinutes(now()),
                "scoreTeam1"         => $pk->t1_score,
                "scoreTeam2"         => $pk->t2_score,
                "percentagepk_team1" => $pk->t1_per,
                "percentagepk_team2" => $pk->t2_per
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $roomId, $userId, $json);
    }


    /**
     * @param Gift $gift
     * @param Room $room
     * @param $number
     * @param mixed $totalPrice
     * @param User $senderUser
     * @param User $receivedUser
     * @param mixed $isPlay
     * @return array
     */
    public function getGiftLogData(Gift $gift, Room $room, $number, mixed $totalPrice, User $senderUser, User $receivedUser, mixed $isPlay, $isPk = false, $cpId = null): array
    {
        $appFeatureStatus = AppFeature::where('slug', 'room_gift_target')->value('status');
        $info['giftId']       = $gift->id;
        $info['roomowner_id'] = $room->uid;
        $info['giftNum']      = $number;
        $info['giftName']     = $gift->name ?: '_';
        $info['giftPrice']    = $totalPrice;
        $info['sender_id']    = $senderUser->id;
        $info['receiver_id']  = $receivedUser->id;
        $info['is_play']      = $isPlay ? 2 : 1;
        $info['type']         = 2;
        $info['created_at']   = $info['updated_at'] = date('Y-m-d H:i:s', time());

        $info['platform_obtain']  = 0.0;                          //platform
        $info['receiver_obtain']  = $totalPrice;                  //recipient
        $info['roomowner_obtain'] = floor($totalPrice * 0.03);    //homeowner

        $info['agency_id']          = $receivedUser->agency_id;//homeowner
        $info['receiver_family_id'] = @$receivedUser->family_id;         //homeowner
        $info['sender_family_id']   = @$senderUser->family_id;
        $info['pk']   = @$isPk ?? false;
        $info['cp_id']   = $cpId;
        $info['room_id']   = $room->id;
        $info['room_gift_status'] = $appFeatureStatus ?? false;



        return $info;
    }

    public function updatePkScoresAndSendToZegoJob2($pk, $receivedIds, $giftPrice, $microphone) :  array
    {
        if(!($pk instanceof Pk)) return [];

        $m = explode (',',$microphone);
        $mic_1 = isset($m[1])?$m[1]:0;
        $mic_2 = isset($m[2])?$m[2]:0;
        $mic_3 = isset($m[3])?$m[3]:0;
        $mic_4 = isset($m[4])?$m[4]:0;
        $mic_5 = isset($m[5])?$m[5]:0;
        $mic_6 = isset($m[6])?$m[6]:0;
        $mic_7 = isset($m[7])?$m[7]:0;
        $mic_8 = isset($m[8])?$m[8]:0;
        $mic_9 = isset($m[9])?$m[9]:0;
        $team_1 = [$mic_2,$mic_3,$mic_6,$mic_7];
        $team_2 = [$mic_4,$mic_5,$mic_8,$mic_9];
        $t1 = implode (',',$team_1);
        $t2 = implode (',',$team_2);

        foreach ($receivedIds as $toUid) {
            if (in_array($toUid, $team_1)) {
                $pk->t1_score += $giftPrice;
            } elseif (in_array($toUid, $team_2)) {
                $pk->t2_score += $giftPrice;
            }
        }
        $pk->team_1 = $t1;
        $pk->team_2 = $t2;
        $pk->save();

        return ["end_at" => $pk->end_at, 't1_score' => $pk->t1_score, 't2_score' => $pk->t2_score];
    }
}
