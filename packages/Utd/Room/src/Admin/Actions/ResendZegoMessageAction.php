<?php

namespace Utd\Room\Admin\Actions;

use App\Models\Gift; // App\Models\Gift safely aliases Utd\Gifts\Entities\Gift when package is installed
use App\Models\User;
use App\Traits\Gifts\WinLuckyGift;
use Encore\Admin\Actions\RowAction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Utd\Room\Entities\CustomZegoMessage;

class ResendZegoMessageAction extends RowAction
{
    use WinLuckyGift;

    public $name;

    public function __construct($id = 0)
    {
        $this->name = __('dashboard.retryZego');
        parent::__construct();
    }

    public function handle(Model $model, Request $request)
    {
        try {
            $data = CustomZegoMessage::query()->find($model->id);
            $percentage = ($request->percentage !== null ? $request->percentage : $data->percentage);
            $newDate = CustomZegoMessage::create([
                'user_id' => $data->user_id,
                'room_id' => $data->room_id,
                'gift_id' => $data->gift_id,
                'percentage' => $percentage,
            ]);

            $user = User::query()->find($newDate->user_id);
            $gift = Gift::query()->find($newDate->gift_id);
            $room = $user->ownerRoom;

            $zigoData = [
                'user_id' => $user->id,
                'user_image' => @$user->profile->avatar ?? '',
                'gift_image' => @$gift->img ?? '',
                'owner_id' => $user->id,
                'user_name' => $user->name ?? '',
                'room_id' => $room->id,
                'percentage' => $percentage,
                'is_room_pass' => ($room->room_pass !== null && $room->room_pass !== ''),
                'gift_price' => @$gift->price,
                'room_name' => $room->room_name ?: '',
                'room_cover' => $room->room_cover ?? '',
                'room_background' => $room->final_room_image ?? '',
                'room_mode' => $room->mode,
                'room_uuid' => $room->owner?->uuid ?: 0,
                'room_owner_id' => $room->uid ?: 0,
                'is_password' => (bool) (@$room->room_pass),
            ];
            $this->sendToZegoLuckyGift($zigoData);

            return $this->response()->success(__('dashboard.successful'))->refresh();
        } catch (Exception $exception) {
            return $this->response()->error('خطا.')->refresh();
        }
    }

    public function form()
    {
        $this->integer('percentage', __('dashboard.errorPercentage'));
    }
}
