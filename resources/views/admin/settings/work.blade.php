<div id="workSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-briefcase"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Work Settings') }}</h3>
            <p>{{ __('Configure experience, charge and work-related settings') }}</p>
        </div>
    </div>
    <div class="box-body">
        <!-- Improved Tab Navigation -->
        <div class="inner-settings-menu mb-4">
            @php $chargeTabType = request()->get('type', 'Experience'); @endphp
            <button onclick="changeInnerTab('Experience')"
                    class="tab-btn {{ $chargeTabType == 'Experience' ? 'active' : '' }}">
                <i class="fas fa-star"></i>
                {{ __('Experience settings') }}
            </button>
            <button onclick="changeInnerTab('coin')"
                    class="tab-btn {{ $chargeTabType == 'coin' ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                {{ __('coin exchange') }}
            </button>
        </div>

        @php
            $oldExpData = cache('exp_percentages');
            use Modules\Vip\Entities\Vip;
        @endphp

            <!-- Experience Tab -->
        <div id="Experience_tab" class="inner-tab-content"
             style="display:{{ $chargeTabType == 'Experience' ? 'block' : 'none' }};">

            <div class="section-header mb-4">
                <h4>{{ __('Experience settings:') }}</h4>
                <p class="text-muted">{{ __('Configure EXP rates for different activities') }}</p>
            </div>

            <div class="exp-cards-grid">
                <!-- Wealth Card -->
                <div class="exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon wealth">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h5>{{ __('wealth') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('EXP Rate') }}</label>
                                <input type="number" id="wealth_exp" name="exp_sender_percentage"
                                       value="{{ $oldExpData['exp_sender_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 coin = X EXP</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('gift price') }}</label>
                                <div class="input-with-result">
                                    <input type="number" id="wealth_gift_price" name="wealth_gift_price" step="any"
                                           value="{{ $settings['wealth_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control">
                                    <span id="wealth_exp_result" class="result-badge"></span>
                                </div>
                            </div>

                            @php $sender = Vip::where('type',2)->count(); @endphp
                            @if ($sender == 0)
                                <div class="alert-link">
                                    <a href="/admin/vips"><i class="fas fa-cog"></i> {{ __('Go to Settings') }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Attraction Card -->
                <div class="exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon attraction">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h5>{{ __('attraction') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('EXP Rate') }}</label>
                                <input type="number" id="attraction_exp" name="exp_received_percentage"
                                       value="{{ $oldExpData['exp_received_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 Diamond = X EXP</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('gift price') }}</label>
                                <div class="input-with-result">
                                    <input type="number" id="attraction_gift_price" name="attraction_gift_price" step="any"
                                           value="{{ $settings['attraction_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control">
                                    <span id="attraction_exp_result" class="result-badge"></span>
                                </div>
                            </div>

                            @php $receiver = Vip::where('type',1)->count(); @endphp
                            @if ($receiver == 0)
                                <div class="alert-link">
                                    <a href="/admin/vips"><i class="fas fa-cog"></i> {{ __('Go to Settings') }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Charge Card -->
                <div class="exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon charge">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h5>{{ __('charge') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('EXP Rate') }}</label>
                                <input type="number" id="charge_exp" name="exp_charge_percentage"
                                       value="{{ $oldExpData['exp_charge_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 charge = X EXP</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('coins') }}</label>
                                <div class="input-with-result">
                                    <input type="number" id="charge_gift_price" name="charge_gift_price" step="any"
                                           value="{{ $settings['charge_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter amount') }}" class="form-control">
                                    <span id="charge_exp_result" class="result-badge"></span>
                                </div>
                            </div>

                            @php $charger = Vip::where('type',5)->count(); @endphp
                            @if ($charger == 0)
                                <div class="alert-link">
                                    <a href="/admin/vips"><i class="fas fa-cog"></i> {{ __('Go to Settings') }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Rooms Card -->
                <div class="exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon rooms">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <h5>{{ __('Rooms') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('EXP Rate') }}</label>
                                <input type="number" id="rooms_exp" name="exp_room_percentage"
                                       value="{{ $oldExpData['exp_room_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 room action = X EXP</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('gift price') }}</label>
                                <div class="input-with-result">
                                    <input type="number" id="rooms_gift_price" name="rooms_gift_price" step="any"
                                           value="{{ $settings['rooms_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control">
                                    <span id="rooms_exp_result" class="result-badge"></span>
                                </div>
                            </div>

                            @php $rooms = Vip::where('type',4)->count(); @endphp
                            @if ($rooms == 0)
                                <div class="alert-link">
                                    <a href="/admin/vips"><i class="fas fa-cog"></i> {{ __('Go to Settings') }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- CP Card -->
                <div class="exp-card">
                    <form action="{{ route('admin.ovip-config') }}" method="POST" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon cp">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5>{{ __('cp') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('EXP Rate') }}</label>
                                <input type="number" id="cp_exp" name="exp_cp_percentage"
                                       value="{{ $oldExpData['exp_cp_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 CP action = X EXP</small>
                            </div>

                            <div class="form-group">
                                <label>{{ __('gift price') }}</label>
                                <div class="input-with-result">
                                    <input type="number" id="cp_gift_price" name="cp_gift_price" step="any"
                                           value="{{ $settings['cp_gift_price'] ?? '' }}"
                                           placeholder="{{ __('Enter price') }}" class="form-control">
                                    <span id="cp_exp_result" class="result-badge"></span>
                                </div>
                            </div>

                            @php $cp = Vip::where('type',3)->count(); @endphp
                            @if ($cp == 0)
                                <div class="alert-link">
                                    <a href="/admin/vips"><i class="fas fa-cog"></i> {{ __('Go to Settings') }}</a>
                                </div>
                            @endif
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coin Exchange Tab -->
        <div id="coin_tab" class="inner-tab-content"
             style="display:{{ $chargeTabType == 'coin' ? 'block' : 'none' }};">

            <div class="section-header mb-4">
                <h4>{{ __('coin exchange') }}</h4>
                <p class="text-muted">{{ __('Configure diamond to coin exchange rate') }}</p>
            </div>

            <div class="exp-cards-grid single-card">
                <div class="exp-card">
                    <form action="{{ route('admin.exchange-coins') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                        @csrf
                        <div class="exp-card-header">
                            <div class="exp-card-icon diamond">
                                <i class="fas fa-diamond"></i>
                            </div>
                            <h5>{{ __('Diamond Exchange') }}</h5>
                        </div>

                        <div class="exp-card-body">
                            <div class="form-group">
                                <label>{{ __('Exchange Rate') }}</label>
                                <input type="number" id="coin_exp" name="exchange_coin_percentage"
                                       value="{{ $settings['exchange_coin_percentage'] ?? 1 }}"
                                       placeholder="{{ __('Enter rate') }}" class="form-control" step="any" required>
                                <small class="text-muted">1 diamond = X coin</small>
                            </div>

                            <div class="form-group calculator-box">
                                <label>{{ __('Calculator') }}</label>
                                <div class="input-with-result">
                                    <input type="number" class="user_coin_input form-control" step="any"
                                           placeholder="{{ __('Enter diamond amount') }}">
                                    <input type="hidden" class="exchange_rate"
                                           value="{{ $settings['exchange_coin_percentage'] ?? 1 }}">
                                    <span class="exp_result result-badge"></span>
                                </div>
                            </div>
                        </div>

                        <div class="exp-card-footer">
                            <button type="submit" class="btn btn-primary btn-block btn-save">
                                <i class="fas fa-save"></i> {{ __('Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
