<?php

namespace Modules\UsersWallet\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ProfitTypeResource extends JsonResource
{

        public function toArray($request)
    {
        $target = $this->target; 
        $targetName = $target?->name ?? '---';
        $broadcastTime = $this->hours ?? $this->days ?? 0;
        $totalDiamonds = $this->diamond ?? 0;

        switch ($this->type) {
            case 'bd':
                $profitPercent = $target?->db_percentage ?? 0;
                $role = [
                    'en' => 'BD',
                    'ar' => 'مدير BD',
                    'tr' => 'BD Yönetici',
                    'hi' => 'BD प्रबंधक'
                ];
                break;
            case 'agency_manager':
                $profitPercent = $target?->agency_share ?? 0;
                $role = [
                    'en' => 'Agency Manager',
                    'ar' => 'مدير الوكالة',
                    'tr' => 'Ajans Müdürü',
                    'hi' => 'एजेंसी मैनेजर'
                ];
                break;
            case 'user':
                $app = $target?->app_profit_percentage ?? 0;
                $db  = $target?->db_percentage ?? 0;
                $agency = $target?->agency_share ?? 0;
                $profitPercent = 100 - $app - $db - $agency;
                $role = [
                    'en' => 'User',
                    'ar' => 'المستخدم',
                    'tr' => 'Kullanıcı',
                    'hi' => 'उपयोगकर्ता'
                ];
                break;
            case 'app':
                $profitPercent = $target?->app_profit_percentage ?? 0;
                $role = [
                    'en' => 'App',
                    'ar' => 'التطبيق',
                    'tr' => 'Uygulama',
                    'hi' => 'एप्लिकेशन'
                ];
                break;
            default:
                $profitPercent = 0;
                $role = [
                    'en' => ucfirst($this->type),
                    'ar' => $this->type,
                    'tr' => ucfirst($this->type),
                    'hi' => $this->type
                ];
        }

        $message = [
            'en' => "Diamonds: {$totalDiamonds}, Role: {$role['en']}, Profit %: {$profitPercent}, Broadcast Time: {$broadcastTime}",
            'ar' => "عدد الماسات: {$totalDiamonds}، الدور: {$role['ar']}، نسبة الربح: {$profitPercent}٪، مدة البث: {$broadcastTime}",
            'tr' => "Elmas: {$totalDiamonds}, Rol: {$role['tr']}, Kar %: {$profitPercent}, Yayın Süresi: {$broadcastTime}",
            'hi' => "हीरे: {$totalDiamonds}, भूमिका: {$role['hi']}, लाभ %: {$profitPercent}, प्रसारण समय: {$broadcastTime}"
        ];

        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'type'    => $this->type,
            'message' => $message,
            'title'   => $targetName,
            'date'    => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

}
