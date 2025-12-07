<?php

namespace Modules\UsersWallet\Services;

use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Modules\UsersWallet\Entities\UserWallet;
use Modules\UsersWallet\Entities\WalletLog;
use Modules\UsersWallet\Entities\WalletTemplate;
use Modules\UsersWallet\Repositories\Eloquent\UserLogRepository;
use Modules\UsersWallet\Repositories\WalletRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\Common;
use App\Tik\Repositories\WareRepository;
use App\Http\Resources\MomentGiftResource;
use App\Tik\Repositories\GiftLogRepository;
use Modules\Moment\Entities\MomentUserGift;
use App\Http\Resources\AudioGiftsListResource;
class WalletService
{
    protected $walletRepo;

    public function __construct(WalletRepositoryInterface $walletRepo ,private readonly GiftLogRepository $GiftLogRepository,private readonly UserLogRepository $userCoinLogRepository)
    {
        $this->walletRepo = $walletRepo;
    }
 
    public function transfer(int $fromUserId, int $toUserId, float $amount)
    {
        DB::beginTransaction();

            try {
                $fromWallet = $this->walletRepo->getWalletByUserId($fromUserId)
                            ?? $this->walletRepo->createWallet(['user_id' => $fromUserId, 'balance' => 0]);

                 $available = wallet_available_by_wallet($fromWallet);
                    if ($available < $amount) {
                            throw new \Exception('Insufficient balance.');
                    }

                $toWallet = $this->walletRepo->getWalletByUserId($toUserId)
                        ?? $this->walletRepo->createWallet(['user_id' => $toUserId, 'balance' => 0]);
                $this->walletRepo->updateWallet($fromWallet->id, ['cut_amount' => $fromWallet->cut_amount + $amount]);
               
                $this->walletRepo->createLog([
                    'wallet_id' => $fromWallet->id,
                    'user_id' => $fromUserId,
                    'amount' => -$amount,
                    'operation' => 'transfer',
                    'type' => 'transfer',
                    'before_amount' => $fromWallet->balance - $fromWallet->cut_amount - $fromWallet->pending_amount ,
                    'after_amount' => wallet_available_by_user($fromUserId)
                ]);

                $this->walletRepo->updateWallet($toWallet->id, ['balance' => $toWallet->balance + $amount]);
                $this->walletRepo->createLog([
                    'wallet_id' => $toWallet->id,
                    'user_id' => $toUserId,
                    'amount' => $amount,
                    'operation' => 'transfer',
                    'type' => 'transfer',
                    'before_amount' => $toWallet->balance -  $toWallet->cut_amount - $toWallet->pending_amount,
                    'after_amount' =>  wallet_available_by_user($toUserId)
                ]);

                DB::commit();

                return ['status' => 'success',];

            } catch (Exception $e) {
                DB::rollBack();
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
    
    }

    public function getWalletTransactions($request)
    {
        $user = Auth::user();
        $type = $request['type'];

        if (!in_array($type, ['add', 'cut'])) {
            throw new Exception('Invalid transaction type. Allowed values: add, cut');
        }

        $transactions = WalletTransaction::whereHas('wallet', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('type', $type)
            ->latest()
            ->paginate(15);

        return $transactions;
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

       public function history($userId, $type,$startDate, $endDate, $page, $perPage)
    {
        return $this->userCoinLogRepository->index($userId, $type, $startDate, $endDate,$page, $perPage);
    }



    public function getProfitsByType($params)
    {
        $userId = Auth::user()->id;
        $type = $params['type'] ?? 'user';
        return $this->walletRepo->getProfitsByType($userId,$type);
    }

    public function getLatestTransactions($params)
    {
        $userId = Auth::user()->id;
        $limit = $params['limit'] ?? 20;

        return $this->walletRepo->getLatestTransactions($userId, $limit);
    }
    
}
