<?php

namespace App\Admin\Actions;

use App\Models\Room;
use App\Helpers\Common;
use App\Models\BanRoom;
use Illuminate\Http\Request;
use Encore\Admin\Actions\Action;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Actions\RowAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CloseRoomAction extends RowAction
{
    public $name;
    public $id;
    protected $selector = '.delete-ban';
    public function __construct($id = 0)
    {
        $this->name = __("dashboard.roomClose");
        $this->id = $id;
        parent::__construct();
    }
    public function handle(Model $model, Request $request)
    {
        try {
            DB::beginTransaction();
            $room = Room::find($request->id);
            if (!$room) {
                return $this->response()->error(__('room not found'))->refresh();
            }
            Common::kickOfAllUsersRoom($room);
            $this->closeRoom($room, $request);
            DB::commit();
            return $this->response()->success(__('dashboard.successful'))->refresh();
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->error($exception->getMessage())->refresh();
        }
    }

    public function form()
    {
        $this->hidden('id', __('id'))->default($this->id);
        $this->integer('duration', __('duration(hours)'))->rules('required|max:6');
    }

    // public function dialog()
    // {
    //     $this->confirm(__('dashboard.closeRoom'), '', []);
    // }

    public function html()
    {
        return '<a href="javascript:void(0);" onclick="pu(' . $this->id . ')"  ></a>
<script>
function pu(val) {

  $("#vid").val(val)
}
</script>
';
    }

    public function closeRoom($room, $request)
    {
        $room_id  = $room->id;
        $now = now();
        $messages = [];
        $newBan = false;


        $haveBan = BanRoom::query()->where('room_id', $room_id)->whereRaw("created_at + INTERVAL duration HOUR > '$now'")
            ->exists();
        if ($haveBan) {
            $messages[] = __('already have normal ban');
        } else {
            $newBan = true;

            BanRoom::query()->create(
                [
                    'room_id' => $room_id,
                    'duration' => $request->duration,
                    'staff_id' => Auth::id(),
                ]
            );

            $room->room_status = 2;

            $room->save();
        }




        if ($room && $newBan) {
            $d = [
                "messageContent" => [
                    "message" => "banRoom",
                    "roomId" => $room->id
                ]
            ];
            $json = json_encode($d);

            Common::sendToZego('SendCustomCommand', $room->id, $room->uid, $json);
        }
        if ($newBan) {
            // CustomNotification::banUser($user, $request->duration);
        }

        if (count($messages) > 0) {
            return $this->response()->error(implode("<br>", $messages))->refresh();
        }
    }
}
