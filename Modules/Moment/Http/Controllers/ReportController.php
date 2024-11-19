<?php

namespace Modules\Moment\Http\Controllers;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Moment\Entities\Moment;
use Modules\Moment\Entities\Real;
use Modules\Moment\Entities\ReportMoment as ReportMoment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('reals::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request )
    {    
     
      

        return view('reals::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $data =$request->all();
        $data['Reporter_id'] = Auth::user()->uuid;

        $is_set_report = ReportMoment::where('moment_id',$data['moment_id'])->where('Reported_id',$data['Reported_id'])->where('Reporter_id',$data['Reporter_id'])->first();
        if($is_set_report){
            return Common::apiResponse(0, 'You already reported it', [], 402);

        }
        
        $moment= Moment::where('id',$data['moment_id'])->first();
          if(!$moment){
            return Common::apiResponse(0, 'Moment not founded', [], 402);
        }

        $data['Reported_id'] = $moment->user_id;


        $user= User::where('id',$data['Reported_id'])->first();
          if(!$user){
            return Common::apiResponse(0, 'User not found', [], 402);

        }
        $data['Reported_id'] = $user->uuid;

        ReportMoment::create($data);
        if (!$data) {
            return Common::apiResponse(0, 'try agane leter', [], 402);
        }
        return Common::apiResponse(1, 'successful','', 200);


    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('reals::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('reals::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
