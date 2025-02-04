<?php

namespace App\Services\Gifts;



use App\Facades\RedisService;
use App\Models\Gift;
use App\Models\User;
use Redis;

class LuckyStrategyService
{

    public function calculateMid() : bool
    {
        $numbers = Gift::query()->where('type', 6)->pluck('price')->toArray();
        try {
            $result = $this->getThresholdsFromPython($numbers);
            $this->saveThresholds($result);
        } catch (\Exception $e) {

            // \Log::info($e->getMessage());
            return false;
        }

        return true;
    }


    public function saveThresholds($result) : void
    {
        RedisService::updateUnSerialize(LUCKY_REDIS_KEY, $result);
    }


    public function getThresholds() : array
    {
        // $key = 'LUCKY_REDIS_KEY';
        // $values = [
        //     'threshold1' => 100,
        //     'threshold2' => 200,
        // ];

        // $serializedValues = serialize($values);
        // RedisService::updateUnSerialize(LUCKY_REDIS_KEY, $values);
        // $unSerializedValues = Redis::get("LUCKY_REDIS_KEY");
        // $unSerialize = unserialize($unSerializedValues);
        // return [@$unSerialize["threshold1"] ?? 0 , @$unSerialize["threshold2"] ?? 0];

        $unSerialize = RedisService::getUnSerialize(LUCKY_REDIS_KEY);

        return [@$unSerialize["threshold1"] ?? 0 , @$unSerialize["threshold2"] ?? 0];
    }

    public function getGiftCategory(Gift $gift)
    {
        $numbers = $this->getThresholds();

        $firstN     = $numbers[0];
        $secondN    = $numbers[1];
        $price      = $gift->price;
        $cat        = 0;
        if ($price > $secondN) {
            $cat = 3;
        } elseif ($price < $firstN) {
            $cat = 1;
        } else {
            $cat = 2;
        }
        return $cat;
    }

    public function getUpdateGiftCountForCategory(Gift $gift)
    {
        $cat = $this->getGiftCategory($gift);
        $key = 'cat '.$cat;

        $checkKey=RedisService::get($key);
        if ($checkKey != null) {
            $value = RedisService::getUnSerialize($key);
            $value += 1;
        }else{
            $value = 1;
        }

        RedisService::updateUnSerialize($key, $value);

        return $value;
    }

    public function getUpdateUserStatistic(User $user,$bit,$win)
    {
        $key = 'user '.$user->id;

        $checkKey=RedisService::get($key);
        if ($checkKey != null) {
            $value = RedisService::getUnSerialize($key);
            $value['total_bit'] += $bit;
            $value['total_win'] += $win;
        }else{
            $value['total_bit'] = $bit;
            $value['total_win'] = $win;
        }

        RedisService::updateUnSerialize($key, $value);

        return $value;
    }

    /**
     * @param mixed $numbers
     * @return array
     */
    // public function getThresholdsFromPython(mixed $numbers): array
    // {
    //     $input = implode(' ', $numbers);
    //     $pythonScript = base_path('app/Tik/Python/categorize_numbers.py');
    //     $command = "python3 {$pythonScript} {$input}";

    //     $output = [];
    //     exec($command, $output);
    //     return json_decode(implode('', $output), true);
    // }

    public function getThresholdsFromPython(mixed $numbers): array
    {
        $input = implode(' ', $numbers);
        $pythonScript = base_path('app/Tik/Python/categorize_numbers.py');
        $command = escapeshellcmd("python3 {$pythonScript} {$input}");

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            //dd($output);
           return throw new \RuntimeException("Python script returned an error. Command: {$command}");
        }

        $outputString = implode('', $output);
        $decodedOutput = json_decode($outputString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decoding error: " . json_last_error_msg());
        }
        return $decodedOutput;
    }

}
