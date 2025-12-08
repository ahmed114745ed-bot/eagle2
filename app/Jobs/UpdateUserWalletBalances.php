<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\UsersWallet\Helpers\WalletHelper;

class UpdateUserWalletBalances implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected array $newData;
    protected array $oldData;
    protected ?int $agencyId;
    protected string $type;
    protected   $target_id;


    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, array $newData, array $oldData, ?int $agencyId = null, string $type = 'system' , $target_id = null)
    {
        $this->userId   = $userId;
        $this->newData  = $newData;
        $this->oldData  = $oldData;
        $this->agencyId = $agencyId;
        $this->type     = $type;
        $this->target_id = $target_id;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      \Log::info('UpdateUserWalletBalances data', [
    'target_id' => $this->target_id,
  
]);
        WalletHelper::addAllBalancesByDiffs(
            $this->userId,
            $this->newData,
            $this->oldData,
            $this->agencyId,
            $this->type,
            $this->target_id
        );
    }
}
