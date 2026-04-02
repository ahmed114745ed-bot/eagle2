@php
    $luckyStatus = $config['lucky_gifts_action'] ?? 1;
    $currentVersion = $config['lucky_gift_version'] ?? 1;
@endphp

<style>
    .nav-tabs-custom>.nav-tabs>li.active {
        border-top-color: #3c8dbc;
    }

    /* RTL Support for Horizontal Form Labels */
    body.rtl .form-horizontal .control-label,
    [dir="rtl"] .form-horizontal .control-label {
        text-align: left !important;
    }

    body:not(.rtl):not([dir="rtl"]) .form-horizontal .control-label {
        text-align: right !important;
    }

    /* Ensure vertical alignment is consistent */
    .form-horizontal .control-label {
        padding-top: 7px;
        margin-bottom: 0;
    }
</style>

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab">{{ __('Luck gift Settings') }}</a></li>
        <li><a href="#tab_2" data-toggle="tab">{{ __('lucky gift coins') }}</a></li>
    </ul>
    <div class="tab-content">
        {{-- Tab 1: Luck gift Settings --}}
        <div class="tab-pane active" id="tab_1">

            @if($luckyStatus == 1)
                <div class="box box-solid box-default" style="border: 1px solid #eee; margin-bottom: 20px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-refresh"></i> {{ __('Choose Lucky Gift Version') }}</h3>
                    </div>
                    <div class="box-body">
                        <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-inline">
                            @csrf
                            <div class="radio-group" style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 1 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="1" {{ $currentVersion == 1 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 1 (Standard)') }}</strong>
                                </label>
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 2 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="2" {{ $currentVersion == 2 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 2 (FairLuck)') }}</strong>
                                </label>
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 3 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="3" {{ $currentVersion == 3 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 3 (FairLuck V6)') }}</strong>
                                </label>
                                <label
                                    style="margin: 0; padding: 10px 20px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 10px; {{ $currentVersion == 4 ? 'background: #f4f4f4; border-color: #3c8dbc;' : '' }}">
                                    <input type="radio" name="lucky_gift_version" value="4" {{ $currentVersion == 4 ? 'checked' : '' }} onchange="this.form.submit()" style="margin: 0;">
                                    <strong>{{ __('Version 4 (FairLuck V7)') }}</strong>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Version 1 Content --}}
            @if($currentVersion == 1 || $luckyStatus == 0)
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Luck gift Settings (V1)') }}</h3>
                    </div>
                    <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <h4><i class="icon fa fa-ban"></i> {{ __('Error!') }}</h4>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('application wallet percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="app_wallet_lucky_gift" class="form-control"
                                        value="{{ $config['app_wallet_lucky_gift'] ?? 0 }}" required>
                                    <span class="help-block">{{ __('App owner profit') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('owner percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="owner_lucky_gift" class="form-control"
                                        value="{{ $config['owner_lucky_gift'] ?? 0 }}" required>
                                    <span class="help-block">{{ __('owner gift') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('host percentage') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="any" name="host_lucky_gift" class="form-control"
                                        value="{{ $config['host_lucky_gift'] ?? 0 }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">{{ __('Save V1 Settings') }}</button>
                        </div>
                    </form>
                </div>
            @elseif($currentVersion == 2)
                {{-- Version 2 Content (Matched with fairluck/dashboard.blade.php) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck Settings') }}</h3>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault Negative Limit') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="global_vault_negative_limit" class="form-control"
                                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_app_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_receiver_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_receiver_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Owner Fee Rate (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_owner_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_owner_fee_rate'] ?? '0' }}">
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
            @elseif($currentVersion == 3)
                {{-- Version 3 Content (FairLuck V6 - Same settings as V2) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck V6 Settings (Advanced Protection)') }} / {{ __('إعدادات FairLuck V6 (الحماية المتقدمة)') }}</h3>
                    </div>
                    <form action="{{ admin_url('fairluck/save-settings') }}" method="post" class="form-horizontal">
                        @csrf
                        <div class="box-body">
                          

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault Negative Limit') }} / {{ __('حد الرصيد السالب العام') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="global_vault_negative_limit" class="form-control"
                                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                                    <span class="help-block">{{ __('Maximum allowed negative balance') }} / {{ __('الحد الأقصى للرصيد السالب المسموح به') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Bankruptcy Min Safe Balance') }} / {{ __('الحد الأدنى الآمن لحماية الإفلاس') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="bankruptcy_min_safe_balance" class="form-control"
                                        value="{{ $settings['bankruptcy_min_safe_balance'] ?? 100000 }}">
                                    <span class="help-block">{{ __('Minimum safe balance threshold') }} / {{ __('حد الرصيد الآمن الأدنى') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Bankruptcy Critical Threshold') }} / {{ __('حد الإنذار الحرج للإفلاس') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="bankruptcy_critical_threshold" class="form-control"
                                        value="{{ $settings['bankruptcy_critical_threshold'] ?? 50000 }}">
                                    <span class="help-block">{{ __('Critical alert threshold') }} / {{ __('حد الإنذار الحرج') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Payout Percentage') }} / {{ __('أقصى نسبة دفع') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="bankruptcy_max_payout_percentage" class="form-control"
                                        value="{{ $settings['bankruptcy_max_payout_percentage'] ?? 0.15 }}">
                                    <span class="help-block">{{ __('Maximum payout as % of pool (0.15 = 15%)') }} / {{ __('أقصى دفع كنسبة من المجموعة (0.15 = 15%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Probability Cap') }} / {{ __('الحد الأقصى لسقف الاحتمالية') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="v6_max_probability_cap" class="form-control"
                                        value="{{ $settings['v6_max_probability_cap'] ?? 0.50 }}">
                                    <span class="help-block">{{ __('Hard cap on win probability (0.50 = 50%)') }} / {{ __('حد صارم على احتمالية الفوز (0.50 = 50%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Jackpot Cooldown Bets') }} / {{ __('رهانات فترة الانتظار للجائزة الكبرى') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="fairluck_jackpot_cooldown_bets" class="form-control"
                                        value="{{ $settings['fairluck_jackpot_cooldown_bets'] ?? 200 }}">
                                    <span class="help-block">{{ __('Required bets between big jackpots (250x+)') }} / {{ __('الرهانات المطلوبة بين الجوائز الكبرى (250x+)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('User Data TTL (Days)') }} / {{ __('TTL بيانات المستخدم (أيام)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" name="fairluck_user_data_ttl_days" class="form-control"
                                        value="{{ $settings['fairluck_user_data_ttl_days'] ?? 90 }}">
                                    <span class="help-block">{{ __('Days before user RTP data expires') }} / {{ __('الأيام قبل انتهاء صلاحية بيانات RTP للمستخدم') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Target RTP') }} / {{ __('RTP المستهدف') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="v6_target_rtp" class="form-control"
                                        value="{{ $settings['v6_target_rtp'] ?? 0.85 }}">
                                    <span class="help-block">{{ __('Target Return to Player (0.85 = 85%)') }} / {{ __('العائد المستهدف للاعب (0.85 = 85%)') }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم التطبيق (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_app_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_app_fee_rate'] ?? '0' }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم المستقبل (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_receiver_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_receiver_fee_rate'] ?? '0' }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Owner Fee Rate (0.10 = 10%)') }} / {{ __('معدل رسوم المالك (0.10 = 10%)') }}</label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" name="fair_luck_owner_fee_rate" class="form-control"
                                        value="{{ $settings['fair_luck_owner_fee_rate'] ?? '0' }}">
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success pull-right">{{ __('Save V6 Settings') }} / {{ __('حفظ إعدادات V6') }}</button>
                        </div>
                    </form>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ __('Global Vault Balance History') }} / {{ __('سجل رصيد الخزينة العام') }}</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="vaultChartV6" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($currentVersion == 4)
                {{-- Version 4 Content (FairLuck V7 - User-First Overhaul) --}}
                @php $settings = $fairLuckSettings; @endphp
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('FairLuck V7 Settings (User-First)') }} / {{ __('إعدادات FairLuck V7 (تركيز على المستخدم)') }}</h3>
                        <span class="label label-success pull-right">92% RTP Default</span>
                    </div>
                    <form action="{{ route('admin.lucky-gift.version.update') }}" method="post" class="form-horizontal">
                        @csrf

                         {{-- Section 1: Core RTP Settings --}}
                         <div class="box-header with-border bg-light-blue" style="margin-top: 15px;">
                             <h4 class="box-title">{{ __('Core RTP Settings') }} / {{ __('إعدادات RTP الأساسية') }}</h4>
                             <p style="color: #0066cc; font-size: 12px; margin: 10px 0 0 0;">
                                 <strong>ملاحظة مهمة:</strong> إعدادات الإصدار السابع (V7) تتحكم في نظام الاحتمالية المتقدم الذي يوازن بين عائد اللاعبين وصحة محفظة النظام. هذه الإعدادات حساسة جداً وتؤثر بشكل مباشر على تجربة اللعبة والأرباح.
                             </p>
                         </div>
                         <div class="box-body">
                             <div class="form-group">
                                 <label class="col-sm-3 control-label">{{ __('Target RTP') }} / {{ __('RTP المستهدف') }}</label>
                                 <div class="col-sm-4">
                                     <div class="input-group">
                                         <input type="number" step="0.01" min="70" max="99" name="V7_target_rtp" class="form-control"
                                             value="{{ isset($settings['V7_target_rtp']) ? (float)$settings['V7_target_rtp'] * 100 : 92 }}">
                                         <span class="input-group-addon">%</span>
                                     </div>
                                     <span class="help-block">
                                         <strong>الشرح:</strong> نسبة العائد النظري للاعب (RTP - Return To Player). تحدد كم نسبة من الرهانات يجب أن تعود للاعبين على المدى الطويل. مثلاً: 92% تعني أن اللاعبين يسترجعون 92 من كل 100 وحدة يراهنونها. القيمة الأعلى تعني عائد أفضل للاعبين لكن أرباح أقل للنظام. النطاق: 70-99%. الافتراضي: 92%
                                     </span>
                                 </div>
                             </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Win Probability') }} / {{ __('أقصى احتمالية فوز') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="10" max="95" name="V7_max_probability_cap" class="form-control"
                                            value="{{ isset($settings['V7_max_probability_cap']) ? (float)$settings['V7_max_probability_cap'] * 100 : 50 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى لاحتمالية الفوز. لا يمكن أن تتجاوز احتمالية الفوز هذه النسبة حتى لو كانت ظروف اللعبة تتطلب ذلك. هذا يضمن عدم إعطاء اللاعبين فرصة فوز عالية جداً. الافتراضي: 50%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Boost Scaling Factor') }} / {{ __('معامل زيادة الاحتمالية') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="1" max="20" name="V7_boost_scaling" class="form-control"
                                            value="{{ isset($settings['V7_boost_scaling']) ? (float)$settings['V7_boost_scaling'] * 100 : 5 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> معامل التعزيز يحدد كم يتم زيادة احتمالية الفوز عندما تكون ظروف اللعبة مواتية (مثل عندما تكون محفظة النظام صحية). قيمة أعلى تعني زيادة أكبر في الاحتمالية. الافتراضي: 5%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Reduce Scaling Factor') }} / {{ __('معامل تقليل الاحتمالية') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="1" max="10" name="V7_reduce_scaling" class="form-control"
                                            value="{{ isset($settings['V7_reduce_scaling']) ? (float)$settings['V7_reduce_scaling'] * 100 : 2 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> معامل التقليل يحدد كم يتم تقليل احتمالية الفوز عندما تكون محفظة النظام في حالة حرجة. قيمة أعلى تعني تقليل أكبر في الاحتمالية. الافتراضي: 2%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Chaos Factor Min') }} / {{ __('أدنى معامل عشوائية') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="50" max="100" name="V7_chaos_factor_min" class="form-control"
                                            value="{{ isset($settings['V7_chaos_factor_min']) ? (float)$settings['V7_chaos_factor_min'] * 100 : 90 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأدنى لعامل الفوضى الذي يضيف عشوائية إلى النتائج لتجنب الأنماط المتوقعة. قيمة أعلى تعني عشوائية أقل. الافتراضي: 90%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Chaos Factor Max') }} / {{ __('أقصى معامل عشوائية') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="100" max="150" name="V7_chaos_factor_max" class="form-control"
                                            value="{{ isset($settings['V7_chaos_factor_max']) ? (float)$settings['V7_chaos_factor_max'] * 100 : 110 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى لعامل الفوضى الذي يضيف عشوائية إلى النتائج. قيمة أعلى تعني عشوائية أكبر. الافتراضي: 110%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Multiplier Weights --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('Multiplier Weights') }} / {{ __('أوزان المضاعفات') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-info">
                                {{ __('Higher weight = more frequent wins. Flattened distribution means big wins happen as often as small wins.') }}
                            </div>

                            <div class="row">
                                @php
                                    $multipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
                                    $defaultWeights = [500, 500, 500, 500, 500, 500, 400, 300, 200];
                                    $storedWeights = $settings['V7_multiplier_weights'] ?? [];
                                    if (is_string($storedWeights)) {
                                        $storedWeights = json_decode($storedWeights, true) ?? [];
                                    }
                                @endphp
                                @foreach($multipliers as $index => $m)
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ $m }}x</label>
                                        <div class="col-sm-8">
                                            <input type="number" min="1" max="5000" name="V7_multiplier_weights[{{ $m }}]" class="form-control weight-input"
                                                data-multiplier="{{ $m }}"
                                                value="{{ $storedWeights[$m] ?? $defaultWeights[$index] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                           
                        </div>

                        {{-- Section 3: New Player Settings --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('New Player Settings') }} / {{ __('إعدادات اللاعب الجديد') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('New Player Boost Bets') }} / {{ __('رهانات تعزيز اللاعب الجديد') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="5" max="100" name="V7_new_player_bets" class="form-control"
                                        value="{{ $settings['V7_new_player_bets'] ?? 20 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب الجديد قبل أن يتم اعتباره لاعباً متقدماً. اللاعبون الجدد يحصلون على معاملة خاصة وتعزيز في احتمالية الفوز. الافتراضي: 20
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('New Player Boost Multiplier') }} / {{ __('معامل تعزيز اللاعب الجديد') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" step="0.1" min="1.0" max="5.0" name="V7_new_player_boost" class="form-control"
                                        value="{{ $settings['V7_new_player_boost'] ?? 3.0 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> معامل التعزيز الذي يتم تطبيقه على احتمالية الفوز للاعبين الجدد. قيمة 3.0 تعني أن احتمالية الفوز تُضرب في 3 للاعبين الجدد. قيمة أعلى تعني فرصة أفضل للفوز. الافتراضي: 3.0x
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Low Balance Threshold') }} / {{ __('حد الرصيد المنخفض') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="5" max="50" name="V7_low_balance_threshold" class="form-control"
                                        value="{{ $settings['V7_low_balance_threshold'] ?? 15 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الرصيد إلى الرهان. إذا كان (رصيد اللاعب / قيمة الرهان) أقل من هذا الحد، يتم تفعيل آليات الحماية وزيادة احتمالية الفوز. مثلاً: إذا كان الحد 15 ورصيد اللاعب 150 وقيمة الرهان 10، فإن النسبة = 150/10 = 15 (عند الحد بالضبط). الافتراضي: 15
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Low Balance Min Probability') }} / {{ __('أدنى احتمالية عند رصيد منخفض') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="5" max="50" name="V7_low_balance_min_prob" class="form-control"
                                            value="{{ isset($settings['V7_low_balance_min_prob']) ? (float)$settings['V7_low_balance_min_prob'] * 100 : 18 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأدنى لاحتمالية الفوز عندما يكون رصيد اللاعب منخفضاً. يضمن أن اللاعبين لديهم فرصة معقولة للفوز حتى عند انخفاض الرصيد. الافتراضي: 18%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: Wallet Protection (USD) --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('Wallet Protection (USD)') }} / {{ __('حماية المحفظة (بالدولار)') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-warning">
                                <strong>{{ __('Important') }}:</strong> {{ __('All thresholds are now in USD. Set the Coin-to-USD rate below.') }}
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Coin to USD Rate') }} / {{ __('سعر صرف العملة إلى الدولار') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" step="0.0001" min="0.0001" max="1.0000" name="coin_to_usd_rate" class="form-control"
                                        value="{{ $settings['coin_to_usd_rate'] ?? 0.01 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> سعر صرف العملة الداخلية إلى الدولار الأمريكي. يستخدم لتحويل الأرصدة والرهانات من العملة الداخلية إلى الدولار. مثال: 0.01 تعني أن 1 عملة = 0.01 دولار. الافتراضي: 0.01
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Healthy Wallet (USD)') }} / {{ __('المحفظة الصحية (بالدولار)') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" step="100" min="100" name="wallet_healthy_usd" class="form-control"
                                            value="{{ $settings['wallet_healthy_usd'] ?? 1000 }}">
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأدنى لرصيد المحفظة بالدولار الذي يعتبر صحياً. عندما يكون الرصيد أعلى من هذا الحد، تكون المحفظة في حالة صحية ويمكن السماح بمضاعفات أعلى. الافتراضي: $1,000
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Warning Wallet (USD)') }} / {{ __('محفظة التحذير (بالدولار)') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" step="100" min="50" name="wallet_warning_usd" class="form-control"
                                            value="{{ $settings['wallet_warning_usd'] ?? 500 }}">
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الذي عندما ينخفض الرصيد عنه، يتم إصدار تحذير. المحفظة في هذه الحالة تحتاج إلى مراقبة وقد يتم تقليل المضاعفات المسموحة. الافتراضي: $500
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Critical Wallet (USD)') }} / {{ __('محفظة حرجة (بالدولار)') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" step="100" min="0" name="wallet_critical_usd" class="form-control"
                                            value="{{ $settings['wallet_critical_usd'] ?? 200 }}">
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الذي عندما ينخفض الرصيد عنه، تكون المحفظة في حالة حرجة وتحتاج إلى تدخل فوري. يتم تقليل المضاعفات بشكل كبير وتفعيل آليات الحماية. الافتراضي: $200
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Negative Wallet (USD)') }} / {{ __('أقصى محفظة سالبة (بالدولار)') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" step="100" min="0" name="wallet_max_negative_usd" class="form-control"
                                            value="{{ $settings['wallet_max_negative_usd'] ?? 300 }}">
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى الذي يمكن أن تنخفض إليه المحفظة بالدولار (خط الائتمان). عندما تصل المحفظة إلى هذا الحد السالب، يتم إيقاف اللعبة. الافتراضي: $300
                                    </span>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Wallet Healthy Max Multiplier') }} / {{ __('أقصى مضاعف للمحفظة الصحية') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="100" max="1000" name="V7_wallet_healthy_max_mult" class="form-control"
                                        value="{{ $settings['V7_wallet_healthy_max_mult'] ?? 1000 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى للمضاعف الذي يمكن أن يحصل عليه اللاعب عندما تكون محفظة النظام في حالة صحية (أعلى من الحد الصحي). الافتراضي: 1000x
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Wallet Moderate Max Mult') }} / {{ __('أقصى مضاعف للمحفظة المتوسطة') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="50" max="500" name="V7_wallet_moderate_max_mult" class="form-control"
                                        value="{{ $settings['V7_wallet_moderate_max_mult'] ?? 100 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى للمضاعف عندما تكون محفظة النظام في حالة متوسطة (بين التحذير والصحي). الافتراضي: 100x
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Wallet Low Max Mult') }} / {{ __('أقصى مضاعف للمحفظة المنخفضة') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="10" max="100" name="V7_wallet_low_max_mult" class="form-control"
                                        value="{{ $settings['V7_wallet_low_max_mult'] ?? 50 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى للمضاعف عندما تكون محفظة النظام منخفضة (بين التحذير والحرج). الافتراضي: 50x
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Wallet Critical Max Mult') }} / {{ __('أقصى مضاعف للمحفظة الحرجة') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="5" max="50" name="V7_wallet_critical_max_mult" class="form-control"
                                        value="{{ $settings['V7_wallet_critical_max_mult'] ?? 20 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى للمضاعف عندما تكون محفظة النظام في حالة حرجة (أقل من الحد الحرج). الافتراضي: 20x
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Min Probability When Low') }} / {{ __('أدنى احتمالية عند انخفاض المحفظة') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="30" max="80" name="V7_min_prob_when_low" class="form-control"
                                            value="{{ isset($settings['V7_min_prob_when_low']) ? (float)$settings['V7_min_prob_when_low'] * 100 : 60 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأدنى لاحتمالية الفوز عندما تكون محفظة النظام منخفضة. يضمن أن اللاعبين لديهم فرصة معقولة للفوز حتى عند انخفاض المحفظة. الافتراضي: 60%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: Cooldown & Safety --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('Cooldown & Safety') }} / {{ __('فترة الانتظار والسلامة') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-info">
                                <strong>{{ __('Important') }}:</strong> {{ __('Cooldown is DISABLED by default (0 = disabled). Users can win big back-to-back.') }}
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Jackpot Cooldown (bets)') }} / {{ __('فترة انتظار الجاكبوت (رهانات)') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" max="1000" name="fairluck_jackpot_cooldown_bets" class="form-control"
                                        value="{{ $settings['fairluck_jackpot_cooldown_bets'] ?? 0 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب بعد الفوز بالجاكبوت (مضاعف 250x أو أعلى) قبل أن يكون مؤهلاً للفوز به مرة أخرى. 0 = معطل (لا توجد فترة انتظار). الافتراضي: 0 (معطل)
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Min Bets for 100x+') }} / {{ __('الحد الأدنى من الرهانات لـ 100x+') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="10" max="100" name="V7_min_bets_100x" class="form-control"
                                        value="{{ $settings['V7_min_bets_100x'] ?? 30 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب قبل أن يكون مؤهلاً للحصول على مضاعف 100x أو أعلى. يضمن أن اللاعبين الجدد لا يحصلون على مضاعفات عالية جداً في البداية. الافتراضي: 30
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Min Bets for 500x+') }} / {{ __('الحد الأدنى من الرهانات لـ 500x+') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" min="50" max="500" name="V7_min_bets_500x" class="form-control"
                                        value="{{ $settings['V7_min_bets_500x'] ?? 100 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> عدد الرهانات التي يجب أن يقوم بها اللاعب قبل أن يكون مؤهلاً للحصول على مضاعف 500x أو أعلى. هذا حد أعلى من 100x لضمان أن المضاعفات الكبيرة جداً تُعطى فقط للاعبين ذوي الخبرة. الافتراضي: 100
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Max Single Win (% of wallet)') }} / {{ __('أقصى فوز واحد (% من المحفظة)') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="5" max="50" name="V7_max_single_win_pct" class="form-control"
                                            value="{{ isset($settings['V7_max_single_win_pct']) ? (float)$settings['V7_max_single_win_pct'] * 100 : 15 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى لمبلغ الفوز الواحد كنسبة من رصيد المحفظة. يمنع الفوز الكبير جداً الذي قد يؤثر على استقرار النظام. مثلاً: إذا كانت المحفظة 10,000 دولار و15%، فإن أقصى فوز واحد = 1,500 دولار. النطاق: 5-50%. الافتراضي: 15%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 6: Wallet Distribution --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('Wallet Distribution') }} / {{ __('توزيع المحفظة') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-info">
                                <strong>{{ __('Note') }}:</strong> {{ __('These percentages should add up to 100%. They determine how the system allocates funds across different wallet pools.') }}
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault %') }} / {{ __('نسبة المحفظة العامة') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="30" max="80" name="V7_wallet_dist_global" class="form-control"
                                            value="{{ isset($settings['V7_wallet_dist_global']) ? (float)$settings['V7_wallet_dist_global'] * 100 : 65 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الأموال المخصصة للمحفظة العامة من إجمالي الأموال المتاحة. هذه المحفظة تستخدم لدفع الفوز العادي والمتوسط. قيمة أعلى تعني أموال أكثر متاحة للفوز العادي. الافتراضي: 65%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Jackpot Wallet %') }} / {{ __('نسبة محفظة الجاكبوت') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="10" max="40" name="V7_wallet_dist_jackpot" class="form-control"
                                            value="{{ isset($settings['V7_wallet_dist_jackpot']) ? (float)$settings['V7_wallet_dist_jackpot'] * 100 : 20 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الأموال المخصصة لمحفظة الجاكبوت من إجمالي الأموال المتاحة. هذه المحفظة تستخدم لدفع الفوز الكبير جداً (مضاعفات عالية جداً). قيمة أعلى تعني جوائز جاكبوت أكبر. الافتراضي: 20%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Medium Wallet %') }} / {{ __('نسبة المحفظة المتوسطة') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="5" max="30" name="V7_wallet_dist_medium" class="form-control"
                                            value="{{ isset($settings['V7_wallet_dist_medium']) ? (float)$settings['V7_wallet_dist_medium'] * 100 : 15 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الأموال المخصصة للمحفظة المتوسطة من إجمالي الأموال المتاحة. هذه المحفظة تستخدم لدفع الفوز المتوسط (مضاعفات متوسطة). قيمة أعلى تعني فوز متوسط أكثر. الافتراضي: 15%
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Section 7: Fee Settings --}}
                        <div class="box-header with-border bg-light-blue">
                            <h4 class="box-title">{{ __('Fee Settings') }} / {{ __('إعدادات الرسوم') }}</h4>
                        </div>
                        <div class="box-body">
                            <div class="alert alert-warning">
                                <strong>{{ __('Important') }}:</strong> {{ __('Fees are deducted from the winning amount. Higher fees = lower payouts to users.') }}
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('App Fee Rate') }} / {{ __('نسبة رسوم التطبيق') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="fair_luck_app_fee_rate" class="form-control"
                                            value="{{ isset($settings['fair_luck_app_fee_rate']) ? (float)$settings['fair_luck_app_fee_rate'] * 100 : 10 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها التطبيق من كل معاملة فوز. هذه الرسوم تذهب إلى مالك التطبيق. مثلاً: إذا كان الفوز 100 دولار ورسوم التطبيق 10%، فإن التطبيق يأخذ 10 دولار. الافتراضي: 10%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Receiver Fee Rate') }} / {{ __('نسبة رسوم المستقبل') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="fair_luck_receiver_fee_rate" class="form-control"
                                            value="{{ isset($settings['fair_luck_receiver_fee_rate']) ? (float)$settings['fair_luck_receiver_fee_rate'] * 100 : 10 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها المستقبل (الشخص الذي يتلقى الهدية) من الفوز. هذه الرسوم تذهب إلى المستقبل كحافز. مثلاً: إذا كان الفوز 100 دولار ورسوم المستقبل 10%، فإن المستقبل يأخذ 10 دولار إضافية. الافتراضي: 10%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Owner Fee Rate') }} / {{ __('نسبة رسوم مالك الغرفة') }}</label>
                                <div class="col-sm-4">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" name="fair_luck_owner_fee_rate" class="form-control"
                                            value="{{ isset($settings['fair_luck_owner_fee_rate']) ? (float)$settings['fair_luck_owner_fee_rate'] * 100 : 10 }}">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <span class="help-block">
                                        <strong>الشرح:</strong> نسبة الرسوم التي يأخذها مالك الغرفة من كل معاملة فوز. هذه الرسوم تذهب إلى مالك الغرفة كحافز على استضافة اللعبة. مثلاً: إذا كان الفوز 100 دولار ورسوم مالك الغرفة 10%، فإن مالك الغرفة يأخذ 10 دولار. الافتراضي: 10%
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ __('Global Vault Negative Limit (coins - legacy)') }} / {{ __('حد الرصيد السالب العام (عملات - قديم)') }}</label>
                                <div class="col-sm-4">
                                    <input type="number" name="global_vault_negative_limit" class="form-control"
                                        value="{{ $settings['global_vault_negative_limit'] ?? 30000 }}">
                                    <span class="help-block">
                                        <strong>الشرح:</strong> الحد الأقصى الذي يمكن أن تنخفض إليه المحفظة بالعملات (إذا لم يتم تعيين سعر صرف الدولار). هذا إعداد قديم ويُستخدم فقط للتوافقية مع الإصدارات السابقة. الافتراضي: 30000
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-success btn-lg pull-right">{{ __('Save All V7 Settings') }} / {{ __('حفظ جميع إعدادات V7') }}</button>
                        </div>
                    </form>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">{{ __('Global Vault Balance History') }} / {{ __('سجل رصيد الخزينة العام') }}</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="vaultChartV7" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tab 2: lucky gift coins --}}
        <div class="tab-pane" id="tab_2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ __('lucky gift coins') }}</h3>
                </div>
                <form action="{{ route('admin.lucky-gift.version.update') }}" method="POST" class="form-horizontal">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ __('lucky gift coins') }}</label>
                            <div class="col-sm-8">
                                <input type="number" name="lucky_gift_coins" class="form-control"
                                         value="{{ $config['lucky_gift_coins'] ?? 0 }}"
                                    required>
                                <span
                                    class="help-block">{{ __('Play coin sound inside the room when the win amount is greater than or equal to the added value.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-warning pull-right">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($luckyStatus == 1 && isset($history))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            // Live Preview Bar Chart for Multiplier Weights (V7 only)
            @if($currentVersion == 4)
            var weightChart = null;
            function updateWeightChart() {
                var labels = [];
                var data = [];
                var totalWeight = 0;
                var colors = [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                    'rgba(142, 68, 173, 0.8)',
                    'rgba(39, 174, 96, 0.8)'
                ];

                $('.weight-input').each(function() {
                    var val = parseInt($(this).val()) || 0;
                    totalWeight += val;
                });

                $('.weight-input').each(function() {
                    var mult = $(this).data('multiplier');
                    var val = parseInt($(this).val()) || 0;
                    var pct = totalWeight > 0 ? ((val / totalWeight) * 100).toFixed(1) : 0;
                    labels.push(mult + 'x (' + pct + '%)');
                    data.push(val);
                });

                var canvas = document.getElementById('weightPreviewChart');
                if (!canvas) return;

                if (weightChart) {
                    weightChart.destroy();
                }

                weightChart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Weight',
                            data: data,
                            backgroundColor: colors,
                            borderColor: colors.map(function(c) { return c.replace('0.8', '1'); }),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                        var pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                        return 'Weight: ' + context.raw + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Weight' } }
                        }
                    }
                });
            }

            // Initialize chart and update on input change
            updateWeightChart();
            $(document).on('input change', '.weight-input', function() {
                updateWeightChart();
            });
            @endif

            var historyData = {!! json_encode($history->map(function ($h) {
            return [
                'date' => $h->created_at ? $h->created_at->format('m-d H:i:s') : '',
                'before' => (int) $h->balance_before,
                'change' => (int) $h->amount,
                'after' => (int) $h->balance_after,
                'desc' => $h->description ?? __('Transaction')
            ];
        })->toArray()) !!};

            var labels = historyData.map(function (d) { return d.date; });
            var dataPoints = historyData.map(function (d) { return d.after; });

            var chartId = '{{ $currentVersion == 3 ? 'vaultChartV6' : ($currentVersion == 4 ? 'vaultChartV7' : 'vaultChart') }}';
            var ctx = document.getElementById(chartId).getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "{{ __('Global Vault Balance') }} / {{ __('رصيد الخزينة العام') }}",
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
                                    lines.push("{{ __('🎯 Balance After:  ') }}" + data.after.toLocaleString());
                                    lines.push("{{ __('💰 Change Amt:  ') }}" + (data.change > 0 ? '+' : '') + data.change.toLocaleString());
                                    lines.push("{{ __('⏳ Balance Before: ') }}" + data.before.toLocaleString());

                                    var desc = data.desc;
                                    desc = desc.replace('Win payout', "{{ __('🏆 Win') }}")
                                        .replace('Loss bet', "{{ __('💔 Loss') }}")
                                        .replace('Bet contribution', "{{ __('💸 Bet') }}");

                                    lines.push("{{ __('📝 Info: ') }}" + desc);

                                    if (data.after < 0) {
                                        lines.push("{{ __('⚠️ Status: Wallet is Negative!') }}");
                                    }
                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });
        });
    </script>
@endif
