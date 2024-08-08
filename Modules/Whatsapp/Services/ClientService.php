<?php

namespace Modules\Whatsapp\Services;

use App\Facades\RedisService;
use App\Models\WhatsappWebhookValidate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Whatsapp\Entities\VerificationCode;
use Modules\Whatsapp\Entities\WhatsappApp;
use Modules\Whatsapp\Http\Middleware\Whatsapp;

class ClientService
{

    public function prepareData($data): ?array
    {

        $userPhone   = $data['user_phone'];
        $userName    = $data['user_name'];
        $messageType = $data['message_type'];
        $messageBody = $data['message_body'];

        // Log::info('this is message body ');
        // Log::info($messageBody);
        $verificationCode = $this->getVerificationCodeFromMessage((string)$messageType, (string)$messageBody);

       // Log::info('this is verification code ' . $verificationCode);
        if (!$verificationCode) return null;
        $model  = $this->getVerificationCode($verificationCode);
        $status = 'validated';
        if (!$model) {
            $status = 'notValidated';
        } else if ($model->isExpire()) {
            $status = 'expired';
        }

        return [
            'uuid'              => $model?->uuid ?? '',
            'verification_code' => $verificationCode,
            'phone_number'      => '+'.$userPhone,
            'profile_name'      => $userName,
            'status'            => $status,
            'app_id'            => $model?->app_id ?? '',
            'requested_at'      => $model?->requested_at ?? '',
            'expires_at'        => $model?->expires_at ?? '',
        ];
    }

    private function getVerificationCodeFromMessage(string $messageType, string $messageBody): ?string
    {
        $pattern = '/>>(\w+)<</';

        if (preg_match($pattern, $messageBody, $matches)) {
            $code = $matches[1];

            return $code;
        }
        return null;
    }

    public function getVerificationCode(string $verificationCode): VerificationCode|null
    {
        return VerificationCode::where('code', $verificationCode)->latest()->first();
    }

    public function sendToServer($data)
    {
        if (@$data['status'] == 'validated') {
            $appId = @$data['app_id'];
            if ($appId) {
                $whatsappApp = $this->getWhatsappApp($appId);
                //Log::info('this is whatsapp app : '. $whatsappApp?->id ?? '' . ' and this is app id '. $appId);
                if ($whatsappApp) {
                     $this->callWebhook((string)$whatsappApp->webhook_url, $data);
                }else{
                    WhatsappWebhookValidate::create($data);
                }
            }else{
                WhatsappWebhookValidate::create($data);
            }
        }
       // Log::info(json_encode($data));
    }

    public function getRedirectLinks($data)
    {
        $verificationCode = $this->generateVerificationCode();
        $message = $data['message'];
        $phone = @$data['phone'] ?? '';
        $this->createVerificationCode([
                                          'minutes' => $data['expire_at'],
                                          'code'    => $verificationCode,
                                          'app_id'  => $data['app_id']
                                      ]);

        $message = $this->makeMessage($verificationCode, $message);
        $link    = $this->generateWhatsappUrl($message, $phone);
        $qr      = $this->generateQrCodeLink($link);

        return [
            'code' => $verificationCode,
            'link' => $link,
            'qr'   => $qr
        ];
    }

    private function generateVerificationCode(int $length = 10)
    {
        return \Str::random($length);
    }

    private function createVerificationCode(array $data): VerificationCode
    {

        if (!@$data['code'] || !@$data['minutes'] || !@$data['app_id']) die('you must provide a data ,code and app_id');
        return VerificationCode::create([
                                            'uuid'         => uuid_create(),
                                            'code'         => $data['code'],
                                            'app_id'       => $data['app_id'],
                                            'requested_at' => now(),
                                            'expires_at'   => Carbon::now()->copy()->addMinutes($data['minutes'])->toDateTimeString()
                                        ]);
    }

    private function makeMessage($verificationCode, $message = null)
    {
        $message = $message ?? __('Send this message to verify your phone in'). ' '. config('whatsapp.app_name');
        return <<<EOD
            >>$verificationCode<<
            $message
            EOD;
    }

    public function generateWhatsappUrl($message, string $phone = '')
    {
        if ($phone == '') $phone = config('whatsapp.phone');

        $message = urlencode($message);
        return "whatsapp://send?phone=" . trim($phone) . "&text=$message";
    }

    public function generateQrCodeLink($data)
    {
        $data = urlencode($data);

        return "https://api.qrserver.com/v1/create-qr-code/?data=" . $data . "&size=200x200";

    }

    /**
     * @param mixed $appId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function getWhatsappApp(mixed $appId): array|null|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return WhatsappApp::find($appId);
    }

    private function callWebhook(string $webhookUrl, array $data) : void
    {
       // Log::info('this is webhook : ' . $webhookUrl);
        $response = \Http::post($webhookUrl ,$data);
       // Log::info('this is webhook : ' . $response->status());

    }

    public function getLinksFromServer(array $data)
    {
        $url = config('whatsapp.server_url');
        $token = RedisService::get('whatsapp_token');

        $response = \Http::withHeaders(['Authorization' => 'Bearer '. $token])->get($url, $data);
        return $response->json();
    }

}
