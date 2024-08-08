<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Http\Services\LuckyBoxServices;
use App\Models\BoxUse;
use App\Models\Room;
use App\Models\User;
use App\Models\UserBoxGift;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;


class OpenBoxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $boxUseId;
    private int $userId;
    private string $userName;

    /**
     * @param float $coins
     * @param int $boxUseId
     * @param int $userId
     * @param string $userName
     */
    public function __construct(int $boxUseId, int $userId, string $userName)
    {
        $this->boxUseId = $boxUseId;
        $this->userId   = $userId;
        $this->userName = $userName;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //update box use
        $this->updateBoxUse();
        //update user in user_box_use
        $this->updateDatabase();

        $box_use = BoxUse::find($this->boxUseId);
        $room    = Room::withoutAppends()->where('uid', $box_use->room_uid)->select('id')->first();
        $c     = BoxUse::query()->where('room_uid', $box_use->room_uid)->where('not_used_num', '>', 0)->count();
        $owner = User::withoutAppends()->select('id', 'name')->find($box_use->user_id);
        $m     = [
            "messageContent" => [
                "message"      => "hideluckybox",
                "ownerBoxId"   => @$owner->id,
                "ownerBoxName" => @$owner->name,
                "boxCoins"     => $box_use->coins,
                "boxId"        => $box_use->id,
                "boxType"      => $box_use->type == 1 ? 'super' : 'normal',
                "numOfBoxes"   => $c
            ]
        ];
        $json  = json_encode($m);
        Common::sendToZego('SendCustomCommand', @$room->id, $this->userId, $json);
    }

    public function updateDatabase()
    {
        $keys = Redis::keys('*LuckyBox*');
        foreach ($keys as $key) {
            $cleanKey = str_replace(config('database.redis.options.prefix') , "", $key);
            $type = Redis::type($cleanKey)->getPayload();
            $value = null;
            if ($type == 'string') {
                $value = Redis::get($cleanKey);
                if ($value !== false || $value === 'b:0;') {
                    $unserializedValue = @unserialize($value);
                    $value = $unserializedValue;
                }
                $value['created_at'] =now();
                $value['updated_at'] =now();
                \DB::table('user_box_gifts')->insert($value);
                Redis::del($cleanKey);
            }
        }
    }

    public function updateBoxUse()
    {
        $keys = Redis::keys('*BoxUse_*');
        foreach ($keys as $key) {
            $cleanKey = str_replace(config('database.redis.options.prefix') , "", $key);
            $type = Redis::type($cleanKey)->getPayload();
            $value = null;
            if ($type == 'string') {
                $value = Redis::get($cleanKey);
                if ($value !== false || $value === 'b:0;') {
                    $unserializedValue = @unserialize($value);
                    $value = $unserializedValue;
                }
                $value['updated_at'] =now();
                \DB::table('box_uses')->where([
                    'id'=>$value['id'],
                    'box_id'=>$value['box_id'],
                    'user_id'=>$value['user_id'],
                    'room_id'=>$value['room_id'],
                ])->update($value);
            }
        }
    }


}



