<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\Agency;
use App\Models\UserSallary;
use App\Models\AgencySallary;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;


class WalletExportUser  implements FromCollection, WithHeadings
{




    protected $fileName = 'users_list.csv';
    protected $headings = [
        "id",
        "name",
        'balance',
        'expenses',
        'salary',

    ];

    public $uuid;


    public function __construct($uuid = null)
    {
        $this->uuid = $uuid;
    }

    /**
     * @inheritDoc
     */
    public function collection()
    {
        $uuid = $this->uuid;
        $query = User::query();

        if ($this->uuid) {
            $query->where('uuid',  $uuid);
        }

        $users = $query->get();

        $arr = [];

        foreach ($users as $user) {


            $userSalarys = UserSallary::query()
                ->where('user_id', $user->id)
                ->where('is_paid', 0)

                ->select(
                    DB::raw('SUM(`sallary`) AS target'),
                    DB::raw('SUM(`cut_amount`) AS expenses'),
                    DB::raw('SUM(sallary) - SUM(cut_amount) AS salary')
                )
                ->groupBy('user_id')
                ->first();

            $arr[] = [
                'id' => $user->id,
                'name' => $user->name,
                'balance' => round($userSalarys->target ?? 0, 2) . '💲',
                'withdrawal' => round($userSalarys->expenses ?? 0, 2) . '💲',
                'salary' => round($userSalarys->salary ?? 0, 2) . '💲',

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
