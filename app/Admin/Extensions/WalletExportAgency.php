<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\Agency;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;


class WalletExportAgency  implements FromCollection, WithHeadings
{




    protected $fileName = 'agencies_list.csv';
    protected $headings = [
        "id",
        "name",
        'balance',
        'withdrawal',
        'alary',

    ];

    public $id;


    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        $id = $this->id;
        $query = Agency::query();
        if ($this->id) {
           
            $query->where('id', $id) // Match directly on agency_id
                ->orWhereHas('owner', function ($subQuery) use ($id) {
                    $subQuery->where('uuid', $id); // Match on related owner UUID
                });
        }

        $agencies = $query->get();

        $arr = [];

        foreach ($agencies as $agency) {


            $agencySalarys = AgencySallary::query()
                ->where('agency_id', $agency->id)
                ->where('is_paid', 0)

                ->select(
                    DB::raw('SUM(`sallary`) AS target'),
                    DB::raw('SUM(`cut_amount`) AS expenses'),
                    DB::raw('SUM(sallary) - SUM(cut_amount) AS salary')
                )
                ->groupBy('agency_id')
                ->first();

            $arr[] = [
                'id' => $agency->id,
                'name' => $agency->name,
                'balance' => round($agencySalarys->target ?? 0, 2). '💲',
                'withdrawal' => round($agencySalarys->expenses ?? 0, 2). '💲',
                'salary' => round($agencySalarys->salary ?? 0, 2). '💲',

            ];
        }

        return collect($arr);
    }


    public function headings(): array
    {
        return [
            __("id", [], 'ar'),
            __('name', [], 'ar'),
            __('wallet balance', [], 'ar'),
            __('withdrawal', [], 'ar'),
            __('salary', [], 'ar'),

        ];
    }
}
