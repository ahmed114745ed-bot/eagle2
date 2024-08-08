<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\HelperTraits\PusherTrait;
use App\Models\Room;

class UpdateRoomUserNowCron extends Command
{
    use PusherTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-room-user-now:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        //\Log::info("Testing Cron is Running ... !");

        $rooms_now_live = self::getIdRoomCountUserFromPresenceChannel();

        $rooms_now_live = collect($rooms_now_live)->sortBy(function($item, $key) {
            return $item['owner_room_id'];
        });

        $rooms_owner_ids = $rooms_now_live->pluck('owner_room_id');

        $roomUpdate = Room::whereIn('uid',$rooms_owner_ids)->orderBy('uid')->get();
        foreach($roomUpdate as $key => $room)
        {
            $now = $rooms_now_live->where('owner_room_id', $room->uid)->first();

            $room->count_room_socket = $now['count_user'];
            $room->save();

        }

        Room::whereNotIn('uid',$rooms_owner_ids)->where('count_room_socket','>=',1)->where('room_status',1)->update(['count_room_socket' => 0]);
    }
}
