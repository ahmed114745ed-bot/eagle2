<?php

namespace Utd\Agency\Http\Controllers\Shipping\Utd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Utd\Agency\Http\Resources\AppearChargerAgencyResource;
use Utd\Agency\Traits\ResolvesExternalDependencies;
use Utd\Agency\Helpers\AgencyHelper;

class AppearChargerAgencyController extends Controller
{
    use ResolvesExternalDependencies;

    public function index()
    {
        $id = request('id');
        $search = request('search');
        $perPage = request('per_page') ?? 10;

        $userClass = $this->getUserModel();
        $users = $userClass::when($id, function ($q) use ($id) {
            $q->where('id', $id);
        })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'LIKE', "%$search%")
                        ->orWhere('uuid', 'LIKE', "%$search%")
                        ->orWhere('phone', 'LIKE', "%$search%");
                });
            })
            ->whereIn('type_user', [3, 4])
            ->paginate($perPage);

        return AgencyHelper::apiResponse(true, 'Success', AppearChargerAgencyResource::collection($users));
    }

    public function update(Request $request, $id)
    {
        $userClass = $this->getUserModel();
        $user = $userClass::findOrFail($id);

        $user->update([
            'appear_charger_agency' => $request->appear_charger_agency
        ]);

        return AgencyHelper::apiResponse(true, 'Success');
    }
}
