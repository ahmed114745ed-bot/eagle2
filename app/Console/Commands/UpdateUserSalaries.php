<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TargetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\FixedTarget\Services\FixedTargetService;

class UpdateUserSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-salaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'add user achievements after 30 day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        User::query()
            ->where('agency_id', '!=', 0)
            ->chunk(500, function ($users){
                foreach ($users as $user) {
                    $data = Cache::get('cach-data-mystore-'.$user->id);
                    $cacheKey = 'cache-data-mystore-' . $user->id;
                    if (Cache::add($cacheKey, true, now()->addSeconds(30))) {
                        $targetService = new FixedTargetService($user);
                        $targetService->calculateTarget();
                    }
                }
            });
    }
}
