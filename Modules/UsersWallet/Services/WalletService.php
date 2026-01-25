<?php

namespace Modules\UsersWallet\Services;

use Exception;
use App\Models\User;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Enums\UserCoinLogType;
use App\Models\WalletTransaction;
use App\Helpers\UserCoinLogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Tik\Repositories\WareRepository;
use App\Http\Resources\MomentGiftResource;
use App\Tik\Repositories\GiftLogRepository;
use Modules\Moment\Entities\MomentUserGift;
use Modules\UsersWallet\Entities\WalletLog;
use Illuminate\Database\Eloquent\Collection;
use Modules\UsersWallet\Entities\UserWallet;
use App\Http\Resources\AudioGiftsListResource;
use Modules\UsersWallet\Entities\WalletTemplate;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\UsersWallet\Repositories\WalletRepositoryInterface;
use Modules\UsersWallet\Repositories\Eloquent\UserLogRepository;

class WalletService
{
    protected $walletRepo;

    public function __construct(WalletRepositoryInterface $walletRepo, private readonly GiftLogRepository $GiftLogRepository, private readonly UserLogRepository $userCoinLogRepository)
    {
        $this->walletRepo = $walletRepo;
    }

    public function transfer(int $fromUserId, int $toUserId, float $amount , float $usd )
    {

        $app_feature = \Cache::get('host_agency');
        if (!$app_feature) {
            throw new Exception(__('Agency Feature is Disabled, Contact the administration'));
        }
        DB::beginTransaction();

        try {
            $fromWallet = $this->walletRepo->getWalletByUserId($fromUserId)
                ?? $this->walletRepo->createWallet(['user_id' => $fromUserId, 'balance' => 0]);

            $available = wallet_available_by_wallet($fromWallet);
            
            \Log::info('Transfer - Wallet Info', [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'wallet_id' => $fromWallet->id,
                'wallet_balance' => $fromWallet->balance,
                'wallet_cut_amount' => $fromWallet->cut_amount,
                'wallet_pending_amount' => $fromWallet->pending_amount,
                'available' => $available,
            ]);
            
            if ($available < $usd) {
                throw new \Exception('Insufficient balance.');
            }

            $toWallet = $this->walletRepo->getWalletByUserId($toUserId)
                ?? $this->walletRepo->createWallet(['user_id' => $toUserId, 'balance' => 0]);
            $this->walletRepo->updateWallet($fromWallet->id, ['cut_amount' => $fromWallet->cut_amount + $usd]);

            $this->walletRepo->createLog([
                'wallet_id' => $fromWallet->id,
                'user_id' => $fromUserId,
                'amount' => -$usd,
                'operation' => 'transfer',
                'type' => 'transfer',
                'before_amount' => $fromWallet->balance - $fromWallet->cut_amount - $fromWallet->pending_amount,
                'after_amount' => wallet_available_by_user($fromUserId),
                'related_id'  =>  $toUserId

            ]);

            $receiver = User::find($toUserId);
            $amountBefore =  $receiver->di;


            // $type = $receiver->user_type;
            $receiver->increment('di', $amount);
            UserCoinLogHelper::logByType(
                $toUserId,
                $amount,
                $amountBefore,
                UserCoinLogType::APP_CHARGE,
            );

            $this->walletRepo->updateWallet($toWallet->id, ['balance' => $toWallet->balance + $amount]);
            $this->walletRepo->createLog([
                'wallet_id' => $toWallet->id,
                'user_id' => $toUserId,
                'amount' => $amount,
                'operation' => 'transfer',
                'type' => 'transfer',
                'before_amount' => $toWallet->balance -  $toWallet->cut_amount - $toWallet->pending_amount,
                'after_amount' =>  wallet_available_by_user($toUserId),
                'related_id'  =>  $fromUserId
            ]);

            if ($receiver instanceof User) {
                (new UserAchievementService())->insertCharging($receiver, $amount);
            }
            UserCommon::UserEarnedInvitation($receiver->id, $amount);
            UserCommon::addChargeLevel($receiver->id, $amount);
            DB::commit();

            return ['status' => 'success',];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWalletTransactions($request)
    {
        $userId = Auth::user()->id;
        $type = $request['type'] ?? 'add';
        $perPage = $request['per_page'] ?? 15;
        $page = $request['page'] ?? 1;
        return $this->walletRepo->getTransactions($userId, $type, $perPage, $page);
    }

    public function getTemplate($type): Collection|array
    {
        return WalletTemplate::with('fields')->where('type', $type)->get();
    }


    public function diamondsStatistic($userId, $type, $startDate, $endDate, $perPage, $page)
    {
        $list = collect();
        $resourceClass = AudioGiftsListResource::class;

        switch ($type) {
            case 1:
                $list = $this->GiftLogRepository->listGiftReceiveLive($userId, $startDate, $endDate, $perPage, $page);
                break;

            case 2:
                $list = $this->GiftLogRepository->listGiftReceiveAudio($userId, $startDate, $endDate, $perPage, $page);
                break;

            case 3:
                $list = MomentUserGift::selectRaw('user_id, moment_id, gift_id, SUM(num) as total')
                    ->whereHas('moment', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->groupBy('user_id', 'moment_id', 'gift_id')
                    ->with(['user', 'gift'])
                    ->paginate($perPage, ['*'], 'page', $page);

                $resourceClass = MomentGiftResource::class;
                break;
        }

        $resource = $resourceClass::collection($list);

        return [
            'total_diamonds' => $list->sum('total'),
            'list' => $resource,
        ];
    }

    public function history($userId, $type, $startDate, $endDate, $page, $perPage)
    {
        return $this->userCoinLogRepository->index($userId, $type, $startDate, $endDate, $page, $perPage);
    }



    public function getProfitsByType($params)
    {
        $userId = Auth::user()->id;
        $type = $params['type'] ?? 'user';
        return $this->walletRepo->getProfitsByType($userId, $type);
    }

    public function getLatestTransactions($params)
    {
        $userId = Auth::user()->id;
        $limit = $params['limit'] ?? 20;

        return $this->walletRepo->getLatestTransactions($userId, $limit);
    }
}
