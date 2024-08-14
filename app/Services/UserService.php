<?php

namespace App\Services;

use App\Facades\UserHandling;
use App\Helpers\Common;
use App\Http\Services\WhatsappOtp;
use App\Http\Services\WhatsappWebhook;
use App\Repositories\User\UserRepository;
use App\Repositories\PackRepository;

class UserService
{
    protected $userRepository;
    protected $packRepository;

    public function __construct(UserRepository $userRepository,PackRepository $packRepository)
    {
        $this->userRepository = $userRepository;
        $this->packRepository = $packRepository;

    }

    public function searchUsers($key)
    {
        $perPage = 10;
        $currentPage = request()->has('page') ? request()->page : 1;

        return $this->userRepository->search($key, $perPage, $currentPage);
    }

    public function searchUsersWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchWithPage($key, $page, $perPage);
    }

    public function searchUsersInAgency($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchUserAgency($key, $page, $perPage);
    }

    public function bind($user, $request)
    {
        if ($request->google_id && !$user->google_id) {
            $ex = $this->userRepository->checkByGoogleId($user->id, $request->google_id);
            if ($ex) throw new \Exception('this google_account is restricted with another account');
            $user->google_id = $request->google_id;
        }

        if ($request->facebook_id && !$user->facebook_id) {
            $ex = $this->userRepository->checkByFaceBookId($user->id, $request->facebook_id);
            if ($ex) throw new \Exception('this facebook_account is restricted with another account');
            $user->facebook_id = $request->facebook_id;
        }

        if ($request->phone && !$user->phone) {
            $ex = $this->userRepository->checkByPhone($user->id, $request->phone);
            if ($ex) return throw new \Exception('this phone is restricted with another account');
            if (!$request->has('is_whatsapp')) {
                if (!$request->code || !$request->password) throw new \Exception('missing params');

                $whatsappOtpService = new WhatsappOtp();
                $phone              = $request->phone;
                $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
                if (!$isValid)  throw new \Exception(__('api_responses.invalid_code'));
                $whatsappOtpService->resetCodes($phone); // $code = Code::query ()->where ('phone',$request->phone)->where('code',$request->vr_code)->first ();
                // if (!$code) return Common::apiResponse (0,'this phone not verified',null,310);

            } else {
                if (!$request->password) throw new \Exception('missing params');
                $whatsappWebhookValidate = (new WhatsappWebhook())->getLastValidatedPhone($request->phone);
                if (!$whatsappWebhookValidate) throw new \Exception(__('current phone not verified'));
            }
            $user->phone    = $request->phone;
            $user->password = $request->password;   // $code->delete ();
            UserHandling::AddUserVip($user, 'join_account');
        }
        $this->userRepository->updateUser($user);
        return true;
    }

    public function processUserData($user, $deviceToken)
    {
        $this->userRepository->updateDeviceToken($user, $deviceToken);

        $currentTime = time();
        // $this->dailyPrizeService->reset(Carbon::createFromTimestamp($user->real_online_time), $user->id);

        $this->userRepository->updateOnlineTime($user, $currentTime);

        $userWithMedals = $this->userRepository->getUserWithMedals($user->id);

        return $userWithMedals;
    }

    public function unlockDressHand($userId)
    {
        $vip = Common::getLevel($userId, 3);
        $types = [4, 5, 6, 7, 8];
        $ids = $this->packRepository->getTargetIdsByUserAndType($userId, $types);
        
        $wares = $this->packRepository->getWaresByConditions($vip, $types, $ids);
        
        if ($wares->isEmpty()) return 0;

        foreach ($wares as $ware) {
            $pack = $this->packRepository->getExistingPack($userId, $ware->type, $ware->id);
            if ($pack) continue;

            $data = [
                'user_id'   => $userId,
                'type'      => $ware->type,
                'target_id' => $ware->id,
                'expire'    => $ware->expire ? time() + ($ware->expire * 86400) : 0,
                'is_read'   => 1,
            ];

            $this->packRepository->createPack($data);
        }
        return count($wares);
    }

    public function updateLocation($userId,$lat,$log)
    {
        $this->userRepository->updateLocation($userId,$lat,$log);
    }
}
