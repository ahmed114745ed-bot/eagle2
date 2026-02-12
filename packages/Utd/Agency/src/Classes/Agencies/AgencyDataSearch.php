<?php

namespace Utd\Agency\Classes\Agencies;

use Illuminate\Http\Request;
use Utd\Agency\Traits\ResolvesExternalDependencies;

class AgencyDataSearch
{
    use ResolvesExternalDependencies;

    /**
     * Fetch agency data based on request filters
     *
     * @return array
     */
    public function fetchData(Request $request)
    {
        $agencyClass = $this->getAgencyModel();
        $userClass = $this->getUserModel();

        $query = $agencyClass::query();

        // Apply filters
        if ($request->has('agency_id') && $request->agency_id) {
            $query->where('id', $request->agency_id);
        }

        if ($request->has('owner_id') && $request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Load relationships
        $query->with([
            'owner',
            'country',
            'users' => function ($q) use ($request) {
                if ($request->has('from_date') && $request->has('to_date')) {
                    $q->whereBetween('created_at', [$request->from_date, $request->to_date]);
                }
            },
        ]);

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->get('per_page', 15);

        if ($request->has('paginate') && $request->paginate === false) {
            $agencies = $query->get();
        } else {
            $agencies = $query->paginate($perPage);
        }

        return [
            'agencies' => $agencies,
            'filters' => $request->only(['agency_id', 'owner_id', 'country_id', 'from_date', 'to_date', 'status', 'search']),
        ];
    }

    /**
     * Get agency statistics
     *
     * @return array
     */
    public function getStatistics(Request $request)
    {
        $agencyClass = $this->getAgencyModel();

        $query = $agencyClass::query();

        // Apply date filters
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $totalAgencies = (clone $query)->count();
        $activeAgencies = (clone $query)->where('status', 1)->count();
        $inactiveAgencies = (clone $query)->where('status', 0)->count();
        $pendingAgencies = (clone $query)->where('status', 2)->count();

        return [
            'total' => $totalAgencies,
            'active' => $activeAgencies,
            'inactive' => $inactiveAgencies,
            'pending' => $pendingAgencies,
        ];
    }
}
