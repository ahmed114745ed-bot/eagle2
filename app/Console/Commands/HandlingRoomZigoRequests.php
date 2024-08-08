<?php

namespace App\Console\Commands;

use App\Classes\Gifts\RoomJobFactory;
use App\Dragon\DTO\RoomJobClass;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HandlingRoomZigoRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handling-room-zigo-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roomFactory = new RoomJobFactory();
        while (true) {

            $this->withRedis($roomFactory);
        }
    }



    /**
     * @param RoomJobFactory $roomFactory
     * @return void
     */
    public function withRedis(RoomJobFactory $roomFactory): void
    {

        $data = Redis::keys('*CharismaGift*');


        $allData = [];

        foreach ($data as $rKey) {
            $item = $rKey;
            try {
                $cleanKey = str_replace(config('database.redis.options.prefix'), "", $item);

                $item = Redis::get($cleanKey);

                $item = @unserialize($item);

                $item = new \App\Tik\DTO\RoomJobClass($item ?? []);

                $data_ne                = $roomFactory->setType($item->type)->work($item);
                $allData[$item->type][] = $data_ne;
                echo 'Done ' . $item->type . ' to room ' . $item->room_id . PHP_EOL;
            } catch (\Exception $e) {
                echo 'Fail ' . $item->type . ' to room ' . $item->room_id . PHP_EOL;

            }
            Redis::del($cleanKey);

        }

        try {
            foreach ($allData as $key => $allDatum) {
                $roomFactory->setType($key)->sendToZego($allData);
                echo 'Done zego  ' . $key . ' to room ' . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo 'Fail zego ' . $e->getMessage() . PHP_EOL;
        }





        sleep(5);
    }
}
