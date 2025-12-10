<div id="paymentCredentialSettings" class="settings-section">

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
                                <h4 class="m-0">{{ __('admin.' . $coin->title) }}</h4>
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="paypal_base_url">{{ __('admin.base_url') }}:</label>
                                            <input type="text" id="paypal_base_url"
                                                   name="paypal_base_url" placeholder="paypal_base_url"
                                                   value="{{ $settings['paypal_base_url'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="paypal_client_id">{{ __('admin.client_id') }}:</label>
                                            <input type="text" id="paypal_client_id"
                                                   name="paypal_client_id" placeholder="paypal_client_id"
                                                   value="{{ $settings['paypal_client_id'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="paypal_client_secret">{{ __('admin.client_secret') }}
                                                :</label>
                                            <input type="text" id="paypal_client_secret"
                                                   name="paypal_client_secret"
                                                   placeholder="paypal_client_secret"
                                                   value="{{ $settings['paypal_client_secret'] ?? '' }}"
                                                   class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="paypal_currency">{{ __('admin.currency') }}:</label>
                                            <input type="text" id="paypal_currency"
                                                   name="paypal_currency"
                                                   placeholder="paypal_currency"
                                                   value="{{ $settings['paypal_currency'] ?? '' }}"
                                                   class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paypal_webhook_id">{{ __('admin.webhook_id') }}:</label>
                                            <div class="copy-container">
                                                <input type="text" id="paypal_webhook_id"
                                                       name="paypal_webhook_id"
                                                       placeholder="paypal_webhook_id"
                                                       value="{{ $settings['paypal_webhook_id'] ?? '' }}"
                                                       class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paypal_webhook_url">{{ __('admin.webhook_url') }}
                                                :</label>
                                            <div class="copy-container">
                                                <input type="text" id="paypal_webhook_url"
                                                       name="paypal_webhook_url"
                                                       placeholder="paypal_webhook_url"
                                                       value="{{ url('/api/paypal-callback') }}"
                                                       class="form-control" required>
                                                <button type="button" class="copy-button"
                                                        data-copy-target="paypal_webhook_url" title="Copy">📋
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($coin->type == 'codapay')
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="codapay_base_url">{{ __('admin.base_url') }}:</label>
                                            <input type="text" id="codapay_base_url"
                                                   name="codapay_base_url" placeholder="codapay_base_url"
                                                   value="{{ $settings['codapay_base_url'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="codapay_api_key">{{ __('admin.api_key') }}:</label>
                                            <input type="text" id="codapay_api_key"
                                                   name="codapay_api_key" placeholder="codapay_api_key"
                                                   value="{{ $settings['codapay_api_key'] ?? '' }}"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label
                                                for="codapay_project_id">{{ __('admin.project_id') }}:</label>
                                            <input type="text" id="codapay_project_id"
                                                   name="codapay_project_id"
                                                   placeholder="codapay_project_id"
                                                   value="{{ $settings['codapay_project_id'] ?? '' }}"
                                                   class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="codapay_webhook_url">{{ __('admin.webhook_url') }}
                                                :</label>
                                            <div class="copy-container">
                                                <input type="text" id="codapay_webhook_url"
                                                       name="codapay_webhook_url"
                                                       placeholder="codapay_webhook_url"
                                                       value="{{ url('/api/codapay-callback') }}"
                                                       class="form-control" required>
                                                <button type="button" class="copy-button"
                                                        data-copy-target="codapay_webhook_url" title="Copy">📋
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                        </div>
                    </form>
                </div>
            @endforeach


        </div>
    </div>
</div>
