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
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class OfficialMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $request;
    protected $admin;

    /**
     * Create a new job instance.
     */
    public function __construct($model, array $request, $admin)
    {
        $this->model = $model;
        $this->request = $request;
        $this->admin = $admin;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Log::info('OfficialMessageJob raw request', $this->request);
        $feature = $this->request['feature'] ?? null;
        $subFeature = $this->request['sub_feature'] ?? null;
        $memberTitle = $this->request['member_title'] ?? 'owner';

        // Handle feature_ids as array or string
        $featureIds = $this->model->feature_ids ?? [];
        $usersId = [];
        $AllUsersId = [];
        $HostUsersId = [];
        $HostAgencyUsersId = [];
        $shippingAgencyUserId = [];
        $FamilyUsersId = [];
        $BdUsersId = [];

        // Log::info($featureIds);

        // ✅ Always make $featureIds an array safely
        if (!is_array($featureIds)) {
            // if it's a comma-separated string or single value
            $featureIds = array_filter(explode(',', $featureIds));
        }

        $AllUsersId = [];
        $HostUsersId = [];
        $HostAgencyUsersId = [];
        $shippingAgencyUserId = [];
        $FamilyUsersId = [];
        $BdUsersId = [];
        $usersId = [];

        $AllUsersId = [];
        $HostUsersId = [];
        $HostAgencyUsersId = [];
        $shippingAgencyUserId = [];
        $FamilyUsersId = [];
        $BdUsersId = [];

        if (!empty($this->model->multi_feature) && is_array($this->model->multi_feature)) {
            foreach ($this->model->multi_feature as $feature) {
                if ($feature === 'all') {
                    $AllUsersId = User::pluck('id')->toArray();
                } elseif ($feature === 'users') {
                    $HostUsersId = array_merge($HostUsersId, User::whereNull('agency_id')
                        ->orWhere('agency_id', 0)
                        ->pluck('id')->toArray());
                } elseif ($feature === 'host_users') {
                    $HostUsersId = array_merge($HostUsersId, User::whereNotNull('agency_id')
                        ->where('agency_id', '!=', 0)
                        ->where(function ($query) {
                            $query->whereDoesntHave('ownAgency')
                                ->orWhereDoesntHave('shippingAgency');
                        })
                        ->pluck('id')->toArray());
                } elseif ($feature === 'host_agencies') {
                    $HostAgencyUsersId = array_merge($HostAgencyUsersId, User::whereNotNull('agency_id')
                        ->where('agency_id', '!=', 0)
                        ->whereHas('ownAgency')
                        ->pluck('id')->toArray());
                } elseif ($feature === 'charge_agencies') {
                    $shippingAgencyUserId = array_merge($shippingAgencyUserId, User::whereNotNull('agency_id')
                        ->where('agency_id', '!=', 0)
                        ->whereHas('shippingAgency')
                        ->pluck('id')->toArray());
                } elseif ($feature === 'families') {
                    $FamilyUsersId = array_merge($FamilyUsersId, User::whereHas('user_family')
                        ->pluck('id')->toArray());
                } elseif ($feature === 'bds') {
                    $BdUsersId = array_merge($BdUsersId, User::where('is_bd', 1)
                        ->pluck('id')->toArray());
                }
            }

            // Combine all IDs into one array and remove duplicates
            $usersId = array_unique(array_merge(
                $AllUsersId,
                $HostUsersId,
                $HostAgencyUsersId,
                $shippingAgencyUserId,
                $FamilyUsersId,
                $BdUsersId
            ));

           CustomNotification::officialMsg($this->model, $usersId);
        }


        // Log::info('OfficialMessageJob started', [
        //     'feature'      => $feature,
        //     'sub_feature'  => $subFeature,
        //     'member_title' => $memberTitle,
        //     'feature_ids'  => $featureIds,
        // ]);


        if ($feature && $feature === 'agency') {
            $agencies = Agency::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'your_country', fn($q) => $q->where('country_id', $this->admin->country_id))
                ->when($subFeature === 'ids', fn($q) => $q->whereIn('id', $featureIds))
                ->get();

            foreach ($agencies as $agency) {
                if ($memberTitle === 'owner') {
                    $usersId[] = $agency->app_owner_id;
                    // Log::info($usersId);
                } elseif ($memberTitle === 'admin') {
                    $usersId = array_merge($usersId, $agency->admins->pluck('user_id')->toArray());
                } elseif ($memberTitle === 'members') {
                    $usersId = array_merge($usersId, $agency->members->pluck('user_id')->toArray());
                }
            }
        } elseif ($feature && $feature === 'family') {
            $families = Family::query()
                ->when($subFeature === 'country', function ($q) use ($featureIds) {
                    $q->whereHas('owner', fn($query) => $query->whereIn('country_id', $featureIds));
                })
                ->when($subFeature === 'your_country', fn($q) => $q->where('country_id', $this->admin->country_id))
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
        } elseif ($feature && $feature === 'users') {
            $users = User::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'your_country', fn($q) => $q->where('country_id', $this->admin->country_id))
                ->when($subFeature === 'logout', fn($q) => $q->where('is_logout', 1))
                ->get();

            $usersId = $users->pluck('id')->toArray();
        } elseif ($feature && $feature === 'bds') {
            $users = Bd::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'your_country', fn($q) => $q->where('country_id', $this->admin->country_id))
                ->get();

            $usersId = $users->pluck('app_id')->toArray();
        } elseif ($feature && $feature === 'shipping_agency') {
            $agencies = ShippingAgency::query()
                ->when($subFeature === 'country', fn($q) => $q->whereIn('country_id', $featureIds))
                ->when($subFeature === 'ids', fn($q) => $q->whereIn('id', $featureIds))
                ->when($subFeature === 'your_country', fn($q) => $q->where('country_id', $this->admin->country_id))
                ->get();

            $usersId = $agencies->pluck('app_owner_id')->toArray();
        }
        dd($usersId);
        // Call your custom notification logic
        CustomNotification::officialMsg($this->model, $usersId);
    }
}
