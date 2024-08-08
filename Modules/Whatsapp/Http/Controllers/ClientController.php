<?php

namespace Modules\Whatsapp\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use Modules\Whatsapp\Entities\WhatsappApp;
use Modules\Whatsapp\Entities\WhatsappMessage;
use Modules\Whatsapp\Services\ClientService;

class ClientController extends Controller
{

    public function __construct(private ClientService $clientService) { }

    public function index()
    {

        $host = \request()->getHost();

        if (!isSubdomain($host)){
            $expireAt = request()->expires_at ?? 10;
            $whatsappMessage  = WhatsappMessage::first();
            $message = app()->getLocale() == 'ar' ? $whatsappMessage?->text_ar : $whatsappMessage?->text_en;

            $data          = ['expire_at' => $expireAt, 'app_id' => "123456", 'message' => $message];
            $redirectLinks = $this->clientService->getRedirectLinks($data);
        }else{

            $expireAt = request()->expires_at ?? 10;
            $whatsappMessage  = WhatsappMessage::first();
            $message = app()->getLocale() == 'ar' ? $whatsappMessage?->text_ar : $whatsappMessage?->text_en;

            $data          = ['expire_at' => $expireAt, 'message' => $message];
            $redirectLinks = $this->clientService->getLinksFromServer($data);

        }



        return response()->json($redirectLinks);
    }

    public function service()
    {
        $expireAt = request()->expires_at ?? 10;
        $user    = \Auth::user();
        $appId    = $user->uuid ;
        $message  = request()->message ?? "this for verification";
        $phone         = $user->phone;

        $data          = ['expire_at' => $expireAt, 'app_id' => $appId, 'message' => $message, 'phone' => $phone];
        $redirectLinks = $this->clientService->getRedirectLinks($data);

        return response()->json($redirectLinks);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'username' => 'required',
           'password' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json(['message' => 'validation error'],402);
        }

        if(\Auth::guard('whatsapp')->validate($request->only(['username', 'password']))){
            $whatsappApp = WhatsappApp::where('username', $request->username)->first();
            $whatsappApp->tokens()->delete();
            $token = $whatsappApp->createToken('api_token')->plainTextToken;
            return response()->json(['token' => $token]);
        }
        return response()->json(['message' => 'validation error'],402);
    }
}
