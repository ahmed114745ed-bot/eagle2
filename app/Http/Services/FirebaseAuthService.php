<?php

namespace App\Http\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;


class FirebaseAuthService
{
    protected Auth $auth;

    public function __construct()
    {
        $credentialsPath = config('firebase.credentials');
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \RuntimeException('Firebase credentials file not found: ' . ($credentialsPath ?: 'not configured'));
        }
        $factory = (new Factory)
            ->withServiceAccount($credentialsPath);

        $this->auth = $factory->createAuth();
    }

    /**
     * Create Firebase Custom Token using UID
     */
    public function createCustomToken(string $uid): string
    {
        return $this->auth->createCustomToken($uid)->toString();
    }

    public function createGuest(): array
    {
        $user = $this->auth->createUser([
            'disabled' => false,
        ]);

        $uid = $user->uid;

        $customToken = $this->auth->createCustomToken($uid)->toString();

        return [
            'uid'   => $uid,
            'token' => $customToken,
        ];
    }
}
