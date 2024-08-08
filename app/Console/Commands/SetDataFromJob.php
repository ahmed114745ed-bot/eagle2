<?php

namespace App\Console\Commands;

use App\Jobs\UpdateUserDataWhenSendGift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SetDataFromJob extends Command
{
    protected $signature = 'redis:get_data';

    protected $description = 'get_data from redis';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $keys = Redis::keys('*luckyGift_*');
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
                dispatchJobToQueue((new UpdateUserDataWhenSendGift($value['user_id'], $value['room_id'], $value['receiversIds'], $value['giftId'], $value['number'], $value['price'], $value['user_coin_after'],$value['total_num_win'],$value['total_user_win'])), 'luckyGift');
                Redis::del($cleanKey);
            }
        }
    }

}
