<?php

namespace App\Http\Controllers;

use App\Models\CoinEvaluation;
use Illuminate\Http\Request;

class ChargesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $coinEvaluation = CoinEvaluation::where('user_id', $user->id)->first();

        if (!$coinEvaluation) {
            $coinEvaluation = new CoinEvaluation();
            $coinEvaluation->user_id = $user->id;
            $coinEvaluation->rate = 1; // Default rate
            $coinEvaluation->save();
        }

        return view('charges.index', [
            'coinEvaluation' => $coinEvaluation,
        ]);
    }

    public function updateCoinEvaluation(Request $request)
    {
        $user = $request->user();
        $coinEvaluation = CoinEvaluation::where('user_id', $user->id)->first();

        $coinEvaluation->rate = $request->input('rate');
        $coinEvaluation->save();

        return redirect()->route('charges.index')->with('success', 'Coin evaluation updated successfully.');
    }
}