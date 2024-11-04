<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\CountryRepository;
use Modules\SwitchAccount\Traits\SwithAccountLogin;
use Modules\SwitchAccount\Http\Services\SwitchAccountServices;

class AuthService
{
    use SwithAccountLogin;
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CountryRepository $countryRepository,
    ) {
    }


    public function registration($request)
    {
        if ($this->userRepository->findByPhoneUser($request->phone))  throw new \Exception('already exists');

        $data = [
            'phone' => $request->phone, 'password' => $request->password,
        ];

        $user = $this->userRepository->create($data);

        if (\request('tags') && is_array(\request('tags'))) {
            $user->tags()->attach(\request('tags'));
        }

        if (!$request->country_id) {
            $country = $this->countryRepository->findByPhoneCode('101');
            $user->country_id = @$country->id ;
        }
        $user->is_points_first = 1;
        $user->save();
        $token = $user->createToken('api_token')->plainTextToken;
        UserHandling::AddUserVip($user, 'register');
        return [$user, $token];
    }

    public function loginWithPassword($request)
    {
        $user = $this->userRepository->findByPhoneUser($request['phone']);
        if (!$user || !Hash::check($request['password'], $user->password)) {
            throw new \Exception('credentials does`t match');
        }


        $this->rule($user, '', @$request['device_token'], $request);

        $token = $user->createToken('api_token')->plainTextToken;
        $this->userRepository->updateIsLogout($user, 0);
        return [$user, $token];
    }

    public function loginWithGoogle($request)
    {
        $user = $this->userRepository->findByGoogleId($request['google_id']);
        if (!$user) {
            if ($this->userRepository->checkTrashedEmail($request['email'], $request['google_id'])) {
                $resource = [
                    'google_id' => $request['google_id'],
                    'status' => true,
                    'email' => $request['email'],
                    'name' => $request['name'],
                ];
                return  [[], '', $resource];
                Common::apiResponse(false, 'email already taken', $resource, 405);
            } else {
                $country = $this->countryRepository->findByPhoneCode('101');
                $data = [
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'google_id' => $request['google_id'],
                    'country_id' => @$country->id ?: 0,
                    'is_points_first' => 1,
                    'status' => 1
                ];
                $user = $this->userRepository->create($data);
                if (\request('tags') && is_array(\request('tags'))) {
                    $user->tags()->attach(\request('tags'));
                }

                $user->country_id = @$country->id ?: 0;
                $user->is_points_first = 1;
                $user->save();
            }
        }
        $this->rule($user, '', @$request['device_token'], $request);
        $token = $user->createToken('api_token')->plainTextToken;
        $this->userRepository->updateIsLogout($user, 0);
        return [$user, $token, []];
    }

    public function loginWithApple($request, $unique_id)
    {
        $user = $this->userRepository->findByEmail($request['email']);
        if (!$user) {
            $data = [
                'name' => implode('@', explode('@', $request['email'], -1)),
                'email' => $request['email'],
                'apple_id' => $unique_id,
            ];
            $user = $this->userRepository->create($data);
        }
        $this->rule($user, '', @$request['device_token'], $request);

        $token = $user->createToken('api_token')->plainTextToken;
        $this->userRepository->updateIsLogout($user, 0);
        return [$user, $token];
    }

    public function loginWithHuawei($data)
    {
        $user = $this->userRepository->findByHuawei($data['huawei_id']);
        if (!$user) {
            if ($this->userRepository->checkEmail($data['huawei_id'])) {
                throw new \Exception('email already taken',);
            } else {
                $verify = $this->verifyHuaweiID($data['id_token'], $data['huawei_id']);
                if ($verify == false) {
                    throw new \Exception('هناك مشكله حاول مره اخري');
                }
                $country = $this->countryRepository->findByPhoneCode('101');
                $dataUser = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'huawei_id' => $data['huawei_id'],
                    'country_id' => @$country->id ?: 0,
                    'is_points_first' => 1,
                ];
                $user = $this->userRepository->create($dataUser);
            }
        }

        $this->rule($user, '', @$data['device_token'], $data);

        $token = $user->createToken('api_token')->plainTextToken;
        $this->userRepository->updateIsLogout($user, 0);
        return [$user, $token];
    }

    public function recallAccount($request)
    {
        $user = $this->userRepository->findByTrashedEmail($request['email'], $request['google_id']);
        if (!$user)  throw new \Exception('something wrong');
        if ($request['status'] == 0) {
            $user->forceDelete();
            $data = [
                'name' => @$request['name'],
                'email' => $request['email'],
                'google_id' => $request['google_id'],
                'device_token' => @$request['device_token'],
                'status' => 1
            ];
            $user = $this->userRepository->create($data);
        } else {
            $user->restore();
        }
        $this->rule($user, '', @$request['device_token'], $request);

        $token = $user->createToken('api_token')->plainTextToken;
        $this->userRepository->updateIsLogout($user, 0);
        return [$user, $token];
    }

    
 

    public function logoutAsConfiguration($user)
    {
        if (Common::getConf('login_from_only_one_device') == 'yes') {
            //            $user->tokens()->delete();
        }
    }


    public function rule($user, $type, $deviceToken, $data)
    {
        if ($this->checkIsSameAccount($user->id, $data)) {
            throw new \Exception(__($this->getSameAccountMessage()));
        }
        $message = UserHandling::hasReasonOfBan($user->uuid, \request());

        if ($message) {
            throw new \Exception($message);
        }

        $this->userRepository->updateDeviceToken($user, $deviceToken);
        (new SwitchAccountServices())->saveDeviceUser($user->id, $deviceToken);
        $this->logoutAsConfiguration($user);
        return true;
    }


    public function verifyHuaweiID($idToken, $huaweiId)
    {
        $url = 'https://oauth-login.cloud.huawei.com/oauth2/v3/tokeninfo';

        $response = Http::asForm()->post($url, [
            'id_token' => $idToken
        ]);
        if ($response->successful()) {
            $response = $response->json();
            if ($huaweiId == $response['sub']) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}
