<?php

namespace Modules\UsersWallet\Services;

use App\Helpers\Common;
use App\Models\Setting;
use App\Enums\UserCoinLogType;
use App\Helpers\UserCoinLogHelper;
use Modules\UsersWallet\Repositories\Eloquent\ExchangeLogRepository;
use Modules\UsersWallet\Repositories\Eloquent\ExchangeRepository;



class ExchangeService
{
    public function __construct(
        private  readonly ExchangeRepository $exchangeRepository,
        private readonly ExchangeLogRepository $exchangeLogRepository
    ) {}


    public function index($type)
    {
        return $this->exchangeRepository->getByType($type);
    }

    public function exchangeSetting()
    {
        return   \Cache::rememberForever('exchange_coin_percentage', function () {
            $setting = Setting::where('key', 'exchange_coin_percentage')->first();
            return $setting?->value ?? 1;
        });
    }

    public function create($user, $exchangeId)
    {
        $ex = $this->exchangeRepository->findById($exchangeId);

        if (!$ex) throw new \Exception('not found');
        if ($user->type_user != 0) throw new \Exception('not allowed');
        if ($user->exchange_diamonds < $ex->diamonds) throw new \Exception('balance low');


        $data = [
            'user_id' => $user->id,
            'diamonds' => $ex->diamonds,
            'value' => $ex->value,
            'type' => $ex->type,
            'operation_no' => rand(11111111, 99999999),
        ];

        $this->exchangeLogRepository->create($data);
        $user->exchange_diamonds -= $ex->diamonds;

        if ($user->exchange_diamonds <= 0) {
            $user->exchange_diamonds = 0;
        }
        if ($ex->type == 0) {

            $amountBefore =  $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                $ex->value,
                $amountBefore,
                UserCoinLogType::EXCHANGE,
            );

            $user->di += $ex->value;
        } elseif ($ex->type == 1) {
            $user->gold +=  $ex->value;
        }
        $user->save();
        return true;
    }

    public function createExchange($user, $diamonds, $exValue)
    {
        if($user->type_user == 1 ) throw new \Exception(__('you are host you can not exchange diamonds'));
        if ($user->exchange_diamonds < $diamonds) throw new \Exception(__('balance low'));
        //  if (!ctype_digit($exValue)) throw new \Exception(__('you should exchange number of diamond'));

        $setting = Common::getSettingValue('exchange_coin_percentage') ?? 1;
        $exchangeCoin = (($setting / 100) * $diamonds);
        if (floor($exchangeCoin) != $exchangeCoin) throw new \Exception(__('you should exchange number of diamond'));

        $exchangeCoin = (int) $exchangeCoin;
        if (ctype_digit($exValue) != $exchangeCoin) throw new \Exception(__('calculus not true'));
        $data = [
            'user_id' => $user->id,
            'diamonds' => $diamonds,
            'value' => $exValue,
            'type' => 0,
            'operation_no' => rand(11111111, 99999999),
        ];

        $this->exchangeLogRepository->create($data);
        $user->exchange_diamonds -= $diamonds;

        if ($user->exchange_diamonds <= 0) {
            $user->exchange_diamonds = 0;
        }

        $amountBefore =  $user->di;
        UserCoinLogHelper::logByType(
            $user->id,
            $exValue,
            $amountBefore,
            UserCoinLogType::EXCHANGE,
        );

        $user->di += $exValue;

        $user->save();
        return true;
    }

    public function getExchangeLog($userId, $type)
    {
        return $this->exchangeLogRepository->getExchanges($userId, $type);
    }

    public function all($id, $perPage, $page)
    {
        return $this->exchangeRepository->all($id, $perPage, $page);
    }

    public function createDashboard($request)
    {

        $this->exchangeRepository->create($request->all());
        return true;
    }

    public function update($id, $request)
    {

        $this->exchangeRepository->update($request->all(), $id);
        return true;
    }

    public function delete($id)
    {

        $data = $this->exchangeRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function show($id)
    {
        return $this->exchangeRepository->findOrFail($id);
    }
}
