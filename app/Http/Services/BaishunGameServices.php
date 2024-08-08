<?php

namespace App\Http\Services;

use App\Helpers\Common;
use App\Models\Banner;
use Illuminate\Support\Facades\Http;

class BaishunGameServices
{
    public function getUniqueId($signatureNonce, $signature ,$timestamp)
    {
        $url = config('app.baishun_server_url');
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, [
            'signature_nonce' => $signatureNonce,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
        $data = json_decode($response->getBody(), true);
        if ($data) {
            return $data['unique_id'];
        }
        return ;
    }
}
