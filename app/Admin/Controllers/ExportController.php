<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\AgencyExporter;
use App\Admin\Extensions\UserExporter;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;


class ExportController extends Controller
{


    public function usersSallaryTargets()
    {
        $export = new UserExporter();
        $fileName = 'users_target_salary.csv';

        return Excel::download($export, $fileName);
    }
    public function usersAgencyTargets()
    {
        $export = new AgencyExporter();
        $fileName = 'agency_target_salary.csv';

        return Excel::download($export, $fileName);
    }

}
