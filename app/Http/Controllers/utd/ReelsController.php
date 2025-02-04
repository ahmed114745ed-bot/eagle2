<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\RealResource;
use Modules\Reals\Entities\Real;
use App\Tik\Services\ReelsService;
use Illuminate\Http\Request;
use Exception;
use App\Models\Config;


class ReelsController extends Controller
{
    public function __construct(private ReelsService $reelService) {}
    public function index(Request $request)
    {

        try {
            $reels = $this->reelService->index($request->per_page, $request->Page);

            return Common::apiResponse(true, 'success', RealResource::collection($reels) );
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }


    }

    public function user($id,Request $request){
        try {
            $reels = $this->reelService->showByUser($id);
            return Common::apiResponse(true, 'success', RealResource::collection($reels));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
    public function show( $id,Request $request)
    {

        try {
            $reel = $this->reelService->show($id);
            return Common::apiResponse(true, 'success', new RealResource($reel));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

    }

    public function search( $id,Request $request)
    {

        try {
            $reel = $this->reelService->search($id);
            return Common::apiResponse(true, 'success', new RealResource($reel));
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

    }





    public function destroy($id)
    {
        try {
            $reel = $this->reelService->delete($id);
            return Common::apiResponse(true, 'success', null);
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }


    }

    public function reelConfig($num ,Request $request)
    {
        $conf = Config::where('name','upload_reel')->first();
        if(!$conf)
        {
            config::create([
                'name'  => 'upload_reel',
                'value' => $num,
            ]);
        }else{
            $conf->value = $num;
            $conf->save();
        }
        return Common::apiResponse(true, __('dashboard.update'), null);


    }

}
