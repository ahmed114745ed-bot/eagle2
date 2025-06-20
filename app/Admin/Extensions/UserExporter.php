<?php

namespace App\Admin\Extensions;

use App\Models\User;
use App\Models\UserSallary;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExporter implements FromCollection, WithColumnWidths, WithHeadings
{
    protected $fileName = 'users_list.csv';

    public function collection()
    {
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $query = User::query()
            ->whereNotNull('agency_id')
            ->where('agency_id', '!=', 0);

        if (request('agency_id')) {
            $query->where('agency_id', request('agency_id'));
        }

        $users = $query->get();
        $shippingData = [];

        foreach ($users as $user) {
            $target = $user->target($month, $year);

            $salary = UserSallary::where('user_id', $user->id)
                ->where('month', '<=', $month)
                ->where('year', '<=', $year)
                ->where('is_paid', 0)
                ->whereNotNull('user_agency_id')
                ->select([
                    DB::raw('SUM(sallary) AS target'),
                    DB::raw('SUM(cut_amount) AS expenses'),
                    DB::raw('SUM(achieved_diamond) AS achieved_diamond'),
                    DB::raw('SUM(sallary) - SUM(cut_amount) AS salary'),
                    DB::raw('MAX(days) AS achieved_days'),
                    DB::raw('MAX(hours) AS achieved_hours'),
                    DB::raw('MAX(extras) AS extras'),
                    DB::raw('MAX(user_agency_id) AS user_agency_id'),
                ])
                ->groupBy('user_id')
                ->first();

            $extras = json_decode($salary->extras ?? '{}', true);
            $moment = $extras['moment'] ?? [];
            $reel = $extras['reel'] ?? [];

            $shippingData[] = [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'diamonds' => number_format((int) ($salary->achieved_diamond ?? 0)).' 💎',
                'days' => $salary->achieved_days ?? '0/0',
                'hours' => $salary->achieved_hours ?? '0/0',
                'moment' => $this->formatExtras($moment),
                'reel' => $this->formatExtras($reel),
                'salary' => ($salary->target ?? 0).' 💲',
                'withdrawn' => $salary->expenses ?? 0,
                'remaining' => $salary->salary ?? 0,
                'agency' => optional($salary?->agency)->name ?? '-',
                'agency_id' => $salary->user_agency_id ?? '-',
                'month' => $month,
                'year' => $year,
            ];
        }

        return collect($shippingData);
    }

    public function headings(): array
    {
        return [
            __('uuid', [], 'ar'),
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

    public function columnWidths(): array
    {
        return [
            'F' => 40,
            'G' => 100,
        ];
    }

    protected function formatExtras(array $data): string
    {
        return sprintf(
            "رفع: %s\nإعجاب: %s\nتعليق: %s",
            $data['upload'] ?? '0/0',
            $data['likes'] ?? '0/0',
            $data['comments'] ?? '0/0'
        );
    }
}
