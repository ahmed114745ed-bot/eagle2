<?php

namespace Modules\UsersWallet\Repositories\Eloquent;


use Modules\UsersWallet\Entities\UserWallet;
use Modules\UsersWallet\Entities\WalletLog;
use Modules\UsersWallet\Repositories\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    public function getWalletByUserId(int $userId)
    {
        return UserWallet::where('user_id', $userId)->first();
    }

    public function updateWallet(int $walletId, array $data)
    {
        return UserWallet::where('id', $walletId)->update($data);
    }

    public function createLog(array $data)
    {
        return WalletLog::create($data);
    }

    public function createWallet(array $data)
    {
        return UserWallet::create($data);
    }

}
