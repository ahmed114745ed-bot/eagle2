<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\Agency;
use App\Models\User;
use App\Tik\Repositories\AgencyRepository;
use App\Tik\Repositories\AgencySalaryRepository;
use App\Tik\Repositories\ChargeRepository;
use App\Tik\Repositories\CoinLogRepository;
use App\Tik\Repositories\RoomSalaryRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\UserSalaryRepository;
use Modules\Achievement\Http\Services\UserAchievementService;


class ChargeRepoService
{
    public function __construct(
        private readonly ChargeRepository       $chargeRepository,
        private readonly RoomSalaryRepository   $roomSalaryRepo,
        private readonly UserRepository         $userRepository,
        private readonly UserSalaryRepository   $userSalaryRepository,
        private readonly AgencyRepository       $agencyRepository,
        private readonly AgencySalaryRepository $agencySalaryRepository,
        private readonly CoinLogRepository $coinLogRepository
    )
    {

    }

    public function create(array $data)
    {
        return $this->chargeRepository->create($data);
    }


    public function chargeCoinsFromOwner($amount, $userId, $toUserUuId)
    {
        $userResve = $this->userRepository->searchUser($toUserUuId);
        if (!$userResve) {
            throw new \Exception('this user not found');
        }

        $user_id = $userResve->id;
        $room = $userResve->ownerRoom;

        if (!isset($room)) {
            throw new \Exception('room not founded');
        }
        $salary = $room->salary;
        if ($salary < $amount) {
            throw new \Exception('Low Balance');
        }
        try {
            \DB::beginTransaction();
            // Increment 'di' column for the user
            $coinPrise = Common::getConf('one_usd_value_in_coins') ?? 50;
            $coins = $coinPrise * $amount;
            $userType = $userResve->user_type;

            $data = [
                'userId' => $userId,
                'chargeType' => 'Host agent',
                'receiverId' => $user_id,
                'type' => $userType,
                'amount' => $coins,
                'amountType' => 2,
                'isTransferred' => true,
            ];

            $this->create($data);
            $this->roomSalaryRepo->incrementCutAmount($room->id, $amount);
            $this->userRepository->incrementCoins($toUserUuId, $coins);

            \DB::commit();
            (new UserAchievementService())->insertCharging($userResve, $coins);
            UserCommon::UserEarnedInvitation($userResve->id, $coins);
            return true; //
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception('An error occurred, please try again later');
        }
    }


    public function chargeTo(User $fromUser, User $toUser, $coins, $isRoomTarget, $usd)
    {
        $chargeType = $isRoomTarget ? 'room_owner' : 'host';

        try {


            // update cut_amount last record of user salaries
            if (!$isRoomTarget) {

                $this->userSalaryRepository->incrementCutAmount($fromUser->id, $usd);
            } else {
                $this->roomSalaryRepo->incrementCutAmount($fromUser->ownerRoom?->id, $usd);
            }
            $this->charge($fromUser, $toUser, $chargeType, $coins, $coins);

            if ($toUser instanceof User) {
                (new UserAchievementService())->insertCharging($toUser, $coins);
            }
            UserCommon::UserEarnedInvitation($toUser->id, $coins);
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception('An error occurred, please try again later');
        }
    }


    public function sendMoney(User $sender, $receiverUuid, $count)
    {

        $agency = $this->agencyRepository->findAgencyByOwnerId($sender->id, 1);
        if (!$agency || $agency->status == 0) throw new \Exception(__('api_responses.canNotCharge'));
        $userReceiver = $this->userRepository->searchUser($receiverUuid);
        if (!$userReceiver) throw new \Exception('this user not found');

        // Decrement sender's coins
        $this->userRepository->decrementUserCoins($sender, $count);
        $percentage = Common::getConf("one_usd_value_in_coins") ?? 1;
        $usd = $count / $percentage;

        $this->charge($sender, $userReceiver, 'freight forwarder', $count, $usd);
        return $userReceiver;

    }

    public function getChargeUserHistory($userId, $type, $by_date = null, $chargeType = null, $searchKey = null)
    {
        $charge = $this->chargeRepository->getChargeHistory($chargeType);
        if ($type == 'received') {
            $charge = $charge/*->where('user_type', $charger_type)*/ ->where('user_id', $userId);
        }
        if ($type == 'sent') {
            $charge = $charge/*->where('charger_type', $charger_type)*/ ->where('charger_id', $userId);
        }

        if ($searchKey != null) {
            $charge = $charge->when($searchKey, fn($query) => $query->whereHas('sender', fn($q) => $q->where('uuid', 'like', $searchKey)));
        }
        if ($by_date) {
            $charge = $charge->where('created_at', 'like', "%$by_date%");
        }

        return $charge->orderByDesc('created_at')->get();
    }

