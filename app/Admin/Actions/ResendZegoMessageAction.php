<?php

namespace App\Admin\Actions;

use App\Models\CustomZegoMessage;
use App\Models\Gift;
use App\Models\User;
use App\Traits\Gifts\WinLuckyGift;
use Illuminate\Http\Request;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ResendZegoMessageAction extends RowAction
{
    use WinLuckyGift;
    public $name;
    public function __construct($id = 0)
    {
        $this->name = __("dashboard.retryZego");
        parent::__construct();
    }
    public function handle(Model $model, Request $request)
    {
        try {


           $data=CustomZegoMessage::query()->find($model->id);
           $percentage = ($request->percentage != null ? $request->percentage : $data->percentage);
            $newDate = CustomZegoMessage::create([
                'user_id'   =>  $data->user_id,
                'room_id'   =>  $data->room_id,
                'gift_id'   =>  $data->gift_id,
                'percentage'=>  $percentage,
            ]);

            $user=User::query()->find($newDate->user_id);
            $gift=Gift::query()->find($newDate->gift_id);
            $room=$user->ownerRoom;

            $zigoData = [
                'user_id'      => $user->id,
                'user_image'   => @$user->avatar->image ?? '',
                'gift_image'   => @$gift->img ?? '',
                'owner_id'     => $user->id,
                'user_name'    => $user->name ?? '',
                'room_id'      => $room->id,
                'percentage'   => $percentage,
                'is_room_pass' => ($room->room_pass != null && $room->room_pass != '')

            ];
            $this->sendToZegoLuckyGift($zigoData);
            return $this->response()->success(__('dashboard.successful'))->refresh();
        } catch (\Exception $exception) {
            return $this->response()->error('خطا.')->refresh();
        }
    }

    public function form()
    {
        $this->integer('percentage', __('dashboard.errorPercentage'));
    }
}
