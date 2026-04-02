<style>
    .field-description {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        padding: 8px;
        background-color: #f9f9f9;
        border-left: 3px solid #3498db;
        border-radius: 3px;
    }
    .field-tooltip {
        cursor: help;
        color: #3498db;
        font-weight: bold;
        margin-left: 5px;
    }
    .field-tooltip:hover {
        color: #2980b9;
    }
</style>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{{ __('FairLuck Settings') }}</h3>
    </div>
    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
        @csrf
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Global Vault Negative Limit') }}
                    <span class="field-tooltip" title="الحد الأدنى السالب للمحفظة العامة - المبلغ الذي يمكن أن تنخفض إليه المحفظة">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="global_vault_negative_limit" class="form-control"
                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}"
                        placeholder="مثال: 30000"
                        title="الحد الأدنى السالب للمحفظة العامة - المبلغ الذي يمكن أن تنخفض إليه المحفظة">
                    <div class="field-description">
                        <strong>الشرح:</strong> يحدد الحد الأدنى السالب الذي يمكن أن تصل إليه المحفظة العامة. عندما تنخفض المحفظة عن هذا الحد، يتم تفعيل آليات الحماية.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('App Fee Rate') }}
                    <span class="field-tooltip" title="نسبة رسوم التطبيق - أدخل 10 للـ 10%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="fair_luck_app_fee_rate" class="form-control" style="flex: 1;"
                            value="{{ (float)($settings['fair_luck_app_fee_rate'] ?? 0.10) * 100 }}"
                            placeholder="أدخل النسبة: 10"
                            title="نسبة رسوم التطبيق - أدخل 10 للـ 10%"
                            step="0.1">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها التطبيق من كل معاملة. أدخل الرقم كنسبة مئوية (مثلاً: 10 للـ 10%).
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Receiver Fee Rate') }}
                    <span class="field-tooltip" title="نسبة رسوم المستقبل - أدخل 10 للـ 10%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="fair_luck_receiver_fee_rate" class="form-control" style="flex: 1;"
                            value="{{ (float)($settings['fair_luck_receiver_fee_rate'] ?? 0.10) * 100 }}"
                            placeholder="أدخل النسبة: 10"
                            title="نسبة رسوم المستقبل - أدخل 10 للـ 10%"
                            step="0.1" min="0" max="100">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها المستقبل من الهدية. أدخل الرقم كنسبة مئوية (مثلاً: 10 للـ 10%).
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Owner Fee Rate') }}
                    <span class="field-tooltip" title="نسبة رسوم المالك - أدخل 10 للـ 10%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="fair_luck_owner_fee_rate" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['fair_luck_owner_fee_rate']) ? (float)$settings['fair_luck_owner_fee_rate'] * 100 : 10 }}"
                            placeholder="أدخل النسبة: 10"
                            title="نسبة رسوم المالك - أدخل 10 للـ 10%"
                            step="0.1" min="0" max="100">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها مالك النظام من كل معاملة. أدخل الرقم كنسبة مئوية (مثلاً: 10 للـ 10%).
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('V7 RTP & Probability Settings') }}</h4>
            <p style="color: #666; font-size: 13px; margin-bottom: 15px; padding: 10px; background: #f0f8ff; border-left: 3px solid #2196F3;">
                <strong>ملاحظة مهمة:</strong> إعدادات الإصدار السابع (V7) تتحكم في نظام الاحتمالية المتقدم الذي يوازن بين عائد اللاعبين وصحة محفظة النظام. هذه الإعدادات حساسة جداً وتؤثر بشكل مباشر على تجربة اللعبة والأرباح.
            </p>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Target RTP') }}
                    <span class="field-tooltip" title="نسبة العائد للاعب - أدخل 92 للـ 92%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_target_rtp" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_target_rtp']) ? (float)$settings['V7_target_rtp'] * 100 : 92 }}"
                            placeholder="أدخل النسبة: 92"
                            title="نسبة العائد للاعب - أدخل 92 للـ 92%"
                            step="0.1" min="70" max="99">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة العائد النظري للاعب (RTP - Return To Player). تحدد كم نسبة من الرهانات يجب أن تعود للاعبين على المدى الطويل. مثلاً: 92% تعني أن اللاعبين يسترجعون 92 من كل 100 وحدة يراهنونها. القيمة الأعلى تعني عائد أفضل للاعبين لكن أرباح أقل للنظام.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Max Probability Cap') }}
                    <span class="field-tooltip" title="الحد الأقصى للاحتمالية - أدخل 95 للـ 95%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_max_probability_cap" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_max_probability_cap']) ? (float)$settings['V7_max_probability_cap'] * 100 : 95 }}"
                            placeholder="أدخل النسبة: 95"
                            title="الحد الأقصى للاحتمالية - أدخل 95 للـ 95%"
                            step="0.1" min="10" max="95">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى لاحتمالية الفوز. لا يمكن أن تتجاوز احتمالية الفوز هذه النسبة حتى لو كانت ظروف اللعبة تتطلب ذلك.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Min Probability When Low') }}
                    <span class="field-tooltip" title="الحد الأدنى للاحتمالية عند انخفاض المحفظة - أدخل 60 للـ 60%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_min_prob_when_low" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_min_prob_when_low']) ? (float)$settings['V7_min_prob_when_low'] * 100 : 60 }}"
                            placeholder="أدخل النسبة: 60"
                            title="الحد الأدنى للاحتمالية عند انخفاض المحفظة - أدخل 60 للـ 60%"
                            step="0.1" min="30" max="80">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأدنى لاحتمالية الفوز عندما تكون محفظة النظام منخفضة. يضمن أن اللاعبين لديهم فرصة معقولة للفوز حتى عندما تكون المحفظة في حالة حرجة.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Scaling Factors') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Boost Scaling') }}
                    <span class="field-tooltip" title="معامل التعزيز - أدخل 10 للـ 10%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_boost_scaling" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_boost_scaling']) ? (float)$settings['V7_boost_scaling'] * 100 : 10 }}"
                            placeholder="أدخل النسبة: 10"
                            title="معامل التعزيز - أدخل 10 للـ 10%"
                            step="0.1" min="1" max="20">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> معامل التعزيز يحدد كم يتم زيادة احتمالية الفوز عندما تكون ظروف اللعبة مواتية.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Reduce Scaling') }}
                    <span class="field-tooltip" title="معامل التقليل - أدخل 5 للـ 5%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_reduce_scaling" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_reduce_scaling']) ? (float)$settings['V7_reduce_scaling'] * 100 : 5 }}"
                            placeholder="أدخل النسبة: 5"
                            title="معامل التقليل - أدخل 5 للـ 5%"
                            step="0.1" min="1" max="10">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> معامل التقليل يحدد كم يتم تقليل احتمالية الفوز عندما تكون محفظة النظام في حالة حرجة.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Chaos Factor') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Chaos Factor Min') }}
                    <span class="field-tooltip" title="الحد الأدنى لعامل الفوضى - أدخل 50 للـ 50%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_chaos_factor_min" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_chaos_factor_min']) ? (float)$settings['V7_chaos_factor_min'] * 100 : 50 }}"
                            placeholder="أدخل النسبة: 50"
                            title="الحد الأدنى لعامل الفوضى - أدخل 50 للـ 50%"
                            step="0.1" min="50" max="100">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأدنى لعامل الفوضى الذي يضيف عشوائية إلى النتائج لتجنب الأنماط المتوقعة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Chaos Factor Max') }}
                    <span class="field-tooltip" title="الحد الأقصى لعامل الفوضى - أدخل 150 للـ 150%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_chaos_factor_max" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_chaos_factor_max']) ? (float)$settings['V7_chaos_factor_max'] * 100 : 150 }}"
                            placeholder="أدخل النسبة: 150"
                            title="الحد الأقصى لعامل الفوضى - أدخل 150 للـ 150%"
                            step="0.1" min="100" max="150">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى لعامل الفوضى الذي يضيف عشوائية إلى النتائج.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Wallet Distribution') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Global Wallet Distribution') }}
                    <span class="field-tooltip" title="نسبة توزيع المحفظة العامة - أدخل 60 للـ 60%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_wallet_dist_global" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_wallet_dist_global']) ? (float)$settings['V7_wallet_dist_global'] * 100 : 60 }}"
                            placeholder="أدخل النسبة: 60"
                            title="نسبة توزيع المحفظة العامة - أدخل 60 للـ 60%"
                            step="0.1" min="30" max="80">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الأموال المخصصة للمحفظة العامة من إجمالي الأموال المتاحة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Jackpot Wallet Distribution') }}
                    <span class="field-tooltip" title="نسبة توزيع محفظة الجاكبوت - أدخل 25 للـ 25%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_wallet_dist_jackpot" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_wallet_dist_jackpot']) ? (float)$settings['V7_wallet_dist_jackpot'] * 100 : 25 }}"
                            placeholder="أدخل النسبة: 25"
                            title="نسبة توزيع محفظة الجاكبوت - أدخل 25 للـ 25%"
                            step="0.1" min="10" max="40">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الأموال المخصصة لمحفظة الجاكبوت من إجمالي الأموال المتاحة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Medium Wallet Distribution') }}
                    <span class="field-tooltip" title="نسبة توزيع المحفظة المتوسطة - أدخل 15 للـ 15%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_wallet_dist_medium" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_wallet_dist_medium']) ? (float)$settings['V7_wallet_dist_medium'] * 100 : 15 }}"
                            placeholder="أدخل النسبة: 15"
                            title="نسبة توزيع المحفظة المتوسطة - أدخل 15 للـ 15%"
                            step="0.1" min="5" max="30">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> نسبة الأموال المخصصة للمحفظة المتوسطة من إجمالي الأموال المتاحة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Max Single Win Percentage') }}
                    <span class="field-tooltip" title="الحد الأقصى للفوز الواحد - أدخل 15 للـ 15%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_max_single_win_pct" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_max_single_win_pct']) ? (float)$settings['V7_max_single_win_pct'] * 100 : 15 }}"
                            placeholder="أدخل النسبة: 15"
                            title="الحد الأقصى للفوز الواحد - أدخل 15 للـ 15%"
                            step="0.1" min="5" max="50">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى لمبلغ الفوز الواحد كنسبة من رصيد المحفظة. يمنع الفوز الكبير جداً الذي قد يؤثر على استقرار النظام.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Low Balance Settings') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Low Balance Threshold') }}
                    <span class="field-tooltip" title="حد الرصيد المنخفض - عدد الرهانات">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_low_balance_threshold" class="form-control"
                        value="{{ $settings['V7_low_balance_threshold'] ?? 10 }}"
                        placeholder="مثال: 10"
                        title="حد الرصيد المنخفض - عدد الرهانات"
                        min="5" max="50">
                    <div class="field-description">
                        <strong>الشرح:</strong> عدد الرهانات المتبقية التي تعتبر الرصيد منخفضاً. عندما يصل الرصيد إلى هذا الحد، يتم تفعيل آليات الحماية.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Low Balance Min Probability') }}
                    <span class="field-tooltip" title="الحد الأدنى للاحتمالية عند الرصيد المنخفض - أدخل 20 للـ 20%">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" name="V7_low_balance_min_prob" class="form-control" style="flex: 1;"
                            value="{{ isset($settings['V7_low_balance_min_prob']) ? (float)$settings['V7_low_balance_min_prob'] * 100 : 20 }}"
                            placeholder="أدخل النسبة: 20"
                            title="الحد الأدنى للاحتمالية عند الرصيد المنخفض - أدخل 20 للـ 20%"
                            step="0.1" min="5" max="50">
                        <span style="font-weight: bold; color: #666;">%</span>
                    </div>
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأدنى لاحتمالية الفوز عندما يكون الرصيد منخفضاً. يضمن فرصة معقولة للاعبين حتى عند انخفاض الرصيد.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('New Player Settings') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('New Player Bets Threshold') }}
                    <span class="field-tooltip" title="عدد الرهانات لتصنيف اللاعب كجديد">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_new_player_bets" class="form-control"
                        value="{{ $settings['V7_new_player_bets'] ?? 20 }}"
                        placeholder="مثال: 20"
                        title="عدد الرهانات لتصنيف اللاعب كجديد"
                        min="5" max="100">
                    <div class="field-description">
                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب قبل أن يتم اعتباره لاعباً متقدماً. اللاعبون الجدد يحصلون على معاملة خاصة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('New Player Boost') }}
                    <span class="field-tooltip" title="معامل التعزيز للاعبين الجدد">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_new_player_boost" class="form-control"
                        value="{{ $settings['V7_new_player_boost'] ?? 1.5 }}"
                        placeholder="مثال: 1.5"
                        title="معامل التعزيز للاعبين الجدد"
                        step="0.1" min="1.0" max="5.0">
                    <div class="field-description">
                        <strong>الشرح:</strong> معامل التعزيز الذي يتم تطبيقه على احتمالية الفوز للاعبين الجدد. قيمة أعلى تعني فرصة أفضل للفوز.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Multiplier & Bet Gates') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Min Bets for 100x') }}
                    <span class="field-tooltip" title="الحد الأدنى من الرهانات للحصول على مضاعف 100x">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_min_bets_100x" class="form-control"
                        value="{{ $settings['V7_min_bets_100x'] ?? 30 }}"
                        placeholder="مثال: 30"
                        title="الحد الأدنى من الرهانات للحصول على مضاعف 100x"
                        min="10" max="100">
                    <div class="field-description">
                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب قبل أن يكون مؤهلاً للحصول على مضاعف 100x.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Min Bets for 500x') }}
                    <span class="field-tooltip" title="الحد الأدنى من الرهانات للحصول على مضاعف 500x">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_min_bets_500x" class="form-control"
                        value="{{ $settings['V7_min_bets_500x'] ?? 100 }}"
                        placeholder="مثال: 100"
                        title="الحد الأدنى من الرهانات للحصول على مضاعف 500x"
                        min="50" max="500">
                    <div class="field-description">
                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب قبل أن يكون مؤهلاً للحصول على مضاعف 500x.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Wallet Health Multipliers') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Healthy Wallet Max Multiplier') }}
                    <span class="field-tooltip" title="الحد الأقصى للمضاعف عندما تكون المحفظة صحية">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_wallet_healthy_max_mult" class="form-control"
                        value="{{ $settings['V7_wallet_healthy_max_mult'] ?? 1000 }}"
                        placeholder="مثال: 1000"
                        title="الحد الأقصى للمضاعف عندما تكون المحفظة صحية"
                        min="100" max="1000">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى للمضاعف الذي يمكن أن يحصل عليه اللاعب عندما تكون محفظة النظام في حالة صحية.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Moderate Wallet Max Multiplier') }}
                    <span class="field-tooltip" title="الحد الأقصى للمضاعف عندما تكون المحفظة متوسطة">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_wallet_moderate_max_mult" class="form-control"
                        value="{{ $settings['V7_wallet_moderate_max_mult'] ?? 100 }}"
                        placeholder="مثال: 100"
                        title="الحد الأقصى للمضاعف عندما تكون المحفظة متوسطة"
                        min="50" max="500">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى للمضاعف الذي يمكن أن يحصل عليه اللاعب عندما تكون محفظة النظام في حالة متوسطة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Low Wallet Max Multiplier') }}
                    <span class="field-tooltip" title="الحد الأقصى للمضاعف عندما تكون المحفظة منخفضة">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_wallet_low_max_mult" class="form-control"
                        value="{{ $settings['V7_wallet_low_max_mult'] ?? 50 }}"
                        placeholder="مثال: 50"
                        title="الحد الأقصى للمضاعف عندما تكون المحفظة منخفضة"
                        min="10" max="100">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى للمضاعف الذي يمكن أن يحصل عليه اللاعب عندما تكون محفظة النظام منخفضة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Critical Wallet Max Multiplier') }}
                    <span class="field-tooltip" title="الحد الأقصى للمضاعف عندما تكون المحفظة حرجة">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="V7_wallet_critical_max_mult" class="form-control"
                        value="{{ $settings['V7_wallet_critical_max_mult'] ?? 20 }}"
                        placeholder="مثال: 20"
                        title="الحد الأقصى للمضاعف عندما تكون المحفظة حرجة"
                        min="5" max="50">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى للمضاعف الذي يمكن أن يحصل عليه اللاعب عندما تكون محفظة النظام في حالة حرجة.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Wallet Thresholds (USD)') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Healthy Wallet USD') }}
                    <span class="field-tooltip" title="حد المحفظة الصحية بالدولار">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="wallet_healthy_usd" class="form-control"
                        value="{{ $settings['wallet_healthy_usd'] ?? 1000 }}"
                        placeholder="مثال: 1000"
                        title="حد المحفظة الصحية بالدولار"
                        min="100" step="0.01">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأدنى لرصيد المحفظة بالدولار الذي يعتبر صحياً. عندما يكون الرصيد أعلى من هذا الحد، تكون المحفظة في حالة صحية.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Warning Wallet USD') }}
                    <span class="field-tooltip" title="حد تحذير المحفظة بالدولار">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="wallet_warning_usd" class="form-control"
                        value="{{ $settings['wallet_warning_usd'] ?? 500 }}"
                        placeholder="مثال: 500"
                        title="حد تحذير المحفظة بالدولار"
                        min="50" step="0.01">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الذي عندما ينخفض الرصيد عنه، يتم إصدار تحذير. المحفظة في هذه الحالة تحتاج إلى مراقبة.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Critical Wallet USD') }}
                    <span class="field-tooltip" title="حد المحفظة الحرجة بالدولار">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="wallet_critical_usd" class="form-control"
                        value="{{ $settings['wallet_critical_usd'] ?? 200 }}"
                        placeholder="مثال: 200"
                        title="حد المحفظة الحرجة بالدولار"
                        min="0" step="0.01">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الذي عندما ينخفض الرصيد عنه، تكون المحفظة في حالة حرجة وتحتاج إلى تدخل فوري.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Max Negative Wallet USD') }}
                    <span class="field-tooltip" title="الحد الأقصى السالب للمحفظة بالدولار">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="wallet_max_negative_usd" class="form-control"
                        value="{{ $settings['wallet_max_negative_usd'] ?? 300 }}"
                        placeholder="مثال: 300"
                        title="الحد الأقصى السالب للمحفظة بالدولار"
                        min="0" step="0.01">
                    <div class="field-description">
                        <strong>الشرح:</strong> الحد الأقصى الذي يمكن أن تنخفض إليه المحفظة بالدولار. عندما تصل إلى هذا الحد، يتم إيقاف اللعبة.
                    </div>
                </div>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-top: 20px; margin-bottom: 15px; color: #333;">{{ __('Other Settings') }}</h4>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Coin to USD Rate') }}
                    <span class="field-tooltip" title="سعر صرف العملة إلى الدولار">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="coin_to_usd_rate" class="form-control"
                        value="{{ $settings['coin_to_usd_rate'] ?? 0.01 }}"
                        placeholder="مثال: 0.01"
                        title="سعر صرف العملة إلى الدولار"
                        step="0.0001" min="0.0001" max="1.0000">
                    <div class="field-description">
                        <strong>الشرح:</strong> سعر صرف العملة الداخلية إلى الدولار الأمريكي. يستخدم لتحويل الأرصدة والرهانات.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">
                    {{ __('Jackpot Cooldown Bets') }}
                    <span class="field-tooltip" title="عدد الرهانات قبل إمكانية الفوز بالجاكبوت مرة أخرى">ℹ️</span>
                </label>
                <div class="col-sm-8">
                    <input type="number" name="fairluck_jackpot_cooldown_bets" class="form-control"
                        value="{{ $settings['fairluck_jackpot_cooldown_bets'] ?? 100 }}"
                        placeholder="مثال: 100"
                        title="عدد الرهانات قبل إمكانية الفوز بالجاكبوت مرة أخرى"
                        min="0" max="1000">
                    <div class="field-description">
                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب بعد الفوز بالجاكبوت قبل أن يكون مؤهلاً للفوز به مرة أخرى.
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary pull-right">{{ __('Save') }}</button>
        </div>
    </form>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">{{ __('Global Vault Balance History') }}</h3>
            </div>
            <div class="box-body">
                <canvas id="vaultChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var historyData = {!! json_encode($history->map(function ($h) {
    return [
        'date' => $h->created_at ? $h->created_at->format('m-d H:i:s') : '',
        'before' => (int) $h->balance_before,
        'change' => (int) $h->amount,
        'after' => (int) $h->balance_after,
        'desc' => $h->description ?? 'Transaction'
    ];
})->toArray()) !!};

    var labels = historyData.map(function (d) { return d.date; });
    var dataPoints = historyData.map(function (d) { return d.after; });

    var ctx = document.getElementById('vaultChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Global Vault Balance',
                data: dataPoints,
                borderColor: 'rgba(60,141,188,0.8)',
                backgroundColor: 'rgba(60,141,188,0.2)',
                fill: true,
                tension: 0.1,
                pointBackgroundColor: function (context) {
                    var val = dataPoints[context.dataIndex] || 0;
                    return val < 0 ? 'rgba(255,99,132,1)' : 'rgba(60,141,188,1)';
                },
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            maintainAspectRatio: false,
            interaction: {
                mode: 'nearest',
                intersect: false,
            },
            plugins: {
                tooltip: {
                    padding: 10,
                    callbacks: {
                        label: function (context) {
                            var data = historyData[context.dataIndex];
                            var lines = [];
                            lines.push('🎯 Balance After:  ' + data.after.toLocaleString());
                            lines.push('💰 Change Amt:  ' + (data.change > 0 ? '+' : '') + data.change.toLocaleString());
                            lines.push('⏳ Balance Before: ' + data.before.toLocaleString());

                            // Wrap long description across lines if needed, or truncate (assuming descriptions are usually 1 line here)
                            var desc = data.desc.replace('Win payout', '🏆 Win').replace('Loss bet', '💔 Loss').replace('Bet contribution', '💸 Bet');
                            lines.push('📝 Info: ' + desc);

                            if (data.after < 0) {
                                lines.push('⚠️ Status: Wallet is Negative!');
                            }
                            return lines;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>
