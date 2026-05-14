<div id="realTimeSetting" class="settings-section">

    {{-- ══════════════════════════════════════════════════════════
         SECTION HEADER
    ══════════════════════════════════════════════════════════ --}}
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-broadcast-tower"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Sound & Video System Setting') }}</h3>
            <p>{{ __('Configure real-time communication providers and streaming settings') }}</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         PROVIDER CARDS
    ══════════════════════════════════════════════════════════ --}}
    <div class="rt-providers-grid">

        {{-- ── Tencent Card ──────────────────────────────────────── --}}
        <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="rt-provider-card">
                <div class="rt-provider-header" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
                    <div class="rt-provider-logo">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <div class="rt-provider-title">
                        <h5>{{ __('admin.Tencent') }}</h5>
                        <span>{{ __('Cloud communication platform') }}</span>
                    </div>
                    <div class="rt-provider-badge rt-badge-soon">{{ __('soon') }}</div>
                </div>
                <div class="rt-provider-body">
                    <div class="rt-input-group">
                        <label><i class="fas fa-key"></i> {{ __('admin.server_secret') }}</label>
                        <input type="text" name="tencent_server_secret" placeholder="server_secret"
                               value="{{ $tencent_server_secret }}" class="form-control rt-input" required>
                    </div>
                    <div class="rt-input-row">
                        <div class="rt-input-group">
                            <label><i class="fas fa-fingerprint"></i> {{ __('admin.app_id') }}</label>
                            <input type="text" name="tencent_app_id" placeholder="app_id"
                                   value="{{ $tencent_app_id }}" class="form-control rt-input" required>
                        </div>
                        <div class="rt-input-group">
                            <label><i class="fas fa-signature"></i> {{ __('admin.app_sign') }}</label>
                            <input type="text" name="app_sign" placeholder="app_sign"
                                   value="{{ $app_sign }}" class="form-control rt-input" required>
                        </div>
                    </div>
                </div>
                <div class="rt-provider-footer">
                    <button type="submit" class="btn rt-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ── UTD VOICE Card ────────────────────────────────────── --}}
        <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="rt-provider-card">
                <div class="rt-provider-header" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                    <div class="rt-provider-logo">
                        <i class="fas fa-microphone-alt"></i>
                    </div>
                    <div class="rt-provider-title">
                        <h5>{{ __('UTD VOICE') }}</h5>
                        <span>{{ __('Voice communication service') }}</span>
                    </div>
                </div>
                <div class="rt-provider-body">
                    <div class="rt-input-group">
                        <label><i class="fas fa-shield-alt"></i> {{ __('Encrypted Token') }}</label>
                        <input type="text" name="zego_token" placeholder="{{ __('server_secret') }}"
                               value="{{ $zego_token }}" class="form-control rt-input" required>
                    </div>
                    <div class="rt-input-group">
                        <label><i class="fas fa-id-badge"></i> {{ __('client Id') }}</label>
                        <input type="text" name="zego_key" placeholder="{{ __('server_secret_key') }}"
                               value="{{ $zego_key }}" class="form-control rt-input" required>
                    </div>
                </div>
                <div class="rt-provider-footer">
                    <button type="submit" class="btn rt-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ── UTD-STREAM Card ───────────────────────────────────── --}}
        <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="rt-provider-card">
                <div class="rt-provider-header" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                    <div class="rt-provider-logo">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="rt-provider-title">
                        <h5>{{ __('UTD-STREAM') }}</h5>
                        <span>{{ __('Streaming & media service') }}</span>
                    </div>
                </div>
                <div class="rt-provider-body">
                    <div class="rt-input-row">
                        <div class="rt-input-group">
                            <label><i class="fas fa-fingerprint"></i> {{ __('admin.app_id') }}</label>
                            <input type="text" name="utd_stream_app_id" placeholder="App ID"
                                   value="{{ $utd_stream_app_id }}" class="form-control rt-input" required>
                        </div>
                        <div class="rt-input-group">
                            <label><i class="fas fa-key"></i> {{ __('admin.server_secret') }}</label>
                            <input type="text" name="utd_stream_server_secret" placeholder="Server Secret"
                                   value="{{ $utd_stream_server_secret }}" class="form-control rt-input" required>
                        </div>
                    </div>
                    <div class="rt-input-group">
                        <label><i class="fas fa-lock"></i> Callback Secret</label>
                        <input type="text" name="utd_stream_callback_secret" placeholder="Callback Secret"
                               value="{{ $utd_stream_callback_secret ?? '' }}" class="form-control rt-input" required>
                        <small class="rt-hint">{{ __('Used to verify webhook signatures') }}</small>
                    </div>
                    <div class="rt-input-group">
                        <label><i class="fas fa-link"></i> Webhook URL</label>
                        <div class="rt-webhook-wrap">
                            <input type="text" id="utd_stream_webhook_url"
                                   value="{{ url('/api/utd-stream-webhook') }}"
                                   class="form-control rt-input rt-input-readonly" readonly>
                            <button class="rt-copy-btn" type="button" onclick="copyWebhookUrl(event)" title="Copy">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>
                        <small class="rt-hint">{{ __('This URL is used by UTD-STREAM to send webhook events') }}</small>
                    </div>
                </div>
                <div class="rt-provider-footer">
                    <button type="submit" class="btn rt-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ── Zego Card ─────────────────────────────────────────── --}}
        <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="rt-provider-card">
                <div class="rt-provider-header" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                    <div class="rt-provider-logo">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <div class="rt-provider-title">
                        <h5>{{ __('admin.Zego') }}</h5>
                        <span>{{ __('Real-time communication SDK') }}</span>
                    </div>
                </div>
                <div class="rt-provider-body">
                    <div class="rt-input-row">
                        <div class="rt-input-group">
                            <input type="hidden" name="provider" value="zego">
                            <label><i class="fas fa-key"></i> {{ __('admin.server_secret') }}</label>
                            <input type="text" name="zego_server_secret" placeholder="server_secret"
                                   value="{{ $zego_server_secret }}" class="form-control rt-input" required>
                        </div>
                        <div class="rt-input-group">
                            <label><i class="fas fa-fingerprint"></i> {{ __('admin.app_id') }}</label>
                            <input type="text" name="zego_app_id" placeholder="app_id"
                                   value="{{ $zego_app_id }}" class="form-control rt-input" required>
                        </div>
                    </div>
                    <div class="rt-input-row">
                        <div class="rt-input-group">
                            <label><i class="fas fa-signature"></i> {{ __('admin.app_sign') }}</label>
                            <input type="text" name="app_sign" placeholder="app_sign"
                                   value="{{ $app_sign }}" class="form-control rt-input" required>
                        </div>
                        <div class="rt-input-group">
                            <label><i class="fas fa-filter"></i> {{ __('Filter Enabled') }}</label>
                            <div class="rt-switch-inline">
                                <input type="hidden" name="zego_filter_enabled" value="0">
                                <input type="checkbox" name="zego_filter_enabled" value="1" data-bootstrap-switch
                                    {{ $zego_filter_enabled ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rt-provider-footer">
                    <button type="submit" class="btn rt-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ── Agora Card ────────────────────────────────────────── --}}
        <form class="no-background-form" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="rt-provider-card">
                <div class="rt-provider-header" style="background: linear-gradient(135deg, #10b981, #34d399);">
                    <div class="rt-provider-logo">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="rt-provider-title">
                        <h5>{{ __('admin.Agora') }}</h5>
                        <span>{{ __('Voice & video engine') }}</span>
                    </div>
                </div>
                <div class="rt-provider-body">
                    <div class="rt-input-row">
                        <div class="rt-input-group">
                            <label><i class="fas fa-fingerprint"></i> {{ __('admin.app_id') }}</label>
                            <input type="text" name="app_id" placeholder="app_id"
                                   value="{{ $agora_app_id }}" class="form-control rt-input" required>
                        </div>
                        <div class="rt-input-group">
                            <label><i class="fas fa-certificate"></i> {{ __('certificate') }}</label>
                            <input type="text" name="agora_app_certificate" placeholder="agora_app_certificate"
                                   value="{{ $agora_app_certificate }}" class="form-control rt-input" required>
                        </div>
                    </div>
                </div>
                <div class="rt-provider-footer">
                    <button type="submit" class="btn rt-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         SYSTEM SELECTOR SECTIONS
    ══════════════════════════════════════════════════════════ --}}

    {{-- ── Sound System ──────────────────────────────────────── --}}
    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="rt-selector-section">
            <div class="rt-selector-header">
                <div class="rt-selector-icon" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                    <i class="fas fa-volume-up"></i>
                </div>
                <div>
                    <h5>{{ __('Sound System Setting') }}</h5>
                    <span>{{ __('Choose the active sound provider') }}</span>
                </div>
            </div>
            <div class="rt-radio-grid">
                @foreach ([
                    ['id' => 'agoraSoundRadio', 'value' => '0', 'label' => __('admin.Agora'), 'icon' => 'fas fa-headset', 'color' => '#10b981', 'var' => $soundLibrary],
                    ['id' => 'zegoSoundRadio', 'value' => '1', 'label' => __('admin.Zego'), 'icon' => 'fas fa-satellite-dish', 'color' => '#3b82f6', 'var' => $soundLibrary],
                    ['id' => 'tencentSoundRadio', 'value' => '2', 'label' => __('admin.Tencent'), 'icon' => 'fas fa-cloud', 'color' => '#0ea5e9', 'var' => $soundLibrary],
                    ['id' => 'utdZegoSoundRadio', 'value' => '3', 'label' => __('UTD VOICE'), 'icon' => 'fas fa-microphone-alt', 'color' => '#8b5cf6', 'var' => $soundLibrary],
                    ['id' => 'utdStreamSoundRadio', 'value' => '4', 'label' => __('UTD-STREAM'), 'icon' => 'fas fa-video', 'color' => '#f97316', 'var' => $soundLibrary],
                ] as $opt)
                    <label class="rt-radio-card {{ $opt['var'] == $opt['value'] ? 'active' : '' }}" for="{{ $opt['id'] }}">
                        <input type="radio" id="{{ $opt['id'] }}" class="custom-radio libraryRealTime"
                               name="sound_library" value="{{ $opt['value'] }}" {{ $opt['var'] == $opt['value'] ? 'checked' : '' }}>
                        <div class="rt-radio-icon" style="background: {{ $opt['color'] }};">
                            <i class="{{ $opt['icon'] }}"></i>
                        </div>
                        <span class="rt-radio-label">{{ $opt['label'] }}</span>
                        <div class="rt-radio-check"><i class="fas fa-check"></i></div>
                    </label>
                @endforeach
            </div>
        </div>
    </form>

    {{-- ── Video System ──────────────────────────────────────── --}}
    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="rt-selector-section">
            <div class="rt-selector-header">
                <div class="rt-selector-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                    <i class="fas fa-video"></i>
                </div>
                <div>
                    <h5>{{ __('Video System Setting') }}</h5>
                    <span>{{ __('Choose the active video provider') }}</span>
                </div>
            </div>
            <div class="rt-radio-grid">
                @foreach ([
                    ['id' => 'agoraVideoRadio', 'value' => '0', 'label' => __('admin.Agora'), 'icon' => 'fas fa-headset', 'color' => '#10b981', 'var' => $videoLibrary],
                    ['id' => 'zegoVideoRadio', 'value' => '1', 'label' => __('admin.Zego'), 'icon' => 'fas fa-satellite-dish', 'color' => '#3b82f6', 'var' => $videoLibrary],
                    ['id' => 'tencentVideoRadio', 'value' => '2', 'label' => __('admin.Tencent'), 'icon' => 'fas fa-cloud', 'color' => '#0ea5e9', 'var' => $videoLibrary],
                    ['id' => 'utdZegoVideoRadio', 'value' => '3', 'label' => __('UTD VOICE'), 'icon' => 'fas fa-microphone-alt', 'color' => '#8b5cf6', 'var' => $videoLibrary],
                    ['id' => 'utdStreamVideoRadio', 'value' => '4', 'label' => __('UTD-STREAM'), 'icon' => 'fas fa-video', 'color' => '#f97316', 'var' => $videoLibrary],
                ] as $opt)
                    <label class="rt-radio-card {{ $opt['var'] == $opt['value'] ? 'active' : '' }}" for="{{ $opt['id'] }}">
                        <input type="radio" id="{{ $opt['id'] }}" class="custom-radio libraryRealTime"
                               name="video_library" value="{{ $opt['value'] }}" {{ $opt['var'] == $opt['value'] ? 'checked' : '' }}>
                        <div class="rt-radio-icon" style="background: {{ $opt['color'] }};">
                            <i class="{{ $opt['icon'] }}"></i>
                        </div>
                        <span class="rt-radio-label">{{ $opt['label'] }}</span>
                        <div class="rt-radio-check"><i class="fas fa-check"></i></div>
                    </label>
                @endforeach
            </div>
        </div>
    </form>

    {{-- ── Live System ───────────────────────────────────────── --}}
    <form action="{{ route('admin.update-agora-zego') }}" method="POST">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
        <div class="rt-selector-section">
            <div class="rt-selector-header">
                <div class="rt-selector-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <div>
                    <h5>{{ __('Live System Setting') }}</h5>
                    <span>{{ __('Choose the active live streaming mode') }}</span>
                </div>
                $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
            });
        }

        function initIsPreviewSwitch() {
            const $switch = $('input[name="is_auto_preview"][data-bootstrap-switch]');

            $switch.each(function () {
                $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
            });

            $switch.on('switchChange.bootstrapSwitch', function (event, state) {
                const form = $('#autoPreviewForm');
                const formData = form.serializeArray();
                const newValue = state ? 1 : 0;
                formData.push({name: 'is_auto_preview', value: newValue});

                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: formData,
                    success: function () {
                        console.log('is_auto_preview updated to', newValue);
                    },
                    error: function (xhr) {
                        console.error('Error updating is_auto_preview:', xhr.responseText);
                    }
                });
        }

        // Radio card active state toggle
        document.querySelectorAll('.rt-radio-card input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const name = this.name;
                document.querySelectorAll('.rt-radio-card input[name="' + name + '"]').forEach(function(r) {
                    r.closest('.rt-radio-card').classList.remove('active');
                });
                this.closest('.rt-radio-card').classList.add('active');
            });
        });

        $(document).ready(function() {
            initZegoSwitch();
            initIsPreviewSwitch();
        });
        $(document).on('pjax:success', function() {
            initZegoSwitch();
            initIsPreviewSwitch();
        });

        function copyWebhookUrl(event) {
            const webhookInput = document.getElementById('utd_stream_webhook_url');
            const btn = event.currentTarget;

            const tempInput = document.createElement('input');
            tempInput.value = webhookInput.value;
            document.body.appendChild(tempInput);
            tempInput.select();
            tempInput.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-check"></i>';
                btn.style.color = '#10b981';

                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.style.color = '';
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
                document.body.removeChild(tempInput);
                alert('Failed to copy URL');
            }
        }
    </script>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Provider Cards Grid ───────────────────────────────────── */
    .rt-providers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .rt-providers-grid form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
    }

    /* ── Provider Card ─────────────────────────────────────────── */
    .rt-provider-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .dark-mode .rt-provider-card {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .rt-provider-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .dark-mode .rt-provider-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }

    /* ── Provider Header ───────────────────────────────────────── */
    .rt-provider-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        color: #fff;
        position: relative;
    }
    .rt-provider-logo {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        backdrop-filter: blur(10px);
    }
    .rt-provider-title h5 {
        margin: 0 0 2px 0;
        font-size: 16px;
        font-weight: 700;
        color: #fff;
    }
    .rt-provider-title span {
        font-size: 12px;
        opacity: 0.85;
    }
    .rt-provider-badge {
        position: absolute;
        top: 12px;
        right: 14px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .rtl .rt-provider-badge {
        right: auto;
        left: 14px;
    }
    .rt-badge-soon {
        background: rgba(255,255,255,0.25);
        color: #fff;
        backdrop-filter: blur(10px);
    }

    /* ── Provider Body ─────────────────────────────────────────── */
    .rt-provider-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .rt-input-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .rt-input-group label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }
    .dark-mode .rt-input-group label {
        color: #94a3b8;
    }
    .rt-input-group label i {
        font-size: 11px;
        opacity: 0.7;
    }
    .rt-input {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 13px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #f8fafc !important;
    }
    .dark-mode .rt-input {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }
    .rt-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none;
    }
    .rt-input-readonly {
        background: #f1f5f9 !important;
        cursor: not-allowed;
        color: #64748b !important;
    }
    .dark-mode .rt-input-readonly {
        background: rgba(255,255,255,0.04) !important;
        color: #94a3b8 !important;
    }
    .rt-input-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .rt-hint {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
    }

    /* ── Webhook Wrap ──────────────────────────────────────────── */
    .rt-webhook-wrap {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .rt-webhook-wrap .rt-input {
        flex: 1;
    }
    .rt-copy-btn {
        width: 40px !important;
        height: 40px;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #64748b !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
        padding: 0 !important;
    }
    .dark-mode .rt-copy-btn {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #94a3b8 !important;
    }
    .rt-copy-btn:hover {
        background: #e2e8f0 !important;
        color: #1e293b !important;
    }

    /* ── Switch Inline ─────────────────────────────────────────── */
    .rt-switch-inline {
        padding-top: 6px;
    }

    /* ── Provider Footer ───────────────────────────────────────── */
    .rt-provider-footer {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
    }
    .dark-mode .rt-provider-footer {
        border-top-color: rgba(255,255,255,0.06);
    }
    .rt-btn-save {
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
    .rt-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99,102,241,0.3);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }

    /* ── Selector Sections ─────────────────────────────────────── */
    .rt-selector-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .dark-mode .rt-selector-section {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .rt-selector-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark-mode .rt-selector-header {
        border-bottom-color: rgba(255,255,255,0.06);
    }
    .rt-selector-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .rt-selector-header h5 {
        margin: 0 0 2px 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .dark-mode .rt-selector-header h5 {
        color: #f1f5f9;
    }
    .rt-selector-header > div span {
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Radio Card Grid ───────────────────────────────────────── */
    .rt-radio-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }
    .rt-radio-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }
    .rt-radio-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        padding: 18px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: #f8fafc;
        text-align: center;
    }
    .dark-mode .rt-radio-card {
        background: #0f172a;
        border-color: rgba(255,255,255,0.08);
    }
    .rt-radio-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .rt-radio-card:hover {
        border-color: #6366f1;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(99,102,241,0.12);
    }
    .rt-radio-card.active {
        border-color: #6366f1;
        background: rgba(99,102,241,0.04);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
    }
    .dark-mode .rt-radio-card.active {
        border-color: #818cf8;
        background: rgba(129,140,248,0.08);
    }
    .rt-radio-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    .rt-radio-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
    }
    .dark-mode .rt-radio-label {
        color: #cbd5e1;
    }
    .rt-radio-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #6366f1;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .rtl .rt-radio-check {
        right: auto;
        left: 8px;
    }
    .rt-radio-card.active .rt-radio-check {
        display: flex;
    }

    /* ── Preview Switch ────────────────────────────────────────── */
    .rt-preview-switch {
        flex-shrink: 0;
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 992px) {
        .rt-providers-grid {
            grid-template-columns: 1fr;
        }
        .rt-radio-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .rt-radio-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .rt-radio-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }
        .rt-input-row {
            grid-template-columns: 1fr;
        }
        .rt-selector-section {
            padding: 16px;
        }
    }
    @media (max-width: 480px) {
        .rt-radio-grid,
        .rt-radio-grid-3 {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
