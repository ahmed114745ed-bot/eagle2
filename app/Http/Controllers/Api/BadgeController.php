<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(){

        $code = request('code','en');

        $host = Config::where('name', $code . '_host')->first();
        $shipping = Config::where('name', $code . '_shipping')->first();
        $agency_owner = Config::where('name', $code . '_agency_owner')->first();

        return [
            'host' => $host,
            'shipping' => $shipping,
            'agency_owner' => $agency_owner
        ];
    }
}
