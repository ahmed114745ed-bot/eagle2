<?php

namespace App\Http\Controllers\Dashboard\Posts;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\Posts\AdminReelsResource;
use App\Support\DynamicReals;
use App\Traits\Dashboard\DashBoardTrait;
use Illuminate\Http\Request;

class AdminReelsController extends Controller
{
    use DashBoardTrait;

    public function index(Request $request)
    {
        $realQuery = DynamicReals::queryReal();
        if (!$realQuery) {
            return response()->json(['data' => [], 'message' => 'Reals feature not available'], 200);
        }
        
        $query = $request->get('query');
        $check = json_decode($query);
        if( $check->id !== '')
        {
          if($check->type == 'user_id')
            {
              $data = $realQuery->where('user_id',$check->id)->orderBy('id','desc')->with('comments','likes')->paginate(10);
            }
            else{
                $data = $realQuery->where('description', 'like', '%'.$check->id.'%')->orderBy('id','desc')->with('comments','likes')->paginate(10);
          }
        }
        else{
            $data = $realQuery->orderBy('id','desc')->with('comments','likes')->paginate(10);
        }
        return AdminReelsResource::collection($data);
    }

    public function destroy(string $id)
    {
        $realClass = DynamicReals::getRealClass();
        if (!$realClass) {
            return 500;
        }
        $Real = $realClass::find($id);
        if(!$Real)
        {
            return 500;
        }
        if( $Real->url)
        {
            $this->delete_img($Real->url);
        }
        $Real->delete();
        return 200;
    }
}
