<?php

namespace Modules\Wallet\Services;

use App\Helpers\Common;
use App\Models\Agency;
use App\Models\Charge;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\Enum\WalletEnum;

class WalletService
{
    public UserWallet $userWalletModel;
    public WalletTransaction $walletTransactionModel;
    public User $userModel;
    public Agency $agencyModel;
    public function __construct(UserWallet $userWallet, WalletTransaction $walletTransaction, User $user, Agency $agency)
    {
        $this->userWalletModel = $userWallet;
        $this->walletTransactionModel = $walletTransaction;
        $this->userModel = $user;
        $this->agencyModel = $agency;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function makeTransaction(array $data): JsonResponse
    {
        $sender = auth()->user();
        $amount = $data['amount'];
        $receiverType = '';

        CheckSystemConfigs::checkSystemConfigs();

        $userWallet = CheckAvailableBalance::checkAvailableBalance($this->userWalletModel, $amount);

        if ($data['type'] == WalletEnum::USER->value){
            $receiver = CheckUserExistence::userExists($this->userModel, $data['receiver_id']);
            info($receiver);
            $receiverType = 'user';
        }

        if ($data['type'] == WalletEnum::AGENCY->value){
            $receiver = CheckAgencyExistence::agencyExists($this->agencyModel, $data['receiver_id']);
            $receiverType = 'agency';
        }

        CheckSystemConfigs::checkUserTransferAvailability($sender, $receiver);

        $rate = CheckSystemConfigs::getConfigRate();

        $coins = $amount * $rate;

        return $this->startTransaction($userWallet, $receiver, $sender, $amount, $coins, $receiverType);
    }

    public function startTransaction(UserWallet $userWallet, $receiver,User $sender, int $amount, int $coins,string $receiverType): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userWallet->increment('cut_amount', $amount);
            $receiver->increment('coins', $coins);

            $this->walletTransactionModel::create([
                'user_id' => $sender->id,
                'type' => 'cut',
                'transactions_type' => 'user_transaction',
                'value' => $amount,
            ]);

            $data = [
                'charger_id' => $sender->id,
                'charger_type' => 'user',
                'user_id' => $receiver->id,
                'agency_id' => $receiver->id,
                'user_type' => $receiverType,
                'amount' => $coins,
                'amount_type' => 2,
                "usd" =>  $amount ?? 0,
                'is_used_transferred' => 1,
            ];

            Charge::create($data);

            DB::commit();
            return Common::apiResponse(1, 'success', $data, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), 400);
        }
    }

}
