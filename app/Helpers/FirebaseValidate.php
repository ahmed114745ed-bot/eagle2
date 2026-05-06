<?php

namespace App\Helpers;

use Kreait\Firebase\Factory;

class FirebaseValidate
{

    /**
     * @param $idToken
     * @throw FailedToVerifyToken || \Exception
     * @return mixed|null
     */
    public static function validateIdToken($idToken)
    {
        $credentialPath = storage_path('app/credentials/firebase_credentials.json');

        $factory = (new Factory())->withServiceAccount($credentialPath);
        $auth = $factory->createAuth();
        $verifiedIdToken = $auth->verifyIdToken($idToken);

        // Authentication token is valid
        return $verifiedIdToken->claims()->get('sub');
    }
}
