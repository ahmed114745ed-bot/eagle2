<div id="paymentCredentialSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-credit-card"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Payment') }}</h3>
            <p>{{ __('Configure payment gateways and credential settings') }}</p>
        </div>
    </div>

    <div class="form">
        <label class="d-block">{{ __('Payment Credential Settings:') }}</label>

        <div class="row mt-4">
            @foreach ($paymentCoins as $coin)
                <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                    <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="current_tab" value="">
                        <div class="card payment-card p-3 shadow">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="m-0 color-white">{{ __('admin.' . $coin->title) }}</h4>
                                <div class="d-flex align-items-center">
                                    <input type="hidden" name="is_{{ $coin->type }}_active" value="0">
                                    <input type="hidden" name="payment_getaway_id" value={{ $coin->id }}>
                                    <input type="checkbox" id="{{ $coin->type }}Radio"
                                           class="custom-payment-radio libraryRealTime"
                                           name="is_{{ $coin->type }}_active" value="1"
                                        {{ $coin->status == 1 && @$settings['is_' . $coin->type . '_active'] == '1' ? 'checked' : '' }}>
                                    <label for="{{ $coin->type }}Radio" class="switch"></label>
                                </div>
                            </div>

                            <div class="text-center my-3">
                                <img
                                    src="{{ $coin->photo ? getImagePath($coin->photo) : asset('images/dollar.jpg') }}"
                                    alt="{{ $coin->title }}"
                                    style="border-radius: 50%; width: 100px; height: 100px; object-fit: contain; display: block; margin: 3px auto; background: #fff;">
                            </div>

                            <div class="row">
                                @if ($coin->type == 'fawry')
                                    @include('admin.settings.partial_payments.fawry')
                                @endif
                                @if ($coin->type == 'utd_fawry')
                                    @include('admin.settings.partial_payments.utd_fawry')
                                @endif
                                @if ($coin->type == 'utd_paymob')
                                    @include('admin.settings.partial_payments.utd_paymob')
                                @endif
                                @if ($coin->type == 'strip')
                                    @include('admin.settings.partial_payments.stripe')
                                @endif
                                @if ($coin->type == 'cash_free')
                                    @include('admin.settings.partial_payments.cash_free')
                                @endif
                                @if ($coin->type == 'apple_pay')
                                    @include('admin.settings.partial_payments.apple_pay')
                                @endif
                                @if ($coin->type == 'google_pay')
                                    @include('admin.settings.partial_payments.google_pay')
                                @endif
                                @if ($coin->type == 'sky_pay')
                                    @include('admin.settings.partial_payments.sky_pay')
                                @endif
                                @if ($coin->type == 'opay')
                                    @include('admin.settings.partial_payments.opay')
                                @endif
                                @if ($coin->type == 'paytabs')
                                    @include('admin.settings.partial_payments.paytabs')
                                @endif
                                @if ($coin->type == 'paypal')
                                    @include('admin.settings.partial_payments.paypal ')
                                @endif
                                @if ($coin->type == 'codapay')
                                    @include('admin.settings.partial_payments.codapay')
                                @endif
                                @if ($coin->type == 'utd')
                                    @include('admin.settings.partial_payments.utd')
                                @endif
                            </div>

                            <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
