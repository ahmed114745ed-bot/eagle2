<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Tik\Services\FamilyLevelService;
use Illuminate\Http\Request;

class FamilyLevelController extends Controller
{
    public function __construct(private FamilyLevelService $FamilyLevelService) {}
    public function index(Request $request)
    {
        
        $FamilyLevel = $this->FamilyLevelService->index();
        return Common::apiResponse(true, '', $FamilyLevel, 200);
    }
    public function show(Request $request)
    {
        $id= $request->id;
       
        $FamilyLevel = $this->FamilyLevelService->show($id);
        return Common::apiResponse(true, '', $FamilyLevel, 200);
    }
}
