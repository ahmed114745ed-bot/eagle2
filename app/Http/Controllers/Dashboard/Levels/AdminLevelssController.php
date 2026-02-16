<?php

namespace App\Http\Controllers\Dashboard\Levels;

use App\Http\Controllers\Controller;
use Utd\Vip\Entities\Vip;
use App\Support\PackageHelper;
use App\Traits\Dashboard\DashBoardTrait;
use Illuminate\Http\Request;

class AdminLevelssController extends Controller
{
    use DashBoardTrait;
    public function index($type)
    {
        if (!PackageHelper::isInstalled('vip')) {
            return collect();
        }
        $data = Vip::where('type',$type)->orderBy('level','desc')->get();
        return $data;
    }

    public function store(Request $request)
    {
        if (!PackageHelper::isInstalled('vip')) {
            return response()->json(['status' => 400, 'message' => 'VIP package not installed'], 400);
        }
        $request->validate([
            'img'        => 'required|image|mimes:png,jpg',
            'level'       => 'required|max:255',
            'type'        => 'required|max:255',
            'exp'        => 'required|max:255',
        ]);

        $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'images') : null;
        Vip::insert([
            'level'         => $request->level ,
            'img'          => $img,
            'type'          => $request->type ,
            'exp'      => $request->exp ,
        ]);
        return response()->json([
            'status' => 200 ,
        ]);
    }

    public function show(string $id)
    {
        if (!PackageHelper::isInstalled('vip')) {
            return null;
        }
        $data = Vip::find($id);
        return $data;
    }

    public function update(Request $request, string $id)
    {
        if (!PackageHelper::isInstalled('vip')) {
            return response()->json(['status' => 400, 'message' => 'VIP package not installed'], 400);
        }
        $Vip = Vip::find($id);
        $request->validate([
            'level'       => 'required|max:255',
            'type'        => 'required|max:255',
            'exp'        => 'required|max:255',
        ]);
        if( $request->hasFile('img'))
        {
            $this->delete_img($Vip->img);
            $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'images') : null;;
            $Vip->img   = $img ;
        }
        $Vip->level       = $request->level ;
        $Vip->type    = $request->type ;
        $Vip->exp        = $request->exp ;
        $Vip->update();
        return 200;
    }

    public function destroy(string $id)
    {
        if (!PackageHelper::isInstalled('vip')) {
            return response()->json(['status' => 400, 'message' => 'VIP package not installed'], 400);
        }
        $Vip = Vip::find($id);
        if( $Vip->img)
        {
            $this->delete_img($Vip->img);
        }
        $Vip->delete();
        return 200;
    }
}
