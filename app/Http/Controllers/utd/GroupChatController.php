<?php

namespace App\Http\Controllers\utd;

use App\Http\Controllers\Controller;
use App\Models\GroupChat;
use Illuminate\Http\Request;

class GroupChatController extends Controller
{
    public function index(){

        $per_page = request('per_page') ?? 10;
        $search = request('search');
        $groups = GroupChat::where(function($q) use($search){
            $q->whereHas('user', function($q2) use($search){
                $q2->where('name', 'LIKE', "%$search%")
                ->orWhere('uuid', $search);
            });
        })->paginate($per_page);

        return response()->json([
            'status' => 'success',
            'message' => 'group chats returned successfully',
            'data' => $groups
        ]);
    }

    public function store(Request $request){

        $chat = GroupChat::create([
            'text' => $request->text,
            'user_id'=> $request->user_id
        ]);

        return response()->json([
            'message' => 'Group chat created successfully',
            'data' => $chat,
            'status' => 'success',
        ]);
    }

    public function show($id){
        $group = GroupChat::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Group chat returned successfully',
            'data' => $group
        ]);
    }

    public function update(Request $request, $id){
        GroupChat::findOrFail($id)->update([
            'text' => $request->text,
            'user_id' => $request->user_id
        ]);

        return response()->json([
            'message' => 'Group chat updated successfully',
            'status' => 'success',
        ]);
    }

    public function destroy($id){
        GroupChat::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Group chat deleted successfully',
            'status' => 'success',
        ]);
    }
}
