<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;

class TestsController extends Controller
{
public function discrepancyView(Request $request)
{
    // الشهر والسنة المطلوبين أو الحالي
    $month = $request->query('month', Carbon::now()->month);
    $year = $request->query('year', Carbon::now()->year);

    // جلب كل المستخدمين الذين لديهم أي سجل في الشهر المحدد
    $usersMonthly = DB::table('monthly_diamond_receives')
        ->where('month', $month)
        ->where('year', $year)
        ->get();

    $results = [];

    foreach ($usersMonthly as $monthly) {
        $userId = $monthly->user_id;

        // جمع عدد الماسات فعلياً من gift_logs: عدد الهدايا * السعر
        $totalGifts = DB::table('gift_logs')
            ->where('receiver_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->selectRaw('SUM(giftNum * giftPrice) as total')
            ->value('total') ?? 0; // fallback للصفر إذا لا يوجد هدايا

        // المقارنة بعد تحويل القيم إلى float
        $isDifferent = floatval($totalGifts) != floatval($monthly->monthly_diamond_received);
        if ($isDifferent) {
            $results[] = [
                'user_id' => $userId,
                'registered' => floatval($monthly->monthly_diamond_received),
                'actual' => floatval($totalGifts),
            ];
        }
    }

    return view('tests.discrepancy', [
        'results' => $results,
        'month' => $month,
        'year' => $year,
    ]);
}


 public function form()
    {
        return view('tests.load-test');
    }

    public function run(Request $request)
    {
        $request->validate([
            'count' => 'required|integer|min:1',
            'concurrency' => 'required|integer|min:1',
            'token' => 'required|string',
            'id' => 'required|integer',
            'owner_id' => 'required|integer',
            'toUid' => 'required|integer',
            'num' => 'required|integer',
            'url'=> 'required',
        ]);

        $count = $request->count;
        $con = $request->concurrency;

        $client = new Client([
            'base_uri' => $request->url,
            'timeout'  => 10,
            'verify'   => false,
        ]);

        $promises = [];

        for ($i = 1; $i <= $count; $i++) {

            $promises[$i] = $client->postAsync('/api/gifts/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $request->token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'id' => $request->id,
                    'owner_id' => $request->owner_id,
                    'toUid' => $request->toUid,
                    'num' => $request->num
                ]
            ]);

        }

        $results = Utils::settle($promises)->wait();

        $summary = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($results as $index => $res) {
            if ($res['state'] === 'fulfilled') {
                $summary['success']++;
                $summary['errors'][] = [
                    'request' => $index,
                    'status' => 'SUCCESS',
                    'response' => json_decode($res['value']->getBody(), true)
                ];
            } else {
                $summary['failed']++;
                $summary['errors'][] = [
                    'request' => $index,
                    'status' => 'FAILED',
                    'error' => $res['reason']->getMessage()
                ];
            }
        }

        return view('tests.load-test', [
            'results' => $summary,
        ]);
    }
}
