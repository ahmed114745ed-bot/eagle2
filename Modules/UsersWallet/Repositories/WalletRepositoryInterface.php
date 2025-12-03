<?php

namespace Modules\UsersWallet\Repositories;

interface WalletRepositoryInterface
{
    public function getWalletByUserId(int $userId);
    public function updateWallet(int $walletId, array $data);
    public function createLog(array $data);
    public function createWallet(array $data);

}