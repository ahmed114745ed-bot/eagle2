<div id="paymentCredentialSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-credit-card"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Payment') }}</h3>
            <p>{{ __('Configure payment gateways and credential settings') }}</p>
        </div>
    </div>

    <div class="py-payments-grid">
        @foreach ($paymentCoins as $coin)
            <form class="py-form-reset" action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="current_tab" value="">
                <div class="py-gateway-card">
                    {{-- Card Header --}}
                    <div class="py-gateway-header">
                        <div class="py-gateway-avatar">
                            <img src="{{ $coin->photo ? getImagePath($coin->photo) : asset('images/dollar.jpg') }}"
                                 alt="{{ $coin->title }}">
                        </div>
                        <div class="py-gateway-title">
                            <h5>{{ __('admin.' . $coin->title) }}</h5>
                            <span>{{ __('Payment Gateway') }}</span>
                        </div>
                        <div class="py-gateway-toggle">
                            <input type="hidden" name="is_{{ $coin->type }}_active" value="0">
                            <input type="hidden" name="payment_getaway_id" value="{{ $coin->id }}">
                            <input type="checkbox" id="{{ $coin->type }}Radio"
                                   class="custom-payment-radio libraryRealTime"
                                   name="is_{{ $coin->type }}_active" value="1"
                                {{ $coin->status == 1 && @$settings['is_' . $coin->type . '_active'] == '1' ? 'checked' : '' }}>
                            <label for="{{ $coin->type }}Radio" class="switch"></label>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="py-gateway-body">
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
                    </div>

                    {{-- Card Footer --}}
                    <div class="py-gateway-footer">
                        <button type="submit" class="btn py-btn-save">
                            <i class="fas fa-save"></i> {{ __('save') }}
                        </button>
                    </div>
                </div>
            </form>
        @endforeach
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Form Reset ────────────────────────────────────────────── */
    .py-form-reset {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
    }

    /* ── Payments Grid ─────────────────────────────────────────── */
    .py-payments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        align-items: start;
    }

    /* ── Gateway Card ──────────────────────────────────────────── */
    .py-gateway-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .dark-mode .py-gateway-card {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .py-gateway-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .dark-mode .py-gateway-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }

    /* ── Gateway Header ────────────────────────────────────────── */
    .py-gateway-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark-mode .py-gateway-header {
        border-bottom-color: rgba(255,255,255,0.06);
    }
    .py-gateway-avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        overflow: hidden;
        flex-shrink: 0;
        border: 2px solid #e2e8f0;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .dark-mode .py-gateway-avatar {
        border-color: rgba(255,255,255,0.08);
        background: #0f172a;
    }
    .py-gateway-avatar img {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        display: block !important;
        border-radius: 0 !important;
    }
    .py-gateway-title {
        flex: 1;
        min-width: 0;
    }
    .py-gateway-title h5 {
        margin: 0 0 2px 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        text-transform: capitalize;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark-mode .py-gateway-title h5 {
        color: #f1f5f9;
    }
    .py-gateway-title span {
        font-size: 12px;
        color: #94a3b8;
    }
    .py-gateway-toggle {
        flex-shrink: 0;
    }

    /* ── Gateway Body ──────────────────────────────────────────── */
    .py-gateway-body {
        padding: 20px;
        flex: 1;
    }
    .py-gateway-body .form-group {
        margin-bottom: 14px;
    }
    .py-gateway-body label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
    }
    .dark-mode .py-gateway-body label {
        color: #94a3b8;
    }
    .py-gateway-body .form-control {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 13px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #f8fafc !important;
    }
    .dark-mode .py-gateway-body .form-control {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }
    .py-gateway-body .form-control:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none;
    }
    .py-gateway-body select.form-control {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 36px !important;
        cursor: pointer;
    }

    /* ── Gateway Footer ────────────────────────────────────────── */
    .py-gateway-footer {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
    }
    .dark-mode .py-gateway-footer {
        border-top-color: rgba(255,255,255,0.06);
    }
    .py-btn-save {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 10px 24px !important;
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 3px 10px rgba(99,102,241,0.25);
        width: auto !important;
    }
    .py-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99,102,241,0.3);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }
    .py-btn-save:active {
        transform: translateY(0);
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 992px) {
        .py-payments-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 480px) {
        .py-gateway-header {
            padding: 16px;
        }
        .py-gateway-body {
            padding: 16px;
        }
        .py-gateway-avatar {
            width: 44px;
            height: 44px;
        }
    }
</style>
