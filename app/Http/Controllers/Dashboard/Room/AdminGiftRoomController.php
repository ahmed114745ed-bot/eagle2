<?php

namespace App\Http\Controllers\Dashboard\Room;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\Room\AdminGiftsResource;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\LuckyGift;
use App\Traits\Dashboard\DashBoardTrait;

class AdminGiftRoomController extends Controller
{
    use DashBoardTrait;

    public function index($type)
    {
       $data = Gift::where('type',$type)->orderBy('sort','asc')->get();
       return AdminGiftsResource::collection($data);
    }

    public function enable_gift(Request $request, $id , $status , $type)
    {
        $Gift = Gift::find($id);
        if($Gift)
        {
            if($type == 'music_gift')
            {
                $Gift->music_gift = $status  == 'true' ? 1 : 0;
            }
            else{
                $Gift->enable  = $status  == 'true' ? 1 : 0;
            }
            $Gift->update() ;
        }
        return response()->json([
            'status' => 200,
        ]);
    }

    public function sort()
    {
        $types = [
            ['id' => 1, 'name' => 'normal'],
            ['id' => 2, 'name' => 'hot'],
            ['id' => 3, 'name' => 'country'],
            ['id' => 4, 'name' => 'Moment'],
            ['id' => 5, 'name' => 'Famous gifts'],
            ['id' => 6, 'name' => 'Lucky gifts'],
            ['id' => 7, 'name' => 'Event'],
        ];
        foreach($types as $type)
        {
            $items = Gift::where('type',$type['id'])->orderBy('id','desc')->get();
            $index = 0 ;
            foreach ($items as $item) {
                $index ++;
                $item->sort = $index;
                $item->update();
            }
        }
        return 200;
    }

    function change_sort(Request $request) {
        $Gift = Gift::find($request->id);
        if($Gift)
        {
            if($Gift->sort  > $request->new_num)
            {
                $Gifts = Gift::where('type',$Gift->type)->where('id','not Like',$request->id)->where('sort','>=',$request->new_num)->orderBy('sort','asc')->get();
                foreach ($Gifts as $item) {
                    $item->sort +=1;
                    $item->update();
                }
            }
            else{
                $Gifts = Gift::where('type',$Gift->type)->where('id','not Like',$request->id)->where('sort','<=',$request->new_num)->orderBy('sort','asc')->get();
                foreach ($Gifts as $item) {
                    $item->sort -=1;
                    $item->update();
                }
            }
            $Gift->sort  =$request->new_num;
            $Gift->update();
        }
        return 200;
    }

    public function store(Request $request)
    {
        $request->validate([
            'enable'           => 'required|max:255',
            'music_gift'       => 'required|max:255',
            'name'             => 'required|max:255',
            'price'            => 'required|max:255',
            'type_id'          => 'required|max:255',
            'image_type'       => 'required|max:255',
            'show_img'         => 'required',
            'show_img2'        => 'required',
            'win_percentage'   => 'required|max:100',
            'mini_percentage'  => 'required|max:100',
            'mid_percentage'   => 'required|max:100',
            'max_percentage'   => 'required|max:100',
        ]);
        if($request->type_id === '6')
        {
            if($request->win_percentage > 100 || $request->win_percentage  < 10 )
            {
                return response()->json([
                    'message' => "An error occurred",
                    "errors" => ["win_percentage" => "win percentage must be between 10 and 100"]
                ],422);
            }
            if($request->mini_percentage + $request->mid_percentage + $request->max_percentage !== 100)
            {
                return response()->json([
                    'message' => (int)$request->mini_percentage , (int)$request->mid_percentage , (int)$request->max_percentage,
                    "errors" => [
                        "mini_percentage" => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100.",
                        "mid_percentage"  => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100.",
                        "max_percentage"  => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100."
                    ]
                ],422);
            }
        }
        $last_num = Gift::where('type',$request->type_id)->first()->sort;
        $show_img_name = $request->hasFile('show_img') ? $this->store_img($request->file('show_img'), 'images') : null;;
        $show_img2_name = $request->hasFile('show_img2') ? $this->store_img($request->file('show_img2'), 'images') : null;
        $giftId  = Gift::insertGetId([
            'enable'               => $request->enable ,
            'music_gift'           => $request->music_gift ,
            'name'                 => $request->name,
            'price'                => $request->price,
            'type'                 => $request->type_id,
            'image_type'           => $request->image_type,
            'show_img'             => $show_img_name ,
            'show_img2'            => $show_img2_name,
        ]);
        if($request->type_id === '6')
        {
            $values = [ $request->mini_percentage,  $request->mid_percentage,  $request->max_percentage];
            $implodeValues = implode(',', $values);
            LuckyGift::insert([
                'gift_id'              => $giftId  ,
                'win_probability'      => $request->win_percentage ,
                'min_percentage'       =>$implodeValues,
            ]);
        }
        $Gift_last = Gift::where('type',$request->type_id)->orderBy('id','desc')->first();
        $Gift_last->sort = $last_num+1;
        $Gift_last->save();
        return 200;
    }

