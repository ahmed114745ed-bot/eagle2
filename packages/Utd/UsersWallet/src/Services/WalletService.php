<?php

namespace Utd\UsersWallet\Services;

use Utd\UsersWallet\Entities\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Utd\UsersWallet\Entities\UserWallet;
use Utd\UsersWallet\Entities\WalletLog;
use Utd\UsersWallet\Entities\WalletTemplate;
use Utd\UsersWallet\Repositories\Eloquent\UserLogRepository;
use Utd\UsersWallet\Contracts\WalletRepositoryInterface;
use Utd\UsersWallet\Contracts\WalletServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\Common;
use App\Http\Resources\MomentGiftResource;
use Modules\Moment\Entities\MomentUserGift;
use App\Http\Resources\AudioGiftsListResource;
use App\Contracts\GiftLogRepositoryContract;

class WalletService implements WalletServiceInterface
{
    protected $walletRepo;

    public function __construct(
        WalletRepositoryInterface $walletRepo,
        private readonly ?GiftLogRepositoryContract $GiftLogRepository = null,
        private readonly ?UserLogRepository $userCoinLogRepository = null
    ) {
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
                'before_amount' => $fromWallet->balance - $fromWallet->cut_amount - $fromWallet->pending_amount,
                'after_amount' => wallet_available_by_user($fromUserId),
                'related_id' => $toUserId

            ]);

            $this->walletRepo->updateWallet($toWallet->id, ['balance' => $toWallet->balance + $amount]);
            $this->walletRepo->createLog([
                'wallet_id' => $toWallet->id,
                'user_id' => $toUserId,
                'amount' => $amount,
                'operation' => 'transfer',
                'type' => 'transfer',
                'before_amount' => $toWallet->balance - $toWallet->cut_amount - $toWallet->pending_amount,
                'after_amount' => wallet_available_by_user($toUserId),
                'related_id' => $fromUserId
            ]);

            DB::commit();

            return ['status' => 'success',];

        } catch (Exception $e) {
            DB::rollBack();
            \Log::info(12333);
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



    public function storeTransaction(
        int $userId,
        string $operation,
        float $amount,
        string $type,
        string $description,
        array $descriptionData = [],
        string $uniqueId = null
    ): bool {
        try {
            return DB::transaction(function () use ($userId, $operation, $amount, $type, $description, $descriptionData, $uniqueId) {
                $wallet = $this->walletRepo->getWalletByUserId($userId);
                $beforeAmount = ($wallet->balance ?? 0) - ($wallet->cut_amount ?? 0) - ($wallet->pending_amount ?? 0);

                $logAmount = 0;
                if ($operation === 'add') {
                    $this->walletRepo->updateWallet($wallet->id, ['balance' => $wallet->balance + $amount]);
                    $logAmount = $amount;
                } elseif ($operation === 'subtract' || $operation === 'cut') {
                    $this->walletRepo->updateWallet($wallet->id, ['cut_amount' => $wallet->cut_amount + $amount]);
                    $logAmount = -$amount;
                    $operation = 'subtract'; // Standardize
                } elseif ($operation === 'pending') {
                    $this->walletRepo->updateWallet($wallet->id, ['pending_amount' => $wallet->pending_amount + $amount]);
                    return true; // No log for pending
                }

                $relatedId = $descriptionData['related_id'] ?? $descriptionData['receiver_id'] ?? $descriptionData['target_id'] ?? null;

                $this->walletRepo->createLog([
                    'wallet_id' => $wallet->id,
                    'user_id' => $userId,
                    'amount' => $logAmount,
                    'operation' => $operation,
                    'type' => $type,
                    'before_amount' => $beforeAmount,
                    'after_amount' => $beforeAmount + $logAmount,
                    'related_id' => $relatedId,
                    'description' => $description,
                    'description_data' => json_encode($descriptionData),
                    'unique_id' => $uniqueId
                ]);

                return true;
            });
        } catch (\Exception $e) {
            \Log::error("WalletService Error: " . $e->getMessage());
            return false;
        }
    }

    public function recalculateWalletBalance(int $userId): float
    {
        return DB::transaction(function () use ($userId) {
            $wallet = $this->walletRepo->getWalletByUserId($userId);

            $logs = WalletLog::where('user_id', $userId)->get();
            $balance = 0;
            $cutAmount = 0;

            foreach ($logs as $log) {
                if ($log->operation === 'add') {
                    $balance += abs($log->amount);
                } elseif ($log->operation === 'subtract') {
                    $cutAmount += abs($log->amount);
                }
            }

            $this->walletRepo->updateWallet($wallet->id, [
                'balance' => $balance,
                'cut_amount' => $cutAmount
            ]);

            return $balance - $cutAmount - ($wallet->pending_amount ?? 0);
        });
    }

    public function validateWallet(int $userId)
    {
        $wallet = $this->walletRepo->getWalletByUserId($userId);
        $issues = [];

        if ($wallet->balance < 0)
            $issues[] = "Negative balance";
        if ($wallet->cut_amount < 0)
            $issues[] = "Negative cut_amount";
        if ($wallet->pending_amount < 0)
            $issues[] = "Negative pending_amount";
        if ($wallet->cut_amount > $wallet->balance)
            $issues[] = "Cut exceeds balance";

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'wallet' => $wallet
        ];
    }
}
