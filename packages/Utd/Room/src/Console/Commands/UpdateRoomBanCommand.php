<?php

namespace Utd\Room\Console\Commands;

use Illuminate\Console\Command;
use Utd\Room\Entities\Room;

class UpdateRoomBanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-room-ban';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update room ban status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rooms = Room::where('room_status', 2)->get();

        if ($rooms) {
            foreach ($rooms as $room) {
                $ban = $room->bans()
                    ->whereRaw("created_at + INTERVAL duration HOUR > ?", [now()])
                    ->first();
                if (!$ban) {
                    $room->room_status = 1;
                    $room->save();
                }
            }
        }
    }
}
