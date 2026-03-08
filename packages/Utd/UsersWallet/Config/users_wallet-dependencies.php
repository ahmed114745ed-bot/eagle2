<?php

return [
    'dependencies' => [
        'repositories' => [
            'wallet_repository' => \Utd\UsersWallet\Repositories\Eloquent\WalletRepository::class,
        ],
        'services' => [
            'wallet_service' => \Utd\UsersWallet\Services\WalletService::class,
        ],
    ],
];
