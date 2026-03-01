<?php

namespace Utd\RoleRewards\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoleRewardsController extends Controller
{
    public function index()
    {
        return view('rolerewards::index');
    }

    public function create()
    {
        return view('rolerewards::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('rolerewards::show');
    }

    public function edit($id)
    {
        return view('rolerewards::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
