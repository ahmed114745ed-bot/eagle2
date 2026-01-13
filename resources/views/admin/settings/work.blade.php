<div id="workSettings" class="settings-section">
    <div class="box-body">
        <div class="inner-settings-menu">
            @php $chargeTabType = request()->get('type', 'Experience'); @endphp
            <button onclick="changeInnerTab('Experience')" class="{{ $chargeTabType == 'Experience' ? 'active' : '' }}">
                {{ __('Experience settings') }}
            </button>

            <button onclick="changeInnerTab('coin')" class="{{ $chargeTabType == 'coin' ? 'active' : '' }}">
                {{ __('coin exchange') }}
            </button>
        </div>

        @php
            $oldExpData = cache('exp_percentages');
            use Modules\Vip\Entities\Vip;
        @endphp

        <div id="Experience_tab" class="inner-tab-content" style="display:{{ $chargeTabType == 'Experience' ? 'block' : 'none' }};">
            <div class="form">
                <label class="d-block">{{ __('Experience settings:') }}</label>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card exp-card-cont p-3 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('wealth') }}</h4>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="wealth_exp">{{ __('wealth') }}</label>
                                            <input type="text" id="wealth_exp" name="exp_sender_percentage"
                                                   value="{{ $oldExpData['exp_sender_percentage'] ?? '' }}"
                                                   placeholder="{{ __('Enter Exp') }}" class="form-control">
                                            <span class="form-text text-muted">1 coin = X EXP</span>
                                        </div>
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
                                <input type="text" name="exp_sender_percentage"
                                       value="{{ $oldExpData['exp_sender_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" required>
                                <small class="text-muted">1 coin = X EXP</small>
                            </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const expInput = document.getElementById('wealth_exp');
                                            const giftPriceInput = document.getElementById('wealth_gift_price');
                                            const resultSpan = document.getElementById('exp_result');

                                            function updateExpResult() {
                                                const expRate = parseFloat(expInput.value);
                                                const giftPrice = parseFloat(giftPriceInput.value);

                                                if (!isNaN(expRate) && !isNaN(giftPrice)) {
                                                    resultSpan.textContent = `= ${expRate * giftPrice} EXP`;
                                                } else {
                                                    resultSpan.textContent = '';
                                                }
                                            }

                                            expInput.addEventListener('input', updateExpResult);
                                            giftPriceInput.addEventListener('input', updateExpResult);
                                            updateExpResult();
                                        });
                                    </script>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.ovip-config') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card p-3 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('attraction') }}</h4>
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
                                <input type="text" name="exp_received_percentage"
                                       value="{{ $oldExpData['exp_received_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" required>
                                <small class="text-muted">1 Diamond = X EXP</small>
                            </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.ovip-config') }}" method="POST">
                            @csrf
                            <div class="card p-3 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('charge') }}</h4>
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
                                <input type="text" name="exp_charge_percentage"
                                       value="{{ $oldExpData['exp_charge_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" required>
                                <small class="text-muted">1 charge = X EXP</small>
                            </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.ovip-config') }}" method="POST">
                            @csrf
                            <div class="card p-3 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('Rooms') }}</h4>
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
                                <input type="text" name="exp_room_percentage"
                                       value="{{ $oldExpData['exp_room_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" required>
                                <small class="text-muted">1 room action = X EXP</small>
                            </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.ovip-config') }}" method="POST">
                            @csrf
                            <div class="card p-3 shadow">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="m-0">{{ __('cp') }}</h4>
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
                                <input type="text" name="exp_cp_percentage"
                                       value="{{ $oldExpData['exp_cp_percentage'] ?? '' }}"
                                       placeholder="{{ __('Enter Exp') }}" class="form-control" required>
                                <small class="text-muted">1 CP action = X EXP</small>
                            </div>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
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

        <div id="coin_tab" class="inner-tab-content" style="display:{{ $chargeTabType == 'coin' ? 'block' : 'none' }};">
            <div class="form">
                <div class="row mt-4">
                    <div class="col-md-6 mb-3" style="margin-top:40px;">
                        <form action="{{ route('admin.exchange-coins') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card p-3 shadow">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="coin_exp">{{ __('diamond') }}</label>
                                        <input type="text" id="coin_exp" name="exchange_coin_percentage"
                                               value="{{ $settings['exchange_coin_percentage'] ?? 1 }}"
                                               placeholder="{{ __('Enter Exp') }}" class="form-control">
                                        <span class="form-text text-muted">1 diamond = X coin</span>
                                    </div>

                                    <div class="col-md-12 coin-calculator mt-3">
                                        <label>{{ __('diamond') }}</label>
                                        <input type="text" class="user_coin_input form-control"
                                               placeholder="Enter value">
                                        <input type="hidden" class="exchange_rate"
                                               value="{{ $settings['exchange_coin_percentage'] ?? 1 }}">
                                        <span class="exp_result fw-bold ms-2"></span>
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
                                <input type="text" id="coin_exp" name="exchange_coin_percentage"
                                       value="{{ $settings['exchange_coin_percentage'] ?? 1 }}"
                                       placeholder="{{ __('Enter rate') }}" class="form-control" required>
                                <small class="text-muted">1 diamond = X coin</small>
                            </div>

                                                userInput.addEventListener('input', calc);
                                                calc();
                                            });
                                        });
                                    </script>

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
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
