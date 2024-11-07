<?php

namespace App\Http\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Code;
use App\Jobs\WhatsAppJob;
use Nette\Schema\ValidationException;
use Illuminate\Database\Eloquent\Model;

class WhatsappOtp
{

    /**
     * @throws ValidationException
     */
    public function sendOtpMessage(string $phone)
    {
        $data = $this->getCodeInfo($phone);

        \Log::info('this is the phone '. $phone);

        if ($data?->count >= 10) {
            throw new \Exception(__('you spend all chances'));
        }else if (Carbon::createFromTimeString($data?->created_at ?? now()->copy()->subDay()->toDateTimeString())->addMinutes(2) > now()) {
            throw new Exception(__('whatsapp.wait-2-minutes'));
        }


        $delay   = now();
        $otp     = $this->generateOtp($phone);
//
        $message = $otp->code;
       // $message ='verification code is : '. $otp->code;
        dispatch(new WhatsAppJob($phone ,$message));

    }

    /**
     * @return Model(['phone', 'created_at', 'count'])
     * */
    public function getCodeInfo(string $phone)
    {
        return Code::query()->selectRaw('phone, max(created_at) as created_at, count(code) as count')->where('phone', $phone)->whereDate('created_at', today())->groupBy('phone')->first();
    }

    public function generateOtp(string $phone)
    {
        $this->resetCodes($phone);
        $otp = new Code();
        $otp->phone = $phone;
        $otp->code = rand(100000 , 900000);
        $otp->save();
        return $otp;
    }

    public function isValidate(string $phone, string $code): bool
    {
        \Log::info("Phone matches: ", [Code::query()->where('phone', $phone)->exists()]);
        \Log::info("Code matches: ", [Code::query()->where('code', $code)->exists()]);
        \Log::info("Created at matches: ", [Code::query()->where('created_at', '>', Carbon::now()->subHours()->toDate())->exists()]);
        //error_log("Phone matches: " . [Code::query()->where('phone', $phone)->exists()]);
        //error_log("Code matches: " . [Code::query()->where('code', $code)->exists()]);
        //error_log("Created at matches: " . [Code::query()->where('created_at', '>', Carbon::now()->subHours()->toDate())->exists()]);
        
        return Code::query()->where('phone',$phone)->where('code', $code)->where('created_at', '>', Carbon::now()->subHours()->toDate())->exists();
    }

    public function resetCodes(string $phone)
    {
        return Code::query()->where('phone', $phone)->delete();
    }

}
