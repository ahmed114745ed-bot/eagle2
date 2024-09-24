<?php

namespace App\Http\Services;

use App\Jobs\WhatsAppJob;
use App\Models\Code;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nette\Schema\ValidationException;

class WhatsappOtp
{

    /**
     * @throws ValidationException
     */
    public function sendOtpMessage(string $phone)
    {
        $data = $this->getCodeInfo($phone);

        if ($data?->count >= 10) {
            throw new ValidationException(__('you spend all chances'));
        }else if (Carbon::createFromTimeString($data?->created_at ?? now()->copy()->subDay()->toDateTimeString())->addMinutes(2) > now()) {
            throw new ValidationException(__('whatsapp.wait-2-minutes'));
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
        return Code::query()->where('phone',$phone)->where('code', $code)->where('created_at', '>', Carbon::now()->subHours()->toDate())->exists();

    }

    public function resetCodes(string $phone)
    {
        return Code::query()->where('phone', $phone)->delete();
    }

}
