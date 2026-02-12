<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Utd\Agency\Contracts\ExternalModuleInterface;

class HistoryAgencyResource extends JsonResource
{
    public function toArray($request)
    {
        $year = request('year') ?? Carbon::now()->year;
        $month = request('month') ?? Carbon::now()->month;

        $giftLog = $this->getTopReceivers($year, $month);
        $heroGiftLog = $this->getTopSenders($year, $month);

        $isOwner = Auth::user()->id === $this->app_owner_id;
        $salary = $isOwner ? $this->getSalary($year, $month) : 0;
        $target = $isOwner ? $this->calculateTarget($heroGiftLog) : 0;

        return [
            'star' => ReceiverGiftLogForKickedResource::collection($giftLog),
            'heroes' => SenderGiftLogResource::collection($heroGiftLog),
            'salary' => $isOwner ? (string) $salary : '0',
            'target' => $target,
        ];
    }

    private function getTopReceivers($year, $month)
    {
        $externalModule = app(ExternalModuleInterface::class);
        $giftLogModel = $externalModule->get('models.gift_log');

        return $giftLogModel::where('agency_id', $this->id)
            ->selectRaw('
                SUM(giftPrice) as exp, 
                receiver_id,
                EXISTS (
                    SELECT 1 FROM gift_logs gl 
                    WHERE gl.receiver_id = gift_logs.receiver_id 
                    AND gl.is_finished = 1
                ) as is_kicked
            ')
            ->with('receiver')
            ->groupBy('receiver_id')
            ->whereHas('receiver')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->having('exp', '>', 0)
            ->orderByDesc('exp')
            ->take(3)
            ->get();
    }

    private function getTopSenders($year, $month)
    {
        $externalModule = app(ExternalModuleInterface::class);
        $giftLogModel = $externalModule->get('models.gift_log');

        return $giftLogModel::where('agency_id', $this->id)
            ->selectRaw('SUM(giftPrice) as exp, sender_id, is_finished')
            ->with('sender')
            ->whereHas('sender')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('is_finished', 0)
            ->groupBy('sender_id', 'is_finished')
            ->orderByDesc('exp')
            ->take(3)
            ->get();
    }

    private function getSalary($year, $month)
    {
        $externalModule = app(ExternalModuleInterface::class);
        $agencySalaryModel = $externalModule->get('models.agency_salary');

        return $agencySalaryModel::where('agency_id', $this->id)
            ->where('year', $year)
            ->where('month', $month)
            ->sum('sallary');
    }

    private function calculateTarget($heroGiftLog)
    {
        return 0;
    }
}
