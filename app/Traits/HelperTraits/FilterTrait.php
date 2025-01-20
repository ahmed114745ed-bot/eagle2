<?php


namespace App\Traits\HelperTraits;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\Family;
use App\Models\PeriodTarget;

trait FilterTrait
{

    public static function by_agency_filter()
    {
        $ops = [0 => 'no agency'];
        $agencies = Agency::query()->where('status', 1)->get();
        foreach ($agencies as $agency) {
            $ops[$agency->id] = $agency->name;
        }
        return $ops;
    }

    public static function by_period_filter()
    {
        $ops = []; // Initialize the array
        $periods = PeriodTarget::get();

        foreach ($periods as $period) {
            $ops[$period->id] = 'start:' . Carbon::parse($period->start_at)->format('Y-m-d') . ' / ' . 'end:' . Carbon::parse($period->end_at)->format('Y-m-d');
        }

        return $ops;
    }

    public static function by_user_filter()
    {
        $ops = [0 => 'no agency'];
        $app_owner_id = Agency::query()->where('status', 1)->pluck('app_owner_id');
        $users = User::whereIn('id', $app_owner_id)->get();
        foreach ($users as $user) {
            $ops[$user->id] = $user->name;
        }
        return $ops;
    }

    public static function by_agency_filter_with_owner_id()
    {
        $ops = [0 => 'no agency'];
        $agencies = Agency::query()->with('owner')->where('status', 1)->get();
        foreach ($agencies as $agency) {
            $ops[$agency->id] = $agency->name . ' - ' . $agency->id . ' - ' . $agency->owner?->uuid;
        }
        return $ops;
    }
    public static function by_family_filter()
    {
        $ops = [0 => 'no family'];
        $families = Family::query()->where('status', 1)->get();
        foreach ($families as $family) {
            $ops[$family->id] = $family->name;
        }
        return $ops;
    }
}
