<?php

namespace App\Jobs;

use App\Http\Controllers\Api\V1\BoxController;
use App\Tik\Services\BoxService;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Follow;
use App\Helpers\Common;
use App\Models\PickBoxList;
use App\Models\RoomVisitor;
use App\Models\UserBoxGift;
use App\Facades\RedisService;
use Illuminate\Bus\Queueable;
use App\Facades\CustomNotification;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\LuckyBoxServices;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class TestSuperLuckyBoxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $requestData) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::get()->take(20);

        foreach ($users as $user) {
            // Your sending logic here
            $this->send($this->requestData, $user);
        }
    }

    private function send(array $requestData, $user)
    {
        $boxService = new BoxService();
        $request = new \Illuminate\Http\Request($requestData);
        (new BoxController($boxService))->sendTest($request, $user);
    }
}
