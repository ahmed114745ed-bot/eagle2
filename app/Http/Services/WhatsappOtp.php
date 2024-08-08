<?php

namespace App\Http\Services;

use App\Jobs\WhatsAppJob;
use App\Models\Code;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nette\Schema\ValidationException;

class WhatsappOtp
{

    /**
     * @throws ValidationException
     */
    public function sendOtpMessage(string $phone): void
    {
        $data = $this->getCodeInfo($phone);

        if ($data?->count == 4) {
            throw new ValidationException(__('you spend all chances'));
        }

        $delay   = $data?->count > 1  ? Carbon::createFromTimeString($data?->created_at > now() ? $data?->created_at : now())->addMinutes(2) : now();
        $otp     = $this->generateOtp($phone, $delay);
        $message = 'verification code is : ' . $otp->code;
        dispatch(new \Modules\Whatsapp\Jobs\WhatsappOtp($phone, $message))->delay($delay);

    }

    /**
     * @return Model(['phone', 'created_at', 'count'])
     * */
    public function getCodeInfo(string $phone)
    {
        return Code::query()->selectRaw('phone, max(created_at) as created_at, count(code) as count')->where('phone', $phone)->whereDate('created_at', today())->groupBy('phone')->first();
    }

    public function generateOtp(string $phone, Carbon $createdAt)
    {
        return Code::query()->create([
                                  'phone'      => $phone,
                                  'code'       => rand(100000, 900000),
                                  'created_at' => $createdAt,
                                  'updated_at' => $createdAt
                              ]);
    }

    public function isValidate(string $phone, string $code): bool
    {
        $carbon = Carbon::now();
        $codeAm =
            Code::query()->where('phone', $phone)->where('created_at', '>', $carbon->copy()->subHours()->toDate())->where('created_at', '<=', $carbon)->latest('created_at')->first();

        return $codeAm->code == $code;
    }

    public function resetCodes(string $phone)
    {
        return Code::query()->where('phone', $phone)->delete();
    }


}
