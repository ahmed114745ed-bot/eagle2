<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;

use App\Models\configesModel;
use App\Models\Pk;
use App\Models\RequestBackgroundImage;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Modules\CP\Entities\CpRoomHistory;

class EnterRoomLiveCollection extends JsonResource
{


    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $pks     = $this->getRoomTwoLastPk($this->id);
        $topUser = $this->getTopUser($this->uid);

        request()->type = 1;
        $owner = $this->owner;
        $vip_level_img = Common::ovip_center_rank_img($owner->id);

        $cpRoomHistories = CpRoomHistory::where('room_id',$this->id)->get(['index1', 'index2']);

        $indices = $cpRoomHistories->map(function ($history) {
            return [$history->index1, $history->index2];
        })->toArray();

        /** @var User $owner*/
        return [
            "id"                  => $this->id, // room id
            "owner_id"            => $this->uid, // owner id
            "uuid"                => $owner?->uuid ?? '', // owner uuid
            "owner_name"          => @$owner->name ?? '', // owner name
            "owner_image"         => $owner->avatar ?: '', // owner image
            "giftPrice"           => $this->session_string ?: '', // gift price
            "password_status"     => !($this->room_pass == ""), // room password state
            "admins"              => explode(',', $this->room_admin ?? ''), // room admins
            "room_intro"          => $this->room_intro, // room intro
            "is_comment_closed"   => $this->is_comment_closed, // is Comments Closed
            "room_rule"           => Common::getConfig('room_rule' . (app()->getLocale() != 'ar' ? '_en' : '')), // room rules

        ];
    }

    private function getRoomTwoLastPk(int $roomId)
    {
        return Pk::query()
            ->where('room_id', $roomId)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();
    }


    /*   private function getBoxes()
    {
        return BoxUse::query()
                     ->with('user', fn($q) => $q->withoutAppends()->select(['id', 'name']))
                     ->where('room_uid', $this->uid)
                     ->where('not_used_num', '>', 0)
                     ->where('unused_coins', '>', 0)
                     ->whereDoesntHave('picks', function ($q) {
                         $q->where('user_id', $this->userId);
                     })
                     ->get();
    }*/

    private function getTopUser(int $roomOwner)
    {

        return @$this->topUser;
    }

    /**
     * @return mixed
     */
    public function getRoomBackground()
    {
        return $this->mode == '3' ? 'custom_image/back-black.png' : ((@RequestBackgroundImage::where('status', 1)->where('owner_room_id', $this->uid)->orderByDesc('id')->first())->img ??
            $this->room_background ??
            @DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img);
    }

    public function getOwnerSound($ownerId, $roomSound)
    {
        $roomSound = explode(',', $roomSound);
        return in_array($ownerId, $roomSound);
    }

    private function getBans($roomSpeak)
    {
        $roomSpeak = trim($roomSpeak);
        if ($roomSpeak == '') return [];
        $bans      = [];
        $uid_black = explode(',', trim($roomSpeak));
        foreach ($uid_black as $b) {
            $u      = explode('#', trim($b));
            $bans[] = $u[0];
        }
        return $bans;
    }

    private function getRoomVisitorCount($roomVisitors)
    {
        $roomVisitors = trim($roomVisitors);
        if ($roomVisitors == '') return 1;
        return count(explode(',', $roomVisitors)) + 1;
    }

    private function getMicrophones($microphones, $mainMicrophone)
    {
        $microphones = trim($microphones);
        $mainMicrophone = trim($mainMicrophone);
        if ($microphones == '') return [];
        $microphones = explode(',', $microphones);
        $mainMicrophone = explode(',', $mainMicrophone);
        $arr         = ['0', '-1', '-2'];

        // this users id with 1020#-1
        $usersIds = array_diff($microphones, $arr);

        if (count($usersIds) > 0) {
            //get all users with ids
            $users = User::withoutAppends()->with('profile')->whereIn('id', $usersIds)->select(['id', 'name'])->get();
        }

        for ($i = 0, $j = 0; $i < count($microphones); $i++) {
            $mic = $microphones[$i];
            if ($mic == '0') {
                $microphones[$i] = 'empty';
            } elseif ($mic == '-1') {
                $microphones[$i] = 'locked';
            } elseif ($mic == '-2') {
                $microphones[$i] = 'muted';
            } else {
                //                $microphones[$i] = $users[$j]->setAppends([])->toArray();
                $user = $users->where('id', $microphones[$i])->first();
                if ($user) {
                    $microphones[$i] = [
                        'id'   => $user->id,
                        'name' => $user->name,
                        'img'  => $user->profile?->avatar ?? '',
                        'seat_condition' => ($mainMicrophone[$i] == '0') ? 'empty' : ((($mainMicrophone[$i] == '-1') ? 'locked' : (($mainMicrophone[$i] == '-2') ? 'muted' : 'empty'))),
                    ];
                    $j++;
                } else {
                    $microphones[$i] = 'empty';
                }
            }
        }
        return $microphones;
    }

    private function getUserType($roomAdmin, $roomJudge)
    {
        $userType = 5;
        [$isAdminInRoom, $roomAdmin] = $this->getAdminAndType(trim($roomAdmin));
        //        [$isUserIsJudge, $roomJudge] = $this->getAdminAndType(trim($roomJudge));

        if ($isAdminInRoom) $userType = 2;
        //        if($isUserIsJudge) $userType = 4;

        return [$userType, $roomAdmin];
    }

    private function getAdminAndType($roomAdmins)
    {
        $isAdminInRoom = false;
        $roomAdmin     = explode(',', $roomAdmins ?? '');

        if (in_array((string)auth()->id(), $roomAdmin)) $isAdminInRoom = true;
        return [$isAdminInRoom, $roomAdmin];
    }

    /*private function getJudgeAndType($roomJudge)
    {
        $isUserIsJudge = false;
        $roomJudge     = explode(',', $roomJudge ?? '');

        if (in_array((string)$this->userId, $roomJudge)) $isUserIsJudge = true;
        return [$isUserIsJudge, $roomJudge];
    }*/
}
