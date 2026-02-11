<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserLuckProfile;
use App\Services\FairLuck\FairLuckServiceDualManaged;
use App\Services\FairLuck\FairLuckServiceSmartManaged;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SimulateManagedGifts extends Command
{
    protected $signature = 'simulate:lucky-managed {gift_id?} {--trials=100} {--balance=30000}';
    protected $description = 'Simulate Managed Services (Dual and Smart) that read from Gift Model Probabilities';

    public function handle(FairLuckServiceDualManaged $dualManaged, FairLuckServiceSmartManaged $smartManaged)
    {
        $initialBalance = (int) $this->option('balance');
        $maxTrials = (int) $this->option('trials');
        
        $giftQuery = Gift::where('type', 6)->where('enable', 1);
        $giftId = $this->argument('gift_id') ?? $giftQuery->first()?->id;

        if (!$giftId) {
            $this->error('No lucky gift found.');
            return 1;
        }

        $gift = Gift::with('luckyGift')->find($giftId);
        $this->info("🎁 Gift: {$gift->name} | Admin Probability: " . ($gift->luckyGift?->win_probability ?? 'Not Set (Default 10%)'));

        // Create user
        $user = User::factory()->create(['di' => $initialBalance, 'name' => 'ManagedTester']);
        $userId = $user->id;

        // Run Dual Managed
        $this->warn("\n>>> Running SERVICE: Dual Managed...");
        UserLuckProfile::where('user_id', $userId)->delete();
        $user->di = $initialBalance; $user->save();
        $this->runSimulation($user, $gift, $dualManaged, 'dual_managed_report.html', 'النظام المزدوج (المرتبط بالنسب)');

        // Run Smart Managed
        $this->warn("\n>>> Running SERVICE: Smart Managed...");
        UserLuckProfile::where('user_id', $userId)->delete();
        $user->di = $initialBalance; $user->save();
        $this->runSimulation($user, $gift, $smartManaged, 'smart_managed_report.html', 'النظام الهجين الذكي (المرتبط بالنسب)');

        $this->info("\n✅ Finished! Reports generated in public folder.");
        return 0;
    }

    private function runSimulation($user, $gift, $service, $filename, $title)
    {
        $initial = $user->di;
        $currentBalance = $initial;
        $history = [];
        $count = 0; $spent = 0; $won = 0;

        for ($i = 0; $i < $this->option('trials'); $i++) {
            if ($currentBalance < $gift->price) break;

            $balanceBefore = $currentBalance;
            $currentBalance -= $gift->price;
            $spent += $gift->price;
            $count++;

            $result = $service->processBet($user, $gift, $gift->price);
            
            $winAmount = $result->isWinner ? ($result->multiplier * $gift->price) : 0;
            $currentBalance += $winAmount;
            $won += $winAmount;

            $history[] = [
                'step' => $count,
                'balance_before' => $balanceBefore,
                'outcome' => $result->isWinner ? 'WIN' : 'LOSS',
                'multiplier' => $result->isWinner ? $result->multiplier . 'x' : '-',
                'win_amount' => $winAmount,
                'balance_after' => $currentBalance,
                'deviation' => number_format($result->newDeviation, 4),
            ];
            $user->di = $currentBalance;
            $user->save();
        }

        $this->saveHtml($user, $gift, $initial, $count, $spent, $won, $currentBalance, $history, $filename, $title);
    }

    private function saveHtml($user, $gift, $initial, $count, $spent, $won, $final, $history, $filename, $title)
    {
        $rtp = $spent > 0 ? ($won / $spent) * 100 : 0;
        $net = $final - $initial;
        $netClass = $net >= 0 ? 'text-success' : 'text-danger';

        $html = "
        <!DOCTYPE html>
        <html lang='ar' dir='rtl'>
        <head>
            <meta charset='UTF-8'>
            <title>{$title}</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                .win-row { background-color: #e8f5e9 !important; }
            </style>
        </head>
        <body>
            <div class='container py-5'>
                <h1 class='text-center mb-4'>📊 {$title}</h1>
                <div class='row text-center mb-4 card p-4 shadow-sm bg-white'>
                   <div class='col-12 row'>
                        <div class='col'><h5>الرصيد الابتدائي</h5><p class='h4 text-secondary'>" . number_format($initial) . "</p></div>
                        <div class='col'><h5>إجمالي المدفوع</h5><p class='h4 text-danger'>" . number_format($spent) . "</p></div>
                        <div class='col'><h5>إجمالي المكسب</h5><p class='h4 text-success'>" . number_format($won) . "</p></div>
                        <div class='col'><h5>الرصيد النهائي</h5><p class='h4'>" . number_format($final) . "</p></div>
                        <div class='col'><h5>صافي النتيجة</h5><p class='h4 {$netClass}'>" . number_format($net) . "</p></div>
                        <div class='col'><h5>المراهنات</h5><p class='h4'>" . number_format($count) . "</p></div>
                        <div class='col'><h5>RTP</h5><p class='h4'>" . number_format($rtp, 2) . "%</p></div>
                   </div>
                </div>
                <div class='table-responsive'>
                    <table class='table table-bordered bg-white text-center'>
                        <thead class='table-dark'>
                            <tr><th>#</th><th>النتيجة</th><th>المضاعف</th><th>المكسب</th><th>الرصيد بعد</th><th>الانحراف</th></tr>
                        </thead>
                        <tbody>";

        foreach ($history as $h) {
            $rowClass = $h['outcome'] === 'WIN' ? 'win-row' : '';
            $html .= "<tr class='{$rowClass}'>
                        <td>{$h['step']}</td>
                        <td>" . ($h['outcome'] == 'WIN' ? '✅ فوز' : '❌ خسارة') . "</td>
                        <td>{$h['multiplier']}</td>
                        <td>" . number_format($h['win_amount']) . "</td>
                        <td>" . number_format($h['balance_after']) . "</td>
                        <td>{$h['deviation']}</td>
                      </tr>";
        }

        $html .= "</tbody></table></div></div></body></html>";
        File::put(public_path($filename), $html);
        $this->info("📍 Report generated: public/$filename");
    }
}
