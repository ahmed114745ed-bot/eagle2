<?php

namespace Modules\Vip\Services;

use App\Models\VipPrivilege;
use App\Models\Ware;
use Encore\Admin\Form;

class VipService
{
    public function handleSaving(Form $form): void
    {
        $privilegs = request('privilegs', []);
        $level = request('level');

        Ware::where('level', $level)->update([
            'is_active_for_vip' => false
        ]);

        $types = [];

        foreach ($privilegs as $privilegId) {
            $type_preveleg = VipPrivilege::find($privilegId);

            if (!$type_preveleg) {
                continue;
            }

            $type = $type_preveleg->type;

            if (in_array($type, $types)) {
                admin_error('خـطأ', 'لا يمكن اختيار أكثر من امتياز من نفس النوع: ' . $type);
                return;
            }

            $types[] = $type;

            Ware::where('type', $type)->where('level', $level)->update([
                'is_active_for_vip' => true
            ]);
        }

        session()->forget('show_alert_vip');
    }
}
