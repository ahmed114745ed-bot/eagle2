<?php

namespace App\Jobs;

use Database\Seeders\config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $phone ;
    private $message ;

    public function __construct( $phone , $message  )
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    public function handle(): void
    {
        $url = (string)config('view.whatsapp_url');
        $token = (string)config('view.whatsapp_token');
        $to =  $this->phone;
        $body =$this->message ;
        $client = new Client();
        try {
                $client->post($url, [
                'form_params' => [
                    'token' => (string)$token,
                    'to' =>(string) $to,
                    'body' => (string)$body,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
        } catch (RequestException $e) {

        }
    }
}
