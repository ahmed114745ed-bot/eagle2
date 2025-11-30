<?php

namespace Modules\UsersWallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\UsersWallet\Entities\UserWithdrawal;
use Modules\UsersWallet\Helpers\WalletHelper;
class UsersWalletController extends Controller
{

    
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'meta'   => 'nullable|array', 
        ]);
    
        $userId = auth()->id();
    
        try {
            $withdrawal = WalletHelper::createWithdrawal($userId, $request->amount, $request->meta ?? []);
    
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء طلب السحب بنجاح، حالته: معلق',
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
}
