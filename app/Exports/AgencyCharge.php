<?php

namespace App\Exports;


use App\Models\Agency;
use App\Models\Charge;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AgencyCharge implements FromView
{
    protected $agency_id;

    public function __construct($agency_id)
    {
        $this->agency_id = $agency_id;
    }

    public function view(): View
    {
        $agencyId = $this->agency_id;
        $agency = Agency::find($agencyId);
        $charges = Charge::where(function ($query) use ($agencyId) {
            $query->where('user_id', $agencyId)->where('user_type', 'agency');
        })->orWhere(function ($query) use ($agencyId) {
            $query->where('charger_id', $agencyId)->where('charger_type', 'agency');
        })->orderByDesc('id')->get();

        return view('admin.excel.chargeAgency', compact('charges','agency'));
    }
}
