<?php

namespace App\Console\Commands;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserLuckProfile;
use App\Services\FairLuck\FairLuckService;
use App\Services\FairLuck\FairLuckService2;
use App\Services\FairLuck\FairLuckService3;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompareFairLuckSystems extends Command
{
    protected $signature = 'simulate:lucky-compare {gift_id?} {--trials=1000} {--balance=30000}';
    protected $description = 'Run all 3 systems and generate individual HTML reports for comparison';

    public function handle()
    {
        $trials = $this->option('trials');
        $balance = $this->option('balance');
        $giftId = $this->argument('gift_id');

        $this->info("🚀 Starting Comprehensive Comparison (Trials: $trials, Start Balance: $balance)");

        // 1. Create a Shared User for all simulations
        $user = User::factory()->create([
            'name' => 'CompPlayer_' . now()->timestamp,
            'di' => $balance
        ]);
        $userId = $user->id;
        $this->info("Generated Shared User ID: $userId");

        // System 1
        $this->info("\n--- Running System 1 (Refined Original) ---");
        UserLuckProfile::where('user_id', $userId)->delete();
        $user->di = $balance;
        $user->save();
      /*  $this->call('simulate:lucky-gifts-html', [
            'gift_id' => $giftId,
            '--trials' => $trials,
            '--balance' => $balance,
            '--userId' => $userId
        ]);

        // System 2
        $this->info("\n--- Running System 2 (Dual Macro/Micro) ---");
        UserLuckProfile::where('user_id', $userId)->delete();
        $user->di = $balance;
        $user->save();
        $this->call('simulate:lucky-gifts-dual', [
            'gift_id' => $giftId,
            '--trials' => $trials,
            '--balance' => $balance,
            '--userId' => $userId
        ]);
*/
        // System 3
        $this->info("\n--- Running System 3 (Intelligent Hybrid) ---");
        UserLuckProfile::where('user_id', $userId)->delete();
        $user->di = $balance;
        $user->save();
        $this->call('simulate:lucky-gifts-smart', [
            'gift_id' => $giftId,
            '--trials' => $trials,
            '--balance' => $balance,
            '--userId' => $userId
        ]);

        $this->info("\n✅ Comparison Finished!");
        $this->info("Reports generated:");
        $this->info("1. Original Algorithm: public/lucky_report.html");
        $this->info("2. Dual Algorithm: public/dual_lucky_report.html");
        $this->info("3. Smart Algorithm: public/smart_lucky_report.html");

        return 0;
    }
}
