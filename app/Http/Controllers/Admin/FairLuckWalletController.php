<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FairLuckWallet;
use App\Services\FairLuck\WalletManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FairLuckWalletController extends Controller
{
    /**
     * عرض أرصدة جميع المحافظ
     */
    public function index(): JsonResponse
    {
        $balances = WalletManager::getAllWalletBalances();
        $total = array_sum($balances);
        
        return response()->json([
            'success' => true,
            'data' => [
                'wallets' => $balances,
                'total_balance' => $total,
                'percentages' => $total > 0 ? [
                    'global_vault' => round(($balances['global_vault'] / $total) * 100, 2),
                    'jackpot_wallet' => round(($balances['jackpot_wallet'] / $total) * 100, 2),
                    'medium_wallet' => round(($balances['medium_wallet'] / $total) * 100, 2),
                ] : [],
                'last_updated' => now()->toDateTimeString(),
            ]
        ]);
    }

    /**
     * تحديث رصيد محفظة معينة (للإدارة)
     */
    public function updateBalance(Request $request): JsonResponse
    {
        $request->validate([
            'wallet_type' => 'required|string|in:global_vault,jackpot_wallet,medium_wallet',
            'amount' => 'required|integer|min:0',
            'operation' => 'required|string|in:set,increase,decrease'
        ]);

        $walletType = $request->wallet_type;
        $amount = $request->amount;
        $operation = $request->operation;
        
        $success = match($operation) {
            'set' => FairLuckWallet::setBalance($walletType, $amount),
            'increase' => FairLuckWallet::increaseBalance($walletType, $amount),
            'decrease' => FairLuckWallet::decreaseBalance($walletType, $amount),
            default => false
        };

        if ($success) {
            $newBalance = FairLuckWallet::getBalance($walletType);
            return response()->json([
                'success' => true,
                'message' => "تم {$operation} رصيد المحفظة بنجاح",
                'data' => [
                    'wallet_type' => $walletType,
                    'new_balance' => $newBalance,
                    'operation' => $operation,
                    'amount' => $amount
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في تحديث رصيد المحفظة'
        ], 400);
    }

    /**
     * تحويل مبلغ بين المحافظ
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'from_wallet' => 'required|string|in:global_vault,jackpot_wallet,medium_wallet',
            'to_wallet' => 'required|string|in:global_vault,jackpot_wallet,medium_wallet|different:from_wallet',
            'amount' => 'required|integer|min:1'
        ]);

        $success = WalletManager::transferBetweenWallets(
            $request->from_wallet,
            $request->to_wallet,
            $request->amount
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'تم التحويل بنجاح',
                'data' => [
                    'from_wallet' => $request->from_wallet,
                    'to_wallet' => $request->to_wallet,
                    'amount' => $request->amount,
                    'new_balances' => WalletManager::getAllWalletBalances()
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'فشل في عملية التحويل - تحقق من الرصيد المتاح'
        ], 400);
    }

    /**
     * إعادة توزيع أرصدة المحافظ
     */
    public function rebalance(): JsonResponse
    {
        $balancesBefore = WalletManager::getAllWalletBalances();
        WalletManager::rebalanceWallets();
        $balancesAfter = WalletManager::getAllWalletBalances();

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة توزيع المحافظ بنجاح',
            'data' => [
                'balances_before' => $balancesBefore,
                'balances_after' => $balancesAfter
            ]
        ]);
    }

    /**
     * فحص سلامة المحافظ
     */
    public function validateIntegrity(): JsonResponse
    {
        $isValid = WalletManager::validateWalletIntegrity();
        $balances = WalletManager::getAllWalletBalances();

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'جميع المحافظ سليمة' : 'تم اكتشاف مشاكل في المحافظ',
            'data' => [
                'is_valid' => $isValid,
                'balances' => $balances
            ]
        ]);
    }

    /**
     * إحصائيات المحافظ
     */
    public function stats(): JsonResponse
    {
        $balances = WalletManager::getAllWalletBalances();
        $total = array_sum($balances);
        
        // إحصائيات إضافية
        $walletRecords = FairLuckWallet::all();
        $lastUpdated = $walletRecords->max('last_updated');

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_balance' => $total,
                    'active_wallets' => count(array_filter($balances, fn($balance) => $balance > 0)),
                    'last_updated' => $lastUpdated
                ],
                'wallets' => $balances,
                'distribution' => $total > 0 ? [
                    'global_vault' => round(($balances['global_vault'] / $total) * 100, 2),
                    'jackpot_wallet' => round(($balances['jackpot_wallet'] / $total) * 100, 2),
                    'medium_wallet' => round(($balances['medium_wallet'] / $total) * 100, 2),
                ] : []
            ]
        ]);
    }
}