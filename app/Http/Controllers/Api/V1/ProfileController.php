<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Services\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function update(ProfileRequest $request)
    {
       $out = $this->profileService->updateProfile($request);
 
        return Common::apiResponse(true, 'profile updated successfully', $out, 200);
    }

    public function show(Request $request, $id)
    {
        $me = $request->user();
        return $this->profileService->showProfile($me, $id);
    }
}

