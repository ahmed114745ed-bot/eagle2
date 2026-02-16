<?php

namespace App\Jobs;


use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Milestones\Entities\Milestone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Modules\Milestones\Helpers\MilestoneHelper;

class MilestoneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $milestoneId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $milestoneId)
    {
        $this->milestoneId = $milestoneId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info("Starting MilestoneJob for milestone ID: {$this->milestoneId}");
        Log::info("1111111111111111111111");

        $milestone = Milestone::with('rewards')->find($this->milestoneId);
        $milestone = Milestone::with('rewards')->findOrFail($this->milestoneId);
        $usersQuery = match ($milestone->slug) {
            'super-admin' => User::where('is_super_admin', 1),
            'bd' => User::where('is_bd', 1),
            'host-agency-owner' => User::whereHas('hasHostAgency'),
            'charge-agency-owner' => User::whereHas('hasShippingAgencyV2'),
            'family-owner' => User::whereHas('hasFamily'),
            'host' => User::where('type_user', 1),
            'area-manager' => User::where('is_area_manager', 1),
            default => User::query(),
        };

        $allUserIds = $usersQuery->pluck('id')->toArray();
        Log::info("Processing milestone '{$milestone->slug}' for users: " . implode(', ', $allUserIds));

        DB::transaction(function () use ($usersQuery, $milestone) {
            $usersQuery->chunk(100, function ($users) use ($milestone) {
                foreach ($users as $user) {
                    MilestoneHelper::removeReward($user, $milestone->slug);
                    \Log::info("Removed reward for user {$user->id} for milestone {$milestone->slug}");
                    MilestoneHelper::grantMilestoneToUser($user, $milestone->slug);
                    \Log::info("Granted milestone '{$milestone->slug}' to user {$user->id}");
                }
            });
        });
    }
}