    public function show(string $id)
    {
        $data = Gift::with('lucky_gift')->find($id);
        return  $data ;
    }

    public function update(Request $request, string $id)
    {
        $Gift = Gift::find($id);
        $request->validate([
            'enable'           => 'required|max:255',
            'music_gift'       => 'required|max:255',
            'name'             => 'required|max:255',
            'price'            => 'required|max:255',
            'type_id'          => 'required|max:255',
            'image_type'       => 'required|max:255',
            'win_percentage'   => 'required|max:100',
            'mini_percentage'  => 'required|max:100',
            'mid_percentage'   => 'required|max:100',
            'max_percentage'   => 'required|max:100',
        ]);
        if($request->type_id === '6')
        {
            if($request->win_percentage > 100 || $request->win_percentage  < 10 )
            {
                return response()->json([
                    'message' => "An error occurred",
                    "errors" => ["win_percentage" => "win percentage must be between 10 and 100"]
                ],422);
            }
            if($request->mini_percentage + $request->mid_percentage + $request->max_percentage !== 100)
            {
                return response()->json([
                    'message' => (int)$request->mini_percentage , (int)$request->mid_percentage , (int)$request->max_percentage,
                    "errors" => [
                        "mini_percentage" => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100.",
                        "mid_percentage"  => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100.",
                        "max_percentage"  => "Ensure that the sum of the minimum, midrange, and maximum percentages equals 100."
                    ]
                ],422);
            }
        }
        if( $request->hasFile('show_img'))
        {
            $this->delete_img($Gift->show_img);
            $show_img_name = $request->hasFile('show_img') ? $this->store_img($request->file('show_img'), 'images') : null;;
            $Gift->show_img   = $show_img_name ;
        }
        if( $request->hasFile('show_img2'))
        {
            $this->delete_img($Gift->show_img2);
            $show_img2_name = $request->hasFile('show_img2') ? $this->store_img($request->file('show_img2'), 'images') : null;
            $Gift->show_img2       = $show_img2_name;
        }

        $Gift->enable          = $request->enable ;
        $Gift->music_gift      = $request->music_gift ;
        $Gift->name            = $request->name;
        $Gift->type            = $request->type_id;
        $Gift->image_type      = $request->image_type;
        $Gift->update();

        if($request->type_id === '6')
        {
            $values = [ $request->mini_percentage,  $request->mid_percentage,  $request->max_percentage];
            $implodeValues = implode(',', $values);
            LuckyGift::updateOrInsert(
                ['gift_id' => $id],
                [
                'win_probability'      => $request->win_percentage ,
                'min_percentage'       =>$implodeValues,
            ]);
        }
        else{
            LuckyGift::where('gift_id',$id)->delete();
        }
        return 200;
    }

    public function destroy(string $id)
    {
        $ware = Gift::find($id);
        $this->delete_img($ware->show_img);
        $this->delete_img($ware->show_img2);
        $ware->delete();
        return 200;
    }
}