    public function chargeDollarForOwner(User $sender, $receiverUuid, $count)
    {
        try {
            $receiver = $this->userRepository->searchUser($receiverUuid);
            if (!$receiver) throw new \Exception('this user not found');

            $agency = $this->agencyRepository->findByStatus($sender->agency_id);
            if (!isset($agency))
                throw new \Exception('agency not founded');

            if ($agency->status == 0 || $agency->app_owner_id != $sender->id)
                throw new \Exception(__('api_responses.canNotCharge'),);


            $salary = $agency->salary;

            if ($salary < $count) throw new \Exception('Low Balance');

            // DB::beginTransaction();
            // Increment 'di' column for the user
            // $coinPrise = Common::getConf('one_usd_value_in_coins') ?? 50;
            $coinPrise = Common::getCoinsValue('user_coins');
            $numDi = $coinPrise * $count;
            $this->charge(sender: $sender, receiver: $receiver, chargeType: 'Host agent', amount: $numDi, transferred: true);
            $this->agencySalaryRepository->incrementCutAmount($agency->id, $count);
            return [$receiver, $numDi, $salary];
        } catch (\Exception $e) {
            // \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }


    public function charge(User $sender, User $receiver, $chargeType, $amount, $usd = null, $transferred = false)
    {
        $type = $receiver->user_type;
        $this->userRepository->incrementUserCoins($receiver, $amount);
        $data = [
            'charger_id' => $sender->id,
            'charger_type' => $chargeType,
            'user_id' => $receiver->id,
            'user_type' => $type,
            'amount' => $amount,
            'amount_type' => 2,
            "usd" => $usd != null ? $usd : $amount,
            'is_used_transferred' => $transferred,
        ];
        $this->create($data);
    }

    public function getCoinLogs($userId, string $searchKey = null)
    {
        return $this->coinLogRepository->getCoinsByUserId($userId, $searchKey);
    }

    public function chargeToAgency(User $fromUser, Agency $toAgency, $coins, $isRoomTarget, $usd)
    {
        $chargeType = $isRoomTarget ? 'room_owner' : 'host';

        try {


            // update cut_amount last record of user salaries
            if (!$isRoomTarget) {

                $this->userSalaryRepository->incrementCutAmount($fromUser->id, $usd);
            } else {
                $this->roomSalaryRepo->incrementCutAmount($fromUser->ownerRoom?->id, $usd);
            }
            $this->chargeAgencyNew($fromUser, $toAgency, $chargeType, $coins, $usd);

            // if ($toUser instanceof User) {
            //     (new UserAchievementService())->insertCharging($toUser, $coins);
            // }
            // UserCommon::UserEarnedInvitation($toUser->id, $coins);
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception('An error occurred, please try again later');
        }
    }


    public function chargeAgencyNew(User $sender, Agency $receiver, $chargeType, $amount, $usd = null, $transferred = false)
    {
        $type = $receiver->owner?->user_type ?? '';

        $receiver->increment('coins', $amount);

        $data = [
            'charger_id' => $sender->id,
            'charger_type' => $chargeType,
            'user_id' => $receiver->id,
            'agency_id' => $receiver->id,
            'user_type' => 'agency',
            'amount' => $amount,
            'amount_type' => 2,
            "usd" =>  $usd ?? 0,
            'is_used_transferred' => $transferred,
        ];
        $this->create($data);
    }

    public function chargeDollarForOwner_to_agency(User $sender, $receiverid, $count)
    {
        
        try {

            $receiver = Common::searchAgency($receiverid);
           
            if (!$receiver ) throw new \Exception( 'this  not found');

            if ($receiver->is_frozen == 1) {
                throw new \Exception( __('api_responses.frozen'));
            }
            $agency = $this->agencyRepository->findByStatus($sender->agency_id);
            if (!isset($agency)) throw new \Exception('agency not founded');
            if ($agency->is_frozen == 1) {
                throw new \Exception( __('api_responses.AgencyFrozen'));
            }
            if ($agency->status == 0 || $agency->app_owner_id != $sender->id)
                throw new \Exception(__('api_responses.canNotCharge'),);


            $salary = $agency->salary;

            if ($salary < $count) throw new \Exception('Low Balance');

            // DB::beginTransaction();
            // Increment 'di' column for the user
            // $coinPrise = Common::getConf('one_usd_value_in_coins') ?? 50;

            $coinPrise = Common::getCoinsValue('shipping_coins');
            $numDi = $coinPrise * $count;
            $this->chargeAgency(sender: $sender, receiver: $receiver, chargeType: 'Host agent', amount: $numDi, transferred: true);
            $this->agencySalaryRepository->incrementCutAmount($agency->id, $count);
            return [$receiver, $numDi, $salary];
        } catch (\Exception $e) {
            // \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }


    public function chargeAgency(User $sender, Agency $receiver, $chargeType, $amount, $usd = null, $transferred = false)
    {
        $type = $receiver->owner?->user_type ?? '';

        $receiver->increment('coins', $amount);

        $data = [
            'charger_id' => $sender->id,
            'charger_type' => $chargeType,
            'user_id' => null,
            'agency_id' => $receiver->id,
            'user_type' => $type,
            'amount' => $amount,
            'amount_type' => 2,
            "usd" => $usd != null ? $usd : $amount,
            'is_used_transferred' => $transferred,
        ];
        $this->create($data);
    }

    public function userAgencySearch($request)
    {
        try {
            if ($request->type === 'agency') {
                $user = $this->agencyRepository->find($request->id);
            } else {
                $user = $this->userRepository->searchUser($request->id);
            }
            $data = [
                'id' => $user?->id,
                'name' => $user?->name,
            ];
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    } 
    
    
}
