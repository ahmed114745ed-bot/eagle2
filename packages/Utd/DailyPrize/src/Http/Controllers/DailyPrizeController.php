<?php

namespace Utd\DailyPrize\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DailyPrizeController extends Controller
{
    public function index()
    {
        return view('dailyprize::index');
    }

    public function create()
    {
        return view('dailyprize::create');
    }

    public function store(Request $request): RedirectResponse
    {
        //
    }

    public function show($id)
    {
        return view('dailyprize::show');
    }

    public function edit($id)
    {
        return view('dailyprize::edit');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
