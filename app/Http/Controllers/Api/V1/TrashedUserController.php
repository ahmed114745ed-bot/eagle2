<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Helpers\Common;

use App\Rules\ValidUsd;
use Illuminate\Http\Request;

use App\Services\UserService;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\TrashedUserResource;


class TrashedUserController extends Controller
{
    public function __construct(private UserService $userService) {}


    public function trashedAccount(Request $request)
    {
        $trashed = $this->userService->trashedAccount($request->perPage, $request->Page, $request->uuid);
        return Common::apiResponse(true, 'success', TrashedUserResource::collection($trashed));
    }
}
