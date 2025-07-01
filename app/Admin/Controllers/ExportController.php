<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Extensions\UserExporter;
use App\Admin\Extensions\AgencyExporter;
use App\Admin\Extensions\WalletExportUser;
use App\Admin\Extensions\WalletExportAgency;
use App\Admin\Extensions\AgencyMangerExporter;
use App\Admin\Extensions\ChargeAgencyExporter;


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
        // $export = new AgencyExporter();
        // $fileName = 'agency_target_salary.csv';

        // return Excel::download($export, $fileName);

        $month = request('month');
        $year = request('year');
        $id = request('id');

        return Excel::download(
            new AgencyExporter($id, $month, $year),
            'agency_report.csv'
        );
    }
    public function agencyMangerExport()
    {
        $export = new AgencyMangerExporter();
        $fileName = 'agency_manger_target_salary.csv';

        return Excel::download($export, $fileName);
    }
    public function chargeAgencies()
    {
        $export = new ChargeAgencyExporter();
        $fileName = 'charge_agencies.csv';

        return Excel::download($export, $fileName);
    }

    public function walletExportUser()
    {
        $id = request('uuid');
        $export = new WalletExportUser($id);
        $fileName = 'wallet_users.csv';

        return Excel::download($export, $fileName);
    }

    public function walletExportAgency()
    {
        $id = request('id');
      
        $export = new WalletExportAgency($id);
        $fileName = 'wallet_agencies.csv';

        return Excel::download($export, $fileName);
    }
}
