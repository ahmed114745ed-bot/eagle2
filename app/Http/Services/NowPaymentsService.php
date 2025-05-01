<?php

namespace App\Http\Services;

use Database\Seeders\config;
use GuzzleHttp\Client;

class NowPaymentsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.nowpayments.io/v1/',
            'headers' => [
                //'x-api-key' => env('NOWPAYMENTS_API_KEY'),
                'x-api-key' => config('services.now_payments.api_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getCurrencies(){
        $response = $this->client->get('currencies', [
            'query' => [
                'fixed_rate' => 'true',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
    public function createPayment(array $data)
    {
        $response = $this->client->post('payment', [
            'json' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getPaymentStatus($paymentId)
    {
        $response = $this->client->get("payment/{$paymentId}");

        return json_decode($response->getBody(), true);
    }
}
