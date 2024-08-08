<?php

namespace App\Exports;

use App\Models\Room;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RoomsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Room::select([
            'id', 'numid', 'uid', 'room_status', 'room_name', 'room_cover', 'room_intro', 'room_pass',
            'room_class', 'room_type', 'room_welcome', 'room_admin', 'room_visitor', 'room_speak',
            'room_sound', 'room_black', 'week_star', 'ranking', 'is_popular', 'secret_chat', 'is_top',
            'sort', 'room_background', 'super_uid', 'is_afk', 'hot', 'room_judge', 'microphone',
            'is_prohibit_sound', 'openid', 'commission_proportion', 'fresh_time', 'start_hour', 'end_hour',
            'is_recommended', 'play_num', 'free_mic', 'created_at', 'updated_at', 'mode', 'session',
            'hour_hot', 'visitor_count', 'top_room', 'max_admin', 'count_room_socket', 'no_of_members',
            'is_show_pk', 'top_user_id', 'muted_users', 'pin', 'charizma_status', 'charizma_timestamp',
            'sort_num', 'game_id', 'total_diamond', 'level', 'exp', 'level_id', 'total_game_coins',
            'writing_disabled'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Num ID', 'UID', 'Room Status', 'Room Name', 'Room Cover', 'Room Intro', 'Room Pass',
            'Room Class', 'Room Type', 'Room Welcome', 'Room Admin', 'Room Visitor', 'Room Speak',
            'Room Sound', 'Room Black', 'Week Star', 'Ranking', 'Is Popular', 'Secret Chat', 'Is Top',
            'Sort', 'Room Background', 'Super UID', 'Is AFK', 'Hot', 'Room Judge', 'Microphone',
            'Is Prohibit Sound', 'OpenID', 'Commission Proportion', 'Fresh Time', 'Start Hour', 'End Hour',
            'Is Recommended', 'Play Num', 'Free Mic', 'Created At', 'Updated At', 'Mode', 'Session',
            'Hour Hot', 'Visitor Count', 'Top Room', 'Max Admin', 'Count Room Socket', 'No Of Members',
            'Is Show PK', 'Top User ID', 'Muted Users', 'Pin', 'Charizma Status', 'Charizma Timestamp',
            'Sort Num', 'Game ID', 'Total Diamond', 'Level', 'EXP', 'Level ID', 'Total Game Coins',
            'Writing Disabled'
        ];
    }
}
