<?php

namespace Modules\Whatsapp\Jobs;

use Database\Seeders\config;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Whatsapp\Services\WhatsappOTPService;

class WhatsappOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private string $phone , private string $otp)
    {
        $this->phone = $phone;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $to =  $this->phone;
        $code =$this->otp;
        (new WhatsappOTPService())->sendMessage($to, $code, config('whatsapp.phone_id'));
        /*$url = (string)config('view.whatsapp_url');
        $token = (string)config('view.whatsapp_token');
        $to =  $this->phone;
        $body =$this->code;
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

        }*/
    }
}
