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

        <div id="Experience_tab" class="inner-tab-content" style="display:none;">
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

                                    @php $sender = Vip::where('type',2)->count(); @endphp
                                    @if ($sender == 0)
                                        <div class="col-md-12"><a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                        </div>
                                    @endif

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="wealth_gift_price">{{ __('gift price') }}</label>
                                            <input type="text" id="wealth_gift_price" name="test_calco"
                                                   value="{{ $settings['wealth_gift_price'] ?? '' }}"
                                                   placeholder="{{ __('wealth_gift_price') }}" class="form-control">
                                            <span id="exp_result" class="fw-bold ms-2"></span>
                                        </div>
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

                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="attraction_exp">{{ __('attraction') }}</label>
                                        <input type="text" id="attraction_exp" name="exp_received_percentage"
                                               value="{{ $oldExpData['exp_received_percentage'] ?? '' }}"
                                               placeholder="{{ __('Enter Exp') }}" class="form-control">
                                        <span class="form-text text-muted">1 Diamond = X EXP</span>
                                    </div>

                                    <div class="col-md-12">
                                        <label>{{ __('gift price') }}</label>
                                        <input type="text" id="attraction_gift_price" name="test_calco"
                                               value="{{ $settings['attraction_gift_price'] ?? '' }}"
                                               placeholder="{{ __('attraction_gift_price') }}" class="form-control">
                                        <span id="attraction_exp_result" class="fw-bold ms-2"></span>
                                    </div>

                                    @php $receiver = Vip::where('type',1)->count(); @endphp
                                    @if ($receiver == 0)
                                        <div class="col-md-12"><a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                        </div>
                                    @endif

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

                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="charge_exp">{{ __('charge') }}</label>
                                        <input type="text" id="charge_exp" name="exp_charge_percentage"
                                               value="{{ $oldExpData['exp_charge_percentage'] ?? '' }}"
                                               placeholder="{{ __('Enter Exp') }}" class="form-control">
                                    </div>

                                    <div class="col-md-12">
                                        <label for="charge_gift_price">{{ __('coins') }}</label>
                                        <input type="text" id="charge_gift_price" name="test_calco"
                                               value="{{ $settings['charge_gift_price'] ?? '' }}"
                                               placeholder="{{ __('charge_gift_price') }}" class="form-control">
                                        <span id="charge_exp_result" class="fw-bold ms-2"></span>
                                    </div>

                                    @php $charger = Vip::where('type',5)->count(); @endphp
                                    @if ($charger == 0)
                                        <div class="col-md-12">
                                            <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                        </div>
                                    @endif

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

                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="rooms_exp">{{ __('Rooms') }}</label>
                                        <input type="text" id="rooms_exp" name="exp_room_percentage"
                                               value="{{ $oldExpData['exp_room_percentage'] ?? '' }}"
                                               placeholder="{{ __('Enter Exp') }}" class="form-control">
                                    </div>

                                    <div class="col-md-12">
                                        <label>{{ __('gift price') }}</label>
                                        <input type="text" id="rooms_gift_price" name="test_calco"
                                               value="{{ $settings['rooms_gift_price'] ?? '' }}"
                                               placeholder="{{ __('rooms_gift_price') }}" class="form-control">
                                        <span id="rooms_exp_result" class="fw-bold ms-2"></span>
                                    </div>

                                    @php $rooms = Vip::where('type',4)->count(); @endphp
                                    @if ($rooms == 0)
                                        <div class="col-md-12"><a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                        </div>
                                    @endif

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

                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="cp_exp">{{ __('cp') }}</label>
                                        <input type="text" id="cp_exp" name="exp_cp_percentage"
                                               value="{{ $oldExpData['exp_cp_percentage'] ?? '' }}"
                                               placeholder="{{ __('Enter Exp') }}" class="form-control">
                                    </div>

                                    <div class="col-md-12">
                                        <label>{{ __('gift price') }}</label>
                                        <input type="text" id="cp_gift_price" name="test_calco"
                                               value="{{ $settings['cp_gift_price'] ?? '' }}"
                                               placeholder="cp_gift_price" class="form-control">
                                        <span id="cp_exp_result" class="fw-bold ms-2"></span>
                                    </div>

                                    @php $cp = Vip::where('type',3)->count(); @endphp
                                    @if ($cp == 0)
                                        <div class="col-md-12">
                                            <a href="/admin/vips">{{ __('Go to Settings') }}</a>
                                        </div>
                                    @endif

                                    <div class="col-12 d-flex gap-3 mt-3">
                                        <button class="btn btn-primary">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="coin_tab" class="inner-tab-content" style="display:none;">
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

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            document.querySelectorAll('.coin-calculator').forEach(container => {
                                                const userInput = container.querySelector('.user_coin_input');
                                                const rate = container.querySelector('.exchange_rate');
                                                const resultSpan = container.querySelector('.exp_result');

                                                function calc() {
                                                    const val = parseFloat(userInput.value);
                                                    const rateX = parseFloat(rate.value);
                                                    if (!isNaN(val) && !isNaN(rateX)) {
                                                        resultSpan.textContent = `= ${(rateX / 100) * val} coin`;
                                                    } else {
                                                        resultSpan.textContent = '';
                                                    }
                                                }

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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
