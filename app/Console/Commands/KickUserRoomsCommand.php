<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\RoomVisitor;
use App\Facades\UserHandling;
use App\Models\TimeEnterRoom;
use App\Models\RealtimeProject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Modules\CP\Entities\CpRoomHistory;
use App\Traits\Salaries\UserSalaryTrait;
use Modules\Charizma\Http\Services\UserCharismaService;

class KickUserRoomsCommand extends Command
{
    use UserSalaryTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kick-users-room';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update realtime project utd every hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $realtime = RealtimeProject::where([
            'month' => $month,
            'year' => $year,
            'type'  => 'audio',
        ])->first();
        if ($realtime) {


            $remaining = $realtime->balance - $realtime->used;


            $TimeRooms = TimeEnterRoom::whereNull('end_time')->get();
            $totalMinutes = 0;

            foreach ($TimeRooms as $timer) {

                // If still active, use current time
                $endTime = time();

                // Calculate minutes
                $minutes = ceil(($endTime - $timer->start_time) / 60);

                $totalMinutes += $minutes;
            }
            if ($totalMinutes >= $remaining) {
                foreach ($TimeRooms as $TimeRoom) {

                    $room = Room::select(['id', 'uid', 'count_room_socket', 'room_visitor', 'charizma_status', 'microphone'])->find($TimeRoom->room_id);
                    $user = User::find($TimeRoom->user_id);
                    $visitors = $this->updateRoomVisitorsBasedOnEvent($room, $user->id);

                    if ($room->uid == $user->now_room_uid) {
                        $user->now_room_uid = 0;
                    }

                    $this->removeUserToVisitors($room->id, $user->id);
                    $this->handleLeaveCp($user, $room);


                    if ($room->charizma_status) {
                        $this->handleCharismaStatusOnLogout($room, $user, $room->uid);
                    }
                    $user->save();
                }
            }
        }
    }


    private function handleCharismaStatusOnLogout($room, $user, $ownerId)
    {
        $userCharismaService = new UserCharismaService();
        $userCharismaService->resetUserCharisma($user->id, $room->id);
        $userDataWithCharisma = $userCharismaService->getUserResetData($room->microphone, [$user->id]);

        $ms = [
            'messageContent' => [
                "message" => "updateCharisma",
                'data' => $userDataWithCharisma
            ]
        ];
        $json = json_encode($ms);

        Common::sendToZego('SendCustomCommand', $room->id, $ownerId, $json);
    }

    private function removeUserToVisitors(int $roomId, int $userId)
    {
        RoomVisitor::query()->where(['user_id' => $userId, 'room_id' => $roomId])->delete();
    }

    public function handleLeaveCp($user, $room)
    {
        $userId = $user->id;
        $this->removeUserCpInRoom($userId);
        return $this->sendCpLovelyMessage($room, $user);
    }

    public function removeUserCpInRoom(mixed $userId): void
    {
        CpRoomHistory::where("user_one_id", $userId)
            ->orWhere("user_two_id", $userId)->delete();
    }

    public function sendCpLovelyMessage($room, $user)
    {
        $cpRoomHistories = CpRoomHistory::where("room_id", $room->id)->get(['index1', 'index2']);
        $indices = $cpRoomHistories->map(function ($history) {
            return [$history->index1, $history->index2];
        })->toArray();

        $json = $this->cpMapJson($indices);

        Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
    }

    private function updateRoomVisitorsBasedOnEvent($room, $userId)
    {

        $visitors = $room->room_visitor ? explode(',', $room->room_visitor) : [];


        UserHandling::calcTime($userId);
        UserHandling::calcTimeRoomEntered($userId, $room);
        $this->updateMicrophone($room->uid, $userId);
        $visitors = array_diff($visitors, [$userId]);

        return array_values(array_unique($visitors));
    }

    private function updateMicrophone($room_uid, $user_id)
    {
        $user = User::query()->find($user_id);
        if (!$user) return;
        $result  = Common::go_microphone_hand($room_uid, $user_id);

        $room = Room::query()->where('uid', $room_uid)->first();

        if (!$room) return;
        if ($result) {

            (new UserCharismaService())->RemoveUserRoomWhenLeaveMic($user_id, $room->id);
        }
    }

    public function cpMapJson($indices): string|false
    {
        $ms = [
            'messageContent' => [
                "message" => "cpLovelyZego",
                "data" => $indices,
            ]
        ];
        $json = json_encode($ms);
        return $json;
    }
}
