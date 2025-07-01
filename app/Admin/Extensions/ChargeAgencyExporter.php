<?php

namespace App\Admin\Extensions;

use App\Models\ShippingAgency;
use App\Models\User;
use App\Models\Agency;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\SalaryTransaction\Entities\ChargeAgency;


class ChargeAgencyExporter  implements FromCollection,WithHeadings
{



    public $agency_id;
    protected $fileName = 'agencies_list.csv';
    public $month;
    public $year;

    public function __construct($agency_id = null, $month = null, $year = null)
    {
        $this->agency_id = $agency_id;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        $agencies = ShippingAgency::with(['owner.profile'])->get();

        $arr = [];

        foreach ($agencies as $agency) {
            $item['id'] = $agency->id;
            $item['name'] = $agency->name;
            $item['owner_name'] = optional($agency->owner)->name ?? '-';
            $item['owner_uuid'] = optional($agency->owner)->uuid ?? '-';
            $item['owner_phone'] = '"' . optional($agency->owner)->phone . '"';
            $item['charge_agency'] = ChargeAgency::where('agency_id', $agency->id)->exists() ? 'Yes' : 'No';
            $item['appear_charger_agency'] = optional($agency->owner)->appear_charger_agency ? 'Yes' : 'No';
            $item['is_frozen'] = $agency->is_frozen ? 'Yes' : 'No';

            $arr[] = $item;
        }

        return collect($arr);
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Agency Name'),
            __('Owner Name'),
            __('Owner UUID'),
            __('Owner Phone'),
            __('Charge Agency_'),
            __('Appear Charger Agency'),
            __('Is Frozen'),
        ];
    }
}
