<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\SalaryTrx;
use Illuminate\Http\Request;

class SallariesHistoryController extends Controller
{
    public function index()
    {

        $type = request('type') ?? 0; // 0 => users , 1 => agencies
        $search = request('search');
        $result = SalaryTrx::where('type', $type)
            ->when($search, function ($q) use ($search) {
                $q->where('id', $search);
            })
            ->paginate(10)
            ->through(function($salary){
                return [
                    'id' => $salary->id,
                    'oid' => $salary->oid,
                    'type' => $salary->type == 0 ? 'User' : 'Agency',
                    'amount' => $salary->amount
                ];
            });

        return Common::apiResponse(true, 'Success', $result);
    }

    public function delete($id){

        SalaryTrx::findOrFail($id)->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function delete_all(Request $request){
        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        SalaryTrx::whereIn('id', $ids)->delete();

        return Common::apiResponse(true, 'Success');

    }
}
