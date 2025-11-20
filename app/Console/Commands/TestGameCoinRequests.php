<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class TestGameCoinRequests extends Command
{
    protected $signature = 'test:game-coins {--user=} {--count=1}';
    protected $description = 'Send concurrent game coin requests for a single user';

    public function handle()
    {
        $this->info("🚀 Starting requests...");

        $userId = $this->option('user');
        $count = (int)$this->option('count');

        if (!$userId) {
            $this->error("❌ Please provide a user ID using --user option.");
            return;
        }

        if ($count < 1) {
            $this->error("❌ Count must be at least 1.");
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User not found.");
            return;
        }

        $client = new Client();
        $endpoint = 'https://eagle.utdsoftware.com/api/leader-cc-game/change-balance';
        $key = "303";
        $gameId = "101";
        $rewardType = 2;
        $type = 1; // consume
        $roundId = "round_" . time();
        $token = "TESTTOKEN" . $user->id;
        $winId = "";
        $roomid = "";

        $promises = [];

        for ($i = 1; $i <= $count; $i++) {
            $orderId = "ORD_" . $user->id . "_" . time() . "_$i";
            $coin = 1000;

            $rawString = $orderId . $gameId . $roundId . $user->id . $coin . $type . $rewardType . $token . $winId . $key;
            $sign = md5($rawString);

            $promises[] = $client->postAsync($endpoint, [
                'form_params' => [
                    'uid'        => (string)$user->id,
                    'orderId'    => (string)$orderId,
                    'gameId'     => $gameId,
                    'roundId'    => $roundId,
                    'coin'       => $coin,
                    'type'       => $type,
                    'rewardType' => $rewardType,
                    'token'      => $token,
                    'winId'      => $winId,
                    'roomid'     => $roomid,
                    'sign'       => $sign
                ]
            ]);
        }

        $results = Promise\Utils::settle($promises)->wait();

        $summary = [
            'SUCCESS' => 0,
            'BUSY'    => 0,
            'OTHER'   => 0,
        ];
        
        foreach ($results as $result) {
            if ($result['state'] === 'fulfilled') {
                $body = (string) $result['value']->getBody();
                $json = json_decode($body, true);
        
                if (isset($json['errorCode'])) {
                    switch ($json['errorCode']) {
                        case 0:
                            $summary['SUCCESS']++;
                            break;
                        case 5001:
                            $summary['BUSY']++;
                            break;
                        default:
                            $summary['OTHER']++;
                            break;
                    }
                } else {
                    $summary['OTHER']++;
                }
            } else {
                $summary['OTHER']++;
            }
        }
        
        $this->info("🎯 Summary for user {$user->id}:");
        foreach ($summary as $type => $count) {
            switch ($type) {
                case 'SUCCESS':
                    $desc = 'Accepted requests';
                    break;
                case 'BUSY':
                    $desc = 'Rejected due to user busy';
                    break;
                case 'OTHER':
                    $desc = 'Rejected for other reasons';
                    break;
            }
            $this->info("{$desc}: {$count}");
        }
        
        $this->info("🎉 Finished $count concurrent requests!");
    }
}
