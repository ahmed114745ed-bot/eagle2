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
            // This is a long-running Redis listener loop
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

        \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][2-HANDLER] withRedis cycle started', [
            'keys_found' => count($data),
            'keys' => $data,
        ]);

        $allData = [];

        foreach ($data as $rKey) {
            $item = $rKey;
            $cleanKey = null;
            try {
                $cleanKey = str_replace(config('database.redis.options.prefix'), "", $item);

                $rawValue = Redis::get($cleanKey);

                \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][2-HANDLER] Raw Redis value', [
                    'redis_key' => $cleanKey,
                    'raw_type'  => gettype($rawValue),
                    'raw_value' => substr((string) $rawValue, 0, 200),
                ]);

                $item = @unserialize($rawValue);

                // Ensure $item is an array
                if ($item === false || !is_array($item)) {
                    \Illuminate\Support\Facades\Log::channel('charisma_value')->error('[CHARISMA][2-HANDLER] unserialize FAILED', [
                        'redis_key' => $cleanKey,
                        'raw_value' => substr((string) $rawValue, 0, 500),
                    ]);
                    Redis::del($cleanKey);
                    continue;
                }

                \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][2-HANDLER] Processing Redis key', [
                    'redis_key' => $cleanKey,
                    'room_id'   => $item['room_id'] ?? '?',
                    'user_id'   => $item['user_id'] ?? '?',
                    'coins'     => $item['coins'] ?? '?',
                    'type'      => $item['type'] ?? '?',
                    'receivers' => unserialize($item['data'] ?? 'a:0:{}'),
                ]);

                $item = new \App\Tik\DTO\RoomJobClass($item ?? []);

                $data_ne                = $roomFactory->setType($item->type)->work($item);
                $allData[$item->type][] = $data_ne;

                \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][2-HANDLER] work() result', [
                    'room_id' => $item->room_id,
                    'user_id' => $item->user_id,
                    'type'    => $item->type,
                    'result'  => $data_ne,
                ]);

                echo 'Done ' . $item->type . ' to room ' . $item->room_id . PHP_EOL;
            } catch (\Throwable $e) {
                $type = is_object($item) ? ($item->type ?? '?') : 'unknown';
                $room = is_object($item) ? ($item->room_id ?? '?') : 'unknown';
                \Illuminate\Support\Facades\Log::channel('charisma_value')->error('[CHARISMA][2-HANDLER] work() FAILED', [
                    'redis_key' => $cleanKey ?? '?',
                    'room_id'   => $room,
                    'type'      => $type,
                    'error'     => $e->getMessage(),
                ]);
                echo 'Fail ' . $type . ' to room ' . $room . ': ' . $e->getMessage() . PHP_EOL;
            }
            Redis::del($cleanKey);

        }

        \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][3-ZEGO] Preparing to send to Zego', [
            'allData_types' => array_keys($allData),
            'allData'       => $allData,
        ]);

        try {
            foreach ($allData as $key => $allDatum) {
                $roomFactory->setType($key)->sendToZego($allData);
                \Illuminate\Support\Facades\Log::channel('charisma_value')->info('[CHARISMA][3-ZEGO] sendToZego dispatched', [
                    'type' => $key,
                ]);
                echo 'Done zego  ' . $key . ' to room ' . PHP_EOL;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::channel('charisma_value')->error('[CHARISMA][3-ZEGO] sendToZego FAILED', [
                'error' => $e->getMessage(),
            ]);
            echo 'Fail zego ' . $e->getMessage() . PHP_EOL;
        }





        sleep(5);
    }
}
