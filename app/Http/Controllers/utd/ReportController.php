<?php

namespace App\Http\Controllers\utd;

use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\AgencyService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ActiveAgencyResource;
use App\Http\Resources\AgencyRequestsResource;


class ReportController extends Controller
{
    public function __construct(private AgencyService $agencyService) {}
}