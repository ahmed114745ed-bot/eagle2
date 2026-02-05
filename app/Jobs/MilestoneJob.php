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

        $milestone = Milestone::with('rewards')->findOrFail($this->milestoneId);
        $usersQuery = match ($milestone->slug) {
            'super-admin' => User::where('is_super_admin', 1),
            'bd' => User::where('is_bd', 1),
            'host-agency-owner' => User::whereHas('hasHostAgency'),
            'charge-agency-owner' => User::whereHas('hasShippingAgencyV2'),
            'family-owner' => User::whereHas('hasFamily'),
            'host' => User::where('type_user', 1),
            default => User::query(),
        };

        DB::transaction(function () use ($usersQuery, $milestone) {
            $usersQuery->chunk(100, function ($users) use ($milestone) {
                foreach ($users as $user) {
                    MilestoneHelper::removeReward($user, $milestone->slug);
                    MilestoneHelper::grantMilestoneToUser($user, $milestone->slug);
                }
            });
        });
    }
}
