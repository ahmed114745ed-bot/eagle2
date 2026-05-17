<?php

namespace Utd\AreaManager\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Utd\AreaManager\Services\AreaService;
use Utd\AreaManager\Transformers\AreaResource;

class AreaController extends Controller
{
    public function __construct(private readonly AreaService $service)
    {
    }

    public function index()
    {
        $areas = $this->service->all();
        return Common::apiResponse(1, 'success', AreaResource::collection($areas));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        $area = $this->service->create($data);
        return Common::apiResponse(1, 'Area created successfully', new AreaResource($area));
    }

    public function show($id)
    {
        $area = $this->service->find($id);
        return Common::apiResponse(1, 'success', new AreaResource($area));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $area = $this->service->update($id, $request->all());
        return Common::apiResponse(1, 'Area updated successfully', new AreaResource($area));
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return Common::apiResponse(1, 'Area deleted successfully');
    }
}
