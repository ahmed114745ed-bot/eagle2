<?php

namespace App\Http\Controllers\utd;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\PaymentWithdrawType;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function index()
    {
        $search = request('search');

        $result = PaymentWithdrawType::when($search, function ($q) use ($search) {
            $q->where('id', $search);
        })
            ->paginate(10);

        return Common::apiResponse(true, 'Success', $result);
    }

    public function show($id)
    {
        $result = PaymentWithdrawType::findOrFail($id);

        return Common::apiResponse(true, 'Success', $result);
    }

    public function delete($id)
    {
        $result = PaymentWithdrawType::findOrFail($id);
        $result->withdrawFields()->delete();
        $result->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function delete_all(Request $request)
    {

        $request->validate([
            'ids' => 'required'
        ]);

        $ids  = explode(',', $request->ids);

        $paymentWithdrawTypes = PaymentWithdrawType::whereIn('id', $ids)->get();

        foreach ($paymentWithdrawTypes as $paymentWithdrawType) {
            $paymentWithdrawType->withdrawFields()->delete();
        }

        PaymentWithdrawType::whereIn('id', $ids)->delete();

        return Common::apiResponse(true, 'Success');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'name_en' => 'required|string',
            'image' => 'nullable|image',
            'min_value' => 'required|numeric',
            'exchange_rate' => 'required|numeric',
            'withdrawFields' => 'nullable|string',
        ]);


        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = Common::upload('images', $request->file('image'));
        }

        $paymentWithdrawType = PaymentWithdrawType::create($data);

        if ($request->has('withdrawFields')) {
            $json_decoded  = json_decode($request->withdrawFields, true);

            $paymentWithdrawType->withdrawFields()->createMany($json_decoded);
        }

        return Common::apiResponse(true, 'Success', $paymentWithdrawType);
    }

    public function update($id, Request $request){
        $request->validate([
            'name' => 'required|string',
            'name_en' => 'required|string',
            'image' => 'nullable|image',
            'min_value' => 'required|numeric',
            'exchange_rate' => 'required|numeric',
            'withdrawFields' => 'nullable|string',
        ]);

    $paymentWithdrawType = PaymentWithdrawType::findOrFail($id);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = Common::upload('images', $request->file('image'));
        }

        $paymentWithdrawType->update($data);

        if ($request->has('withdrawFields')) {
            $paymentWithdrawType->withdrawFields()->delete();
            $json_decoded = json_decode($request->withdrawFields,true);
            $paymentWithdrawType->withdrawFields()->createMany($json_decoded);
        }

        return Common::apiResponse(true, 'Success', $paymentWithdrawType);
    }
}
