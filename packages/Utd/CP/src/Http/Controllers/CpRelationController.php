<?php

namespace Utd\CP\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Utd\CP\Entities\CpRelation;
use Utd\CP\Transformers\CpRelationResource;

class CpRelationController extends Controller
{

    public function index()
    {
        $data = CpRelation::select("id","title","image","price")->get();

        // TODO add user available Cards count and convert this to resource
        return Common::apiResponse(1, '', CpRelationResource::collection($data));
    }

}


