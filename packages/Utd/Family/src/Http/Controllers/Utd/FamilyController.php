<?php

namespace Utd\Family\Http\Controllers\Utd;

use Utd\Family\Http\Controllers\Controller;

use Utd\Family\Entities\Family;
use Utd\Family\Entities\FamilyUser;
use Illuminate\Http\Request;
use Utd\Family\Services\FamilyService;

class FamilyController extends Controller
{
    use DashBoardTrait;

    protected $familyService;

    public function __construct(FamilyService $familyService)
    {
        $this->familyService = $familyService;
    }

    public function index()
    {

        $perPage = request('per_page') ?? 10;
        $families = Family::paginate($perPage);


        return family_helper('common')::apiResponse(true, '', $families, 200);
    }

    public function all()
    {

        $families = Family::all()->map(function($family){
            return [
                'id' => $family->id,
                'name' => $family->name,
            ];
        });


        return family_helper('common')::apiResponse(true, '', $families, 200);
    }

    public function store(Request $request)
    {

        $image = null;

        if ($request->has('image')) {
            $image = family_helper('common')::upload('images', $request->image);
        }

        $family  = Family::create([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'image' => $image,
            'user_id' => $request->user_id,
            'num' => $request->num,
        ]);

        // Update the user's family_id
        User::where('id', $family->user_id)->update(['family_id' => $family->id]);

        // Create FamilyUser entry
        FamilyUser::create([
            'user_id'   => $family->user_id,
            'family_id' => $family->id,
            'user_type' => 2,
            'status'    => 1,
        ]);

        return family_helper('common')::apiResponse(true, '',  [], 200);
    }

    public function update(Request $request, $id)
    {


        if ($request->has('image')) {
            $image = family_helper('common')::upload('images', $request->image);
            Family::findOrFail($id)->update([
                'image' => $image
            ]);
        }
        $family =  Family::findOrFail($id);

        if ($family->user_id != $request->user_id) {
            User::where('id', $request->user_id)->update(['family_id' => null]);
            FamilyUser::where([
                'user_id' => $request->user_id,
                'family_id' => $family->id,
                'user_type' => 2,
                'status' => 1,
            ])->delete();

            User::where('id', $request->user_id)->update(['family_id' => $family->id]);
            FamilyUser::create([
                'user_id' => $request->user_id,
                'family_id' => $family->id,
                'user_type' => 2,
                'status' => 1,
            ]);
        }
        $family->update([
            'name' => $request->name,
            'introduce' => $request->introduce,
            'notice' => $request->notice,
            'is_success' => $request->is_success,
            'user_id' => $request->user_id,
            'num' => $request->num
        ]);

        return family_helper('common')::apiResponse(1, 'Family updated successfully');
    }

    public function show($id)
    {
        $family = Family::findOrFail($id);

        return family_helper('common')::apiResponse(true, '', $family, 200);
    }

    public function destroy($id)
    {

        Family::findOrFail($id)->delete();

        return family_helper('common')::apiResponse(1, 'success', null, 200);
    }

    public function delete_all(Request $request)
    {
        $request->validate([
            'ids' => 'required'
        ]);

        $ids = explode(',', $request->ids);

        Family::whereIn('id', $ids)->delete();

        return family_helper('common')::apiResponse(1, 'success');
    }

    public function kickFamily($id)
    {
        try {
            $this->familyService->kickFamily($id);
            return family_helper('common')::apiResponse(true, 'removed');
        } catch (\Exception $exception) {

            return family_helper('common')::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
