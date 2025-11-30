<?php

namespace Modules\UsersWallet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\UsersWallet\Helpers\WalletHelper;
class WalletController extends Controller
{
    
    public function add(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'amount' => 'required|numeric|min:0.1',
            'type' => 'nullable|string'
        ]);

        $wallet = WalletHelper::addBalance(
            $request->user_id,
            $request->amount,
            $request->type ?? 'manual_add'
        );

        return response()->json(['wallet' => $wallet]);
    }

    public function subtract(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'amount' => 'required|numeric|min:0.1',
            'type' => 'nullable|string'
        ]);

        $wallet = WalletHelper::subtractBalance(
            $request->user_id,
            $request->amount,
            $request->type ?? 'manual_subtract'
        );

        return response()->json(['wallet' => $wallet]);
    }
}
