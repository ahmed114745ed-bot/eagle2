<?php

namespace Modules\CP\Http\Services;

use App\models\User;
use App\Models\Room;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Models\Vip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\CP\Entities\CpInfo;
use Modules\CP\Entities\CpUser;
use Modules\CP\Helpers\CpCustomNotification;

class CpServices
{
    private array $expPercentages;

    public function __construct()
    {
        $this->expPercentages = Config::get('exp_percentages') ?? [1, 1, 1];

    }

    /**
     * @param User $sender
     * @param User $receiver
     * @param int $giftId
     * @param int $giftPrice
     * @return int|null The ID of the CpInfo model, or null if no CpInfo is found.
     * @throws \Exception
     */
    public function processCpWhenSendGift(User $sender, User $receiver, int $giftId, int $giftPrice): int | null
    {
        //check is ring gift or no
        $isRingGift = (Common::getConf('cp') ?? 0) == $giftId;

        $senderId = $sender->id;
        $receiverId = $receiver->id;

        if ($isRingGift) {
            //check is invite or response (invite if no had cp or receiver id not invite him,
            // response if receiver invite him )
            $cpUsers = $this->getAcceptInviteToCp($senderId, $receiverId);
           // \Log::info(json_encode($cpUsers));
            $cpInfo = $this->makeCp($senderId, $receiverId, cpInvitation: $cpUsers);
            return $cpInfo->id;
        }

        //get cp with this two users
        $cpInfo = $this->getCpFromUsers($senderId, $receiverId);

        //if gift is not a ring gift
        //and sender and receiver had same cp then update exp for them and level
        if ($cpInfo) {
            $this->upgradeLevelAndExp($cpInfo, $giftPrice);
            return $cpInfo->id;
        }
        return null;

    }

    private function getCpFromUsers(int $senderId, int $receiverId): null|CpInfo
    {
        return CpInfo::where(fn($q) => $q->where(['user_one_id' => $senderId, "user_two_id" => $receiverId])->orWhere(['user_one_id' => $receiverId, "user_two_id" => $senderId]))
            ->where("status", 1)
            ->first();
    }

    private function makeInvitation(int $senderId, int $receiverId): Model
    {
        return CpUser::create([
            'user_one_id' => $senderId,
            'user_two_id' => $receiverId,
            "status" => 0,
        ]);
    }

    public function getAcceptInviteToCp(int $senderId, int $receiverId): CpUser|null
    {
        return CpUser::where(fn($q) => $q->where(['user_one_id' => $receiverId, "user_two_id" => $senderId])->orWhere(['user_one_id' => $senderId, "user_two_id" => $receiverId]))
            ->where("status", 0)
            ->where("created_at", '>=', now()->subDay())
            ->first();
    }

    /**
     * @param $userOneId
     * @param $userTwoId
     * @param null $cpInvitation
     * @return CpInfo|null
     * @throws \Exception
     */
    public function makeCp($userOneId, $userTwoId, $cpInvitation = null): CpInfo | null
    {
        $userOneAvailable = $this->userAvailableCpInfo($userOneId);
        $userTwoAvailable = $this->userAvailableCpInfo($userTwoId);

        if (!$userOneAvailable) throw new \Exception(__("api.already_in_cp"));
        if (!$userTwoAvailable) throw new \Exception(__("api.user_not_available"));

        $cpInvitation = $cpInvitation ?? $this->getCpUserInvitation($userOneId, $userTwoId);
        if ($cpInvitation && $cpInvitation->user_two_id == $userOneId) {
            return $this->createCp($userOneId, $userTwoId, $cpInvitation);
        }else if($cpInvitation && $cpInvitation->user_one_id == $userOneId){
            throw new \Exception(__("api.already_send_before"));
        }  else {
            $this->makeInvitation($userOneId, $userTwoId);
        }

        return null;

    }

    public function userAvilable($user_id)
    {
        $data = CpUser::query()->where(fn($q) => $q->where("user_one_id", $user_id)->orWhere("user_two_id", $user_id))
            ->where("status", '!=', 0)
            ->first();
        return !($data != null);
    }

