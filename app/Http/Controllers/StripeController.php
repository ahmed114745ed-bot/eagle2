<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function __construct(public StripeService $stripeService)
    {

    }
    public function pay(Request $request){

        $request->validate([
            'input' => 'required|array'
        ]);

        $apiKey = 'sk_test_51QDK2SATuMaocXXJZgv1YsOhf1AGmXyzoeUQ9b65xmMj84EVTvPHiljxwQdMoXTJoop4Y2F8wzSSdjP9EI1Z9ehF00V3zCFXSh';

        $link = $this->stripeService->pay($apiKey, $request->input);


        return response()->json([
            'message' => 'Link generated successfully',
            'link' => $link
        ]);
    }
}
