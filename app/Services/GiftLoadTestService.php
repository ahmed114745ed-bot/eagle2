<?php

namespace App\Services;

use App\Models\User;
use App\Models\Gift;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Http;

class GiftLoadTestService
{
    /**
     * Run a load test for sending gifts.
     *
     * @param array $data [
   
     * @return array
     * @throws \Exception
     */
    public function run(array $data)
    {
        // *** Fetch sender data from the API using token ***
        $response = Http::withToken($data['token'])
                        ->get($data['url'] . '/api/my-data');

        if ($response->failed()) {
            throw new \Exception("Token invalid — unable to fetch sender data");
        }

        $senderData = $response->json('data');
        if (!isset($senderData['id'])) {
            throw new \Exception("Sender ID not found in /my-data response");
        }

        $sender = User::findOrFail($senderData['id']);
        $receiver = User::findOrFail($data['toUid']);
        $gift = Gift::findOrFail($data['id']);

        $giftValue = $gift->price;
        $giftReceive =  $gift->price;

        // *** Wallet balances before sending ***
        $beforeSender = $sender->di;
        $beforeReceiver = $receiver->monthly_diamond_received;

        // *** Initialize HTTP client ***
        $client = new Client([
            'base_uri' => $data['url'],
            'timeout'  => 10,
            'verify'   => false,
        ]);

        $promises = [];

        for ($i = 1; $i <= $data['count']; $i++) {
            $promises[$i] = $client->postAsync('/api/gifts/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $data['token'],
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'id' => $data['id'],
                    'owner_id' => $data['owner_id'],
                    'toUid' => $data['toUid'],
                    'num' => $data['num']
                ]
            ]);
        }

        // *** Wait for all requests to finish ***
        $results = Utils::settle($promises)->wait();

        // *** Summary ***
        $summary = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($results as $index => $res) {
            if ($res['state'] === 'fulfilled') {
                $body = json_decode($res['value']->getBody(), true);
                $isSuccess = !empty($body['success']);

                $summary['success'] += $isSuccess ? 1 : 0;
                $summary['failed'] += $isSuccess ? 0 : 1;

                $summary['errors'][] = [
                    'index' => $index,
                    'status' => $isSuccess ? 'SUCCESS' : 'FAILED',
                    'response' => $body
                ];

            } else {
                $summary['failed']++;
                $summary['errors'][] = [
                    'index' => $index,
                    'status' => 'FAILED',
                    'error' => $res['reason']->getMessage()
                ];
            }
        }

        // *** Calculations ***
        $sentGifts = $summary['success'] * $data['num'];

        $failedGifts = $summary['failed'] * $data['num'];

        $expectedSender = $beforeSender - ($giftValue * $sentGifts);
        $expectedReceiver = $beforeReceiver + ($giftReceive * $sentGifts);

        $afterSender = $sender->fresh()->di;
        $afterReceiver = $receiver->fresh()->monthly_diamond_received;

        return [
            'summary' => $summary,
            'before_sender' => $beforeSender,
            'after_sender' => $afterSender,
            'expected_sender' => $expectedSender,
            'before_receiver' => $beforeReceiver,
            'after_receiver' => $afterReceiver,
            'expected_receiver' => $expectedReceiver,
            'gift_value' => $giftValue,
            'gift_receive' => $giftReceive,
            'sent_gifts' => $sentGifts,
            'failed_gifts' => $failedGifts,
        ];
    }
}
