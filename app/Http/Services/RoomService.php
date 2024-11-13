<?php

namespace App\Http\Services;

use App\Exceptions\RoomUserHandling\PermissionNotAllow;
use App\Facades\RoomHelper;
use App\Models\RequestBackgroundImage;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomService
{
    /**
     * @throws PermissionNotAllow
     * @throws \Exception
     */
    public function muteUserStatus(int $userId, Room $room, bool $isMute = true)
    {
        $admins   = $room->room_admin ?? '';
        $owner_id = $room->uid;

        if (!RoomHelper::checkUserIsAdminOrOwner($admins, $owner_id)) {
            throw new PermissionNotAllow(__('api_responses.you_dont_have_permission'));
        }

        $mutedUsersArr = ($room->muted_users != '') ? explode(',', $room->muted_users) : [];

        if ($isMute) {
            if (in_array($userId, $mutedUsersArr)) throw new \Exception(__('api_responses.user_already_muted'));
            $mutedUsersArr[] = $userId;
        } else {
            if (!in_array($userId, $mutedUsersArr)) throw new \Exception(__('api_responses.user_already_un_muted'));
            $mutedUsersArr = array_diff($mutedUsersArr, [$userId]);
        }
        $room->muted_users = implode(',', $mutedUsersArr);
        $room->save();

    }

    public function getRoomBackground(?Room $room)
    {
        if ($room == null) return '';
        if ($room->is_pk_custom) return PK_IMAGE;
        return 
            ((@RequestBackgroundImage::where('status', 1)->where('owner_room_id', $room->uid)->orderByDesc('id')->first())->img ??
                $room->room_background ??
                @DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img);
    }
}
