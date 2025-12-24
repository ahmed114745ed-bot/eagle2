<?php

namespace App\Http\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;


class FirebaseAuthService
{
    protected Auth $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'));

        $this->auth = $factory->createAuth();
    }

    /**
     * Create Firebase Custom Token using UID
     */
    public function createCustomToken(string $uid): string
    {
        return $this->auth->createCustomToken($uid)->toString();
    }
}
