<?php

namespace App\Http\Controllers\Dashboard\Interests;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use App\Traits\Dashboard\DashBoardTrait;
use Illuminate\Http\Request;

class AdminInterestsController extends Controller
{
    use DashBoardTrait;

    public function index()
    {
        $data = Interest::orderBy('sort','asc')->get();
        return $data;
    }

    public function sort()
    {

        $items = Interest::orderBy('id','desc')->get();
        $index = 0 ;
        foreach ($items as $item) {
            $index ++;
            $item->sort = $index;
            $item->save();
        }
        return 200;
    }

    function change_sort(Request $request) {
        $Interest = Interest::find($request->id);
        if($Interest)
        {
            if($Interest->sort  > $request->new_num)
            {
                $items = Interest::where('id','not Like',$request->id)->where('sort','>=',$request->new_num)->orderBy('sort','asc')->get();
                foreach ($items as $item) {
                    $item->sort +=1;
                    $item->update();
                }
            }
            else{
                $items = Interest::where('id','not Like',$request->id)->where('sort','<=',$request->new_num)->orderBy('sort','asc')->get();
                foreach ($items as $item) {
                    $item->sort -=1;
                    $item->update();
                }
            }

             $Interest->sort  =$request->new_num;
            $Interest->update();

        }
        return 200;
    }

    public function store(Request $request)
    {
        $request->validate([
            'img'        => 'required|image|mimes:png,jpg',
            'name'       => 'required|max:255',
        ]);
        $last_num = Interest::orderBy('sort','desc')->first();
        $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'images') : null;

        $data = new Interest();
        $data->img    = $img;
        $data->name = $request->name;
        $data->sort  = $last_num ? $last_num ->sort + 1 : 1;
        $data->save();
        return response()->json([
            'status' => 200 ,
        ]);
    }

    public function show(string $id)
    {
        $data = Interest::find($id);
        return $data;
    }

    public function update(Request $request, string $id)
    {
        $data = Interest::find($id);
            $request->validate([
            'name'       => 'required|max:255',
        ]);
        if( $request->hasFile('img'))
        {
            $this->delete_img($data->img);
            $img = $request->hasFile('img') ? $this->store_img($request->file('img'), 'images') : null;;
            $data->img   = $img ;
        }
        $data->name = $request->name;
        $data->save();
        return response()->json([
            'status' => 200 ,
        ]);
    }

    public function destroy(string $id)
    {
        $Interest = Interest::find($id);
        if( $Interest->img)
        {
            $this->delete_img($Interest->img);
        }
        $Interest->delete();
        return 200;
    }
}
