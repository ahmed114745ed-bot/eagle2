<?php

namespace Modules\Whatsapp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Whatsapp\Services\ClientService;

class WhatsappController extends Controller
{

    public function __construct(private ClientService $clientService) {}

    public function webhook(Request $request)
    {
        //\Log::info(' this is post request  ' . json_encode($request->all()));

        $changes = @$request['entry'][0]['changes'][0];


        if ($changes['field'] == 'messages') {
            $value     = @$changes['value'];
            $contacts  = @$value['contacts'];
            $userName  = @$contacts[0]['profile']['name'];
            $userPhone = @$contacts[0]['wa_id'];

            $messages = @$value['messages'];

            $timestamp = @$messages[0]['timestamp'];
            $type      = @$messages[0]['type'];
            $body      = @$messages[0]['text']['body'];

            $data = [
                'user_phone'   => $userPhone,
                'user_name'    => $userName,
                'message_type' => $type,
                'message_body' => $body,
                'timestamp' => $timestamp,
            ];
            $serverData = $this->clientService->prepareData($data);
            if ($serverData == null) response()->json();
            $this->clientService->sendToServer($serverData);
        }


        return response()->json();
    }

    public function getWebhook(Request $request)
    {
       // \Log::info(' this is get request  ' . json_encode($request->all()));
        $hubChallenge = @$request->hub_challenge;
       // \Log::info(' this is get request  ' . $hubChallenge);

        $hubVerifyToken = @$request->hub_verify_token;
        $hubMode        = @$request->hub_mode;
        $validToken     = 'test-verify';
        if ($hubMode == 'subscribe' && $hubVerifyToken == $validToken) {
            return response(content: $hubChallenge, status: 200);
        }

        return response(status: 403);

    }
}
