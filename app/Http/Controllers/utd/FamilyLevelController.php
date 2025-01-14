<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\FamilyLevel;
use App\Tik\Services\FamilyLevelService;
use Illuminate\Http\Request;
use Exception;

class FamilyLevelController extends Controller
{
    public function __construct(private FamilyLevelService $FamilyLevelService) {}
    public function index(Request $request)
    {
        
        $FamilyLevel = $this->FamilyLevelService->index();
        return Common::apiResponse(true, '', $FamilyLevel, 200);
    }
    public function show( $id,Request $request)
    {
        $FamilyLevel = FamilyLevel::find($id);
        return Common::apiResponse(true, '', $FamilyLevel, 200);
    }


    public function store(Request $request)
    {
 
        try {
            $FamilyLevel =   $this->FamilyLevelService->create( $request);
        } catch (\Exception $e) {
    
            return Common::apiResponse(0, $e != null ? $e->getMessage() : 'missing params', 422);
        }

        return Common::apiResponse(true, '', $FamilyLevel, 200);

    }


    

    public function update($id ,Request $request)
    {
        
        try {
            $family = $this->FamilyLevelService->update( $request, $id);
        } catch (Exception $e) {
            return Common::apiResponse(0, $e->getMessage(), 422);
        }

        return Common::apiResponse(1, '', new FamilyResource($family));
    }


    public function destroy(Request $request)
    {
        

        try {

            $this->FamilyLevelService->delete($request->id);

            return Common::apiResponse(1, 'success', null, 200);
        } catch (\Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage() ?? 'failed', null, 400);
        }
    }
    
}
