<?php

namespace App\Jobs;

use App\Models\Bd;
use App\Models\User;
use App\Models\Agency;
use App\Models\Family;
use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use App\Models\ShippingAgency;
use App\Facades\CustomNotification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class OfficialMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $request;

    /**
     * Create a new job instance.
     */
    public function __construct($model, array $request)
    {
        $this->model = $model;
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $feature = $this->request['feature'] ?? null;
        $subFeature = $this->request['sub_feature'] ?? null;
        $memberTitle = $this->request['member_title'] ?? null;

        // Handle feature_ids as array or string
        $featureIds = $this->request['feature_ids'] ?? [];

        // ✅ Always make $featureIds an array safely
        if (!is_array($featureIds)) {
            // if it's a comma-separated string or single value
            $featureIds = array_filter(explode(',', $featureIds));
        }

        $usersId = [];

        if ($feature === 'agency') {
            $agencies = Agency::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'ids', fn($q) => $q->whereIn('id', $featureIds))
                ->get();

            foreach ($agencies as $agency) {
                if ($memberTitle === 'owner') {
                    $usersId[] = $agency->app_owner_id;
                } elseif ($memberTitle === 'admin') {
                    $usersId = array_merge($usersId, $agency->admins->pluck('user_id')->toArray());
                } elseif ($memberTitle === 'members') {
                    $usersId = array_merge($usersId, $agency->members->pluck('user_id')->toArray());
                }
            }
        } elseif ($feature === 'family') {
            $families = Family::query()
                ->when($subFeature === 'country', function ($q) use ($featureIds) {
                    $q->whereHas('owner', fn($query) => $query->whereIn('country_id', $featureIds));
                })
                ->when($subFeature === 'ids', fn($q) => $q->whereIn('id', $featureIds))
                ->get();

            foreach ($families as $family) {
                if ($memberTitle === 'owner') {
                    $usersId[] = $family->user_id;
                } elseif ($memberTitle === 'admin') {
                    $usersId = array_merge($usersId, $family->admins->pluck('user_id')->toArray());
                } elseif ($memberTitle === 'members') {
                    $usersId = array_merge($usersId, $family->members->pluck('user_id')->toArray());
                }
            }
        } elseif ($feature === 'users') {
            $users = User::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'logout', fn($q) => $q->where('is_logout', 1))
                ->get();

            $usersId = $users->pluck('id')->toArray();
        } elseif ($feature === 'bds') {
            $users = Bd::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->get();

            $usersId = $users->pluck('app_id')->toArray();
        } elseif ($feature === 'shipping_agency') {
            $agencies = ShippingAgency::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->get();

            $usersId = $agencies->pluck('app_owner_id')->toArray();
        }

        // Call your custom notification logic
        CustomNotification::officialMsg($this->model, $usersId);
    }
}
