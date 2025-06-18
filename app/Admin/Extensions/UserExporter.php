<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\UserTarget;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\FromCollection;


class UserExporter implements FromCollection, WithHeadings
{



    protected $fileName = 'users_list.csv';
    protected $headings = [
       
        "uuid",
        "name",
        'diamonds',
        'salary',
        'withdrawn',
        'remaining',
        'agency',
        'month',
        'year'
    ];





    /**
     * @inheritDoc
     */
    public function collection()
    {
        $month = request('month') ?? now()->month;
        $year = request('year') ?? now()->year;
    
        $users = User::query()
            ->whereNotNull('agency_id')
            ->where('agency_id', '!=', 0);
    
        if (request('agency_id')) {
            $users->where('agency_id', request('agency_id'));
        }
    
        $users = $users->get();
        $arr = [];
    
        foreach ($users as $user) {
            $target = $user->target($month, $year);
    
            $userSalarys = UserSallary::where('user_id', $user->id)
                ->where('month', '<=', $month)
                ->where('year', '<=', $year)
                ->where('is_paid', 0)
                ->whereNotNull('user_agency_id')
                ->select(
                    DB::raw('SUM(sallary) AS target'),
                    DB::raw('SUM(cut_amount) AS expenses'),
                    DB::raw('SUM(achieved_diamond) AS achieved_diamond'),
                    DB::raw('SUM(sallary) - SUM(cut_amount) AS salary'),
                    DB::raw('MAX(days) AS achieved_days'),
                    DB::raw('MAX(hours) AS achieved_hours'),
                    DB::raw('MAX(extras) AS extras'),
                    DB::raw('MAX(user_agency_id) AS user_agency_id')
                )
                ->groupBy('user_id')
                ->first();
    
            $diamonds = UserTarget::where('user_id', $user->id)
                ->where('add_month', '<=', $month)
                ->where('add_year', '<=', $year)
                ->whereNotNull('agency_id')
                ->select(DB::raw('SUM(user_diamonds) AS diamond'))
                ->groupBy('user_id')
                ->first();
    
            // Parse extras JSON
            $extras = json_decode($userSalarys?->extras ?? '{}', true);
            $moment = $extras['moment'] ?? [];
            $reel = $extras['reel'] ?? [];
    
            $arr[] = [
                'uuid'             => $user->uuid,
                'name'             => $user->name,
                'diamonds'         => $userSalarys->achieved_diamond ?? "0",
                'days'             => $userSalarys->achieved_days ?? "0/0",
                'hours'            => $userSalarys->achieved_hours ?? "0/0",
                'moment' => [
                        'upload'   => $moment['upload'] ?? '0/0',
                        'likes'    => $moment['likes'] ?? '0/0',
                        'comments' => $moment['comments'] ?? '0/0',
                ],
                'reel' => [
                        'upload'   => $reel['upload'] ?? '0/0',
                        'likes'    => $reel['likes'] ?? '0/0',
                        'comments' => $reel['comments'] ?? '0/0',
                ],
                'salary'           => $userSalarys->target ?? "0",
                'withdrawn'        => $userSalarys->expenses ?? "0",
                'remaining'        => $userSalarys->salary ?? "0",
                'agency'           => optional($user->agency)->name ?? '-',
                'agency_id'        => $userSalarys->user_agency_id ?? '-',
                'month'            => $month,
                'year'             => $year,
             
            ];
        }
    
        return collect($arr);
    }
    

    public function headings(): array
    {
        return [
            __("uuid", [], 'ar'),
            __('name', [], 'ar'),
            __('diamonds', [], 'ar'),
            __('days', [], 'ar'),
            __('hours', [], 'ar'),
            __('moment', [], 'ar'),
            __('reel', [], 'ar'),
            __('salary', [], 'ar'),
            __('withdrawn', [], 'ar'),
            __('remaining', [], 'ar'),
            __('agency', [], 'ar'),
            __('agency_id', [], 'ar'),
            __('month', [], 'ar'),
            __('year', [], 'ar'),
        
        ];
    
    }
}
