<?php

namespace App\Admin\Actions;

use App\Models\Room;
use Encore\Admin\Actions\Action;


class RoomAction extends Action
{
    public $id;
    public $top_room;
    public $room_status;
    public $pin;
    public $is_afk;
    public $can_play;

    protected $selector = '.delete-ban';

    public function __construct($id = 0, $room_status = 0, $top_room = 0, $pin = 0, $is_afk = 0, )
    {
        $this->id = $id;
        $this->room_status = $room_status;
        $this->top_room = $top_room;
        $this->pin = $pin;
        $this->is_afk = $is_afk;


        parent::__construct();
    }

    public function handle(\Illuminate\Http\Request $request)
    {
        $room = Room::find($request->id);
        if (!$room) {
            return $this->response()->error(__('room not found'))->refresh();
        }
        $room->update([
            'room_status'   => $request->room_status,
            'top_room'  => $request->top_room,
            'pin' => $request->pin,
            'is_afk' => $request->is_afk,

        ]);
        
        return $this->response()->success('success')->refresh();
    }


    public function form()
    {
        $this->hidden('id', __('ID'))->attribute('id', 'id');

        $this->radio('room_status', __('Room status'))
            ->options([1 => __('on'), 0 => __('off')])
            ->value($this->room_status);

        $this->radio('top_room', __('Top room'))
            ->options([1 => __('on'), 0 => __('off')])->value($this->top_room);

        $this->radio('pin', __('pin'))
            ->options([1 => __('on'), 0 => __('off')])->value($this->pin);


        $this->radio('is_afk', __('owner in'))
            ->options([1 => __('on'), 0 => __('off')])->value($this->is_afk);

    }



    public function html()
    {
        return '<a href="#" onclick="openUserForm(' .
            '\'' . $this->id . '\', ' .
            '\'' . $this->room_status . '\', ' .
            '\'' . $this->top_room . '\', ' .
            '\'' . $this->pin . '\', ' .
            '\'' . $this->is_afk . '\', ' .
            ')" class="btn btn-sm btn-info delete-ban">
            <i class="fa fa-edit"></i> ' . ' '. __('status') . '
        </a>
        <script>
            function openUserForm(id, room_status, top_room, pin, is_afk) {
                console.log(id,  top_room, pin, is_afk, );
                
                $("#id").val(id);
                $("#room_status").val(room_status);
                $("#top_room").val(top_room);
                $("#pin").val(pin);
                $("#is_afk").val(is_afk);
            }
        </script>';
    }
}