    public function userAvailableCpInfo($user_id)
    {
        $data = CpInfo::query()->where(fn($q) => $q->where("user_one_id", $user_id)->orWhere("user_two_id", $user_id))
            ->where("status", '!=', 0)
            ->first();
        return !($data != null);
    }

    public function sendZegoMap(User $user, Room $room)
    {
        $users = explode(',', $room->microphone);
        $checkUserInCp = CpInfo::query()->where(fn($q) => $q->where("user_one_id", $user->id)->orWhere("user_two_id", $user->id))
            ->where("status", 1)
            ->first();

        if ($checkUserInCp != null) {
            $userTwoID = $this->getUserTwo($user->id, $checkUserInCp);
            if (!in_array($userTwoID, $users)) {
              //  Log::info("send zego map");
                $ms = [
                    'messageContent' => [
                        "message" => "cpUpMicRoom",
                        'user_id' => $userTwoID
                    ]
                ];
                $json = json_encode($ms);
                Common::sendToZego('SendCustomCommand', @$room->id, $user->id, $json);
            }
        }
    }

    public function ranking($type = "today")
    {
        $data = GiftLog::whereHas("gift", function ($q) {
            $q->where("type", 8);
        })
            ->whereHas("cp", function ($q) {
                $q->where("status", 1);
            })
            ->whereNotNull("cp_id")
            ->with('cp')
            ->selectRaw('SUM(giftPrice) AS total_gift_num, cp_id')
            ->groupBy('cp_id')
            ->orderByDesc('total_gift_num');

        switch ($type) {
            case "today":
                $data->whereDate('created_at', today());
                break;
            case "week":
                $data->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case "month":
                $data->whereBetween('created_at', [now()->startOfMonth(), now()]);
                break;
        }

        return $data->take(15)->get();
    }

    public function upgradeLevelAndExp(?CpInfo $cp, $diamonds = null): bool
    {
        if (!$cp) return false;

        $cp->exp += $diamonds;
        $cp->last_active_date = now();
        $total = intval($cp->exp) * $this->expPercentages[2];
        $level = $this->getLevel($total);
        if ($level != null) {
            $cp->level = $level->level - $cp->sub_level;
        }
        $cp->save();
        return true;
    }

    public function cancelation(User $user)
    {
        $user = User::first();
        $cp = $this->getCp($user->id);
        if ($cp) {
            $cp->status = 0;
            $cp->save();
            (new CpCustomNotification())->cancelCp($user);
        }
    }

    public function getUserTwo($userId,CpInfo $checkUserInCp)
    {
        return ($checkUserInCp->user_one_id == $userId ? $checkUserInCp->user_two_id : $checkUserInCp->user_one_id);
    }

    public function getCp($userId)
    {
        $checkUserInCp = CpInfo::query()->where(fn($q) => $q->where("user_one_id", $userId)->orWhere("user_two_id", $userId))
            ->where("status", 1)
            ->first();
        return $checkUserInCp;
    }

    public function getLevel(int $totalCoins)
    {
        return Vip::query()->where(['type' => 3])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first();
    }

    /**
     * @param $userOneId
     * @param $userTwoId
     * @return CpUser|Model|null
     */
    public function getCpUserInvitation($userOneId, $userTwoId): null|CpUser|Model
    {
        return CpUser::where(fn($q) => $q->where(['user_one_id' => $userOneId, "user_two_id" => $userTwoId])->orWhere(['user_one_id' => $userTwoId, "user_two_id" => $userOneId]))
            ->where("status", 0)
            ->first();
    }

    /**
     * @param $userOneId
     * @param $userTwoId
     * @param mixed $checkInvite
     * @return CpInfo|null
     */
    public function createCp($userOneId, $userTwoId, CpUser $checkInvite): CpInfo | null
    {
        $checkInvite->delete();
        return CpInfo::create([
            "user_one_id" => $userOneId,
            "user_two_id" => $userTwoId,
            "status" => 1,
            "last_active_date" => now(),
        ]);
    }
}
