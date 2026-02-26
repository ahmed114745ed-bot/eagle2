<?php

namespace Utd\SpecialId\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SpecialIdController extends Controller
{
    public function index()
    {
        return view('specialid::index');
    }

    public function create()
    {
        return view('specialid::create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        return view('specialid::show');
    }

    public function edit($id)
    {
        return view('specialid::edit');
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
