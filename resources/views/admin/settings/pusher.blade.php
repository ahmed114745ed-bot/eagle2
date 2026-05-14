<div id="pusherSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-satellite-dish"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Real Time Setting') }}</h3>
            <p>{{ __('Configure Pusher, Firebase and real-time communication') }}</p>
        </div>
    </div>

    <div class="ps-providers-grid">

        {{-- ══════════════════════════════════════════════════════════
             PUSHER CARD
        ══════════════════════════════════════════════════════════ --}}
        <form class="ps-form-reset" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="ps-provider-card">
                <div class="ps-provider-header" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                    <div class="ps-provider-logo">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="ps-provider-title">
                        <h5>{{ __('pusher') }}</h5>
                        <span>{{ __('Real-time messaging service') }}</span>
                    </div>
                    <div class="ps-provider-radio">
                        <input type="radio" id="pusherRadio" class="custom-radio pusherLib"
                               name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                        <label for="pusherRadio" class="switch"></label>
                    </div>
                </div>

                <div class="ps-provider-body">
                    <div class="ps-input-group">
                        <label><i class="fas fa-fingerprint"></i> {{ __('pusher_app_id') }}</label>
                        <input type="text" name="pusher_app_id" placeholder="pusher_app_id"
                               value="{{ $pusher_app_id }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-key"></i> {{ __('pusher_app_key') }}</label>
                        <input type="text" name="pusher_app_key" placeholder="pusher_app_key"
                               value="{{ $pusher_app_key }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-lock"></i> {{ __('pusher_app_secret') }}</label>
                        <input type="text" name="pusher_app_secret" placeholder="pusher_app_secret"
                               value="{{ $pusher_app_secret }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-globe"></i> {{ __('pusher_app_cluster') }}</label>
                        <input type="text" name="pusher_app_cluster" placeholder="pusher_app_cluster"
                               value="{{ $pusher_app_cluster }}" class="form-control ps-input" required>
                    </div>

                    {{-- Webhook Endpoints --}}
                    <div class="ps-webhooks-section">
                        <div class="ps-webhooks-title">
                            <i class="fas fa-link"></i> {{ __('webhook_endpoints') }}
                        </div>

                        {{-- Chat Room Listener - Channel Existence --}}
                        <div class="ps-webhook-item">
                            <div class="ps-webhook-info">
                                <span class="ps-webhook-name">{{ __('chat_room_listener') }}</span>
                                <span class="ps-webhook-desc">{{ __('chat_room_listener_desc') }}</span>
                            </div>
                            <div class="ps-webhook-url-wrap">
                                <input type="text" class="form-control ps-input ps-input-readonly"
                                       value="{{ url('/api/chat-room-listener') }}" readonly>
                                <button type="button" class="ps-copy-btn" onclick="copyPusherWebhook(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="ps-webhook-tag ps-tag-blue">
                                <i class="fas fa-tag"></i> {{ __('event') }}: {{ __('channel_existence') }}
                            </div>
                        </div>

                        {{-- Chat Room Presence --}}
                        <div class="ps-webhook-item">
                            <div class="ps-webhook-info">
                                <span class="ps-webhook-name">{{ __('chat_room_presence') }}</span>
                                <span class="ps-webhook-desc">{{ __('chat_room_presence_desc') }}</span>
                            </div>
                            <div class="ps-webhook-url-wrap">
                                <input type="text" class="form-control ps-input ps-input-readonly"
                                       value="{{ url('/api/chat-room-listener') }}" readonly>
                                <button type="button" class="ps-copy-btn" onclick="copyPusherWebhook(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="ps-webhook-tag ps-tag-amber">
                                <i class="fas fa-tag"></i> {{ __('event') }}: {{ __('presence') }}
                            </div>
                        </div>

                        {{-- Pusher Edit User --}}
                        <div class="ps-webhook-item">
                            <div class="ps-webhook-info">
                                <span class="ps-webhook-name">{{ __('pusher_edit_user') }}</span>
                                <span class="ps-webhook-desc">{{ __('pusher_edit_user_desc') }}</span>
                            </div>
                            <div class="ps-webhook-url-wrap">
                                <input type="text" class="form-control ps-input ps-input-readonly"
                                       value="{{ url('/api/pusher-edit-user') }}" readonly>
                                <button type="button" class="ps-copy-btn" onclick="copyPusherWebhook(this)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="ps-webhook-tag ps-tag-blue">
                                <i class="fas fa-tag"></i> {{ __('event') }}: {{ __('channel_existence') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ps-provider-footer">
                    <button type="submit" class="btn ps-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ══════════════════════════════════════════════════════════
             FIREBASE CARD
        ══════════════════════════════════════════════════════════ --}}
        <form class="ps-form-reset" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="ps-provider-card">
                <div class="ps-provider-header" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                    <div class="ps-provider-logo">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="ps-provider-title">
                        <h5>{{ __('firebase') }}</h5>
                        <span>{{ __('Google cloud messaging') }}</span>
                    </div>
                    <div class="ps-provider-badge">{{ __('soon') }}</div>
                    <div class="ps-provider-radio">
                        <input type="radio" id="firebaseRadio" class="custom-radio firebaseLib"
                               name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                        <label for="firebaseRadio" class="switch"></label>
                    </div>
                </div>

                <div class="ps-provider-body">
                    <div class="ps-input-group">
                        <label><i class="fas fa-key"></i> {{ __('firebase_api_key') }}</label>
                        <input type="text" name="firebase_api_key" placeholder="{{ __('firebase_api_key') }}"
                               value="{{ $firebase_api_key }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-shield-alt"></i> {{ __('firebase_auth_domain') }}</label>
                        <input type="text" name="firebase_auth_domain" placeholder="{{ __('firebase_auth_domain') }}"
                               value="{{ $firebase_auth_domain }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-database"></i> {{ __('firebase_database_url') }}</label>
                        <input type="text" name="firebase_database_url" placeholder="{{ __('firebase_database_url') }}"
                               value="{{ $firebase_database_url }}" class="form-control ps-input" required>
                    </div>
                </div>

                <div class="ps-provider-footer">
                    <button type="submit" class="btn ps-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- ══════════════════════════════════════════════════════════
             SUPABASE CARD
        ══════════════════════════════════════════════════════════ --}}
        <form class="ps-form-reset" action="{{ route('admin.update-agora-zego') }}" method="POST">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="ps-provider-card">
                <div class="ps-provider-header" style="background: linear-gradient(135deg, #10b981, #34d399);">
                    <div class="ps-provider-logo">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="ps-provider-title">
                        <h5>{{ __('supabase') }}</h5>
                        <span>{{ __('Open source backend platform') }}</span>
                    </div>
                    <div class="ps-provider-badge">{{ __('soon') }}</div>
                    <div class="ps-provider-radio">
                        <input type="radio" id="supabaseRadio" class="custom-radio supabaseLib"
                               name="library" value="3" {{ $library == '3' ? 'checked' : '' }}>
                        <label for="supabaseRadio" class="switch"></label>
                    </div>
                </div>

                <div class="ps-provider-body">
                    <div class="ps-input-group">
                        <label><i class="fas fa-link"></i> {{ __('supabase_url') }}</label>
                        <input type="text" name="supabase_url" placeholder="{{ __('supabase_url') }}"
                               value="{{ $supabase_url }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-key"></i> {{ __('supabase_key') }}</label>
                        <input type="text" name="supabase_key" placeholder="{{ __('supabase_key') }}"
                               value="{{ $supabase_key }}" class="form-control ps-input" required>
                    </div>
                    <div class="ps-input-group">
                        <label><i class="fas fa-user-shield"></i> {{ __('supabase_service_role_key') }}</label>
                        <input type="text" name="supabase_service_role_key" placeholder="{{ __('supabase_service_role_key') }}"
                               value="{{ $supabase_service_role_key }}" class="form-control ps-input" required>
                    </div>
                </div>

                <div class="ps-provider-footer">
                    <button type="submit" class="btn ps-btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Form Reset ────────────────────────────────────────────── */
    .ps-form-reset {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
    }

    /* ── Providers Grid ────────────────────────────────────────── */
    .ps-providers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
        align-items: start;
    }

    /* ── Provider Card ─────────────────────────────────────────── */
    .ps-provider-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .dark-mode .ps-provider-card {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .ps-provider-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .dark-mode .ps-provider-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }

    /* ── Provider Header ───────────────────────────────────────── */
    .ps-provider-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 20px;
        color: #fff;
        position: relative;
    }
    .ps-provider-logo {
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
    .ps-provider-title {
        flex: 1;
    }
    .ps-provider-title h5 {
        margin: 0 0 2px 0;
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        text-transform: capitalize;
    }
    .ps-provider-title span {
        font-size: 12px;
        opacity: 0.85;
    }
    .ps-provider-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(255,255,255,0.25);
        color: #fff;
        backdrop-filter: blur(10px);
        flex-shrink: 0;
    }
    .ps-provider-radio {
        flex-shrink: 0;
    }

    /* ── Provider Body ─────────────────────────────────────────── */
    .ps-provider-body {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .ps-input-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .ps-input-group label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }
    .dark-mode .ps-input-group label {
        color: #94a3b8;
    }
    .ps-input-group label i {
        font-size: 11px;
        opacity: 0.7;
    }
    .ps-input {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 13px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #f8fafc !important;
    }
    .dark-mode .ps-input {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }
    .ps-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none;
    }
    .ps-input-readonly {
        background: #f1f5f9 !important;
        cursor: not-allowed;
        color: #64748b !important;
        font-size: 12px !important;
    }
    .dark-mode .ps-input-readonly {
        background: rgba(255,255,255,0.04) !important;
        color: #94a3b8 !important;
    }

    /* ── Webhooks Section ──────────────────────────────────────── */
    .ps-webhooks-section {
        margin-top: 6px;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .dark-mode .ps-webhooks-section {
        border-top-color: rgba(255,255,255,0.06);
    }
    .ps-webhooks-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dark-mode .ps-webhooks-title {
        color: #f1f5f9;
    }
    .ps-webhooks-title i {
        color: #6366f1;
        font-size: 13px;
    }

    /* ── Webhook Item ──────────────────────────────────────────── */
    .ps-webhook-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .dark-mode .ps-webhook-item {
        background: #0f172a;
        border-color: rgba(255,255,255,0.06);
    }
    .ps-webhook-item:hover {
        border-color: #cbd5e1;
    }
    .dark-mode .ps-webhook-item:hover {
        border-color: rgba(255,255,255,0.12);
    }
    .ps-webhook-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .ps-webhook-name {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        text-transform: capitalize;
    }
    .dark-mode .ps-webhook-name {
        color: #e2e8f0;
    }
    .ps-webhook-desc {
        font-size: 11px;
        color: #94a3b8;
    }
    .ps-webhook-url-wrap {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .ps-webhook-url-wrap .ps-input {
        flex: 1;
    }
    .ps-copy-btn {
        width: 38px !important;
        height: 38px;
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        background: #fff !important;
        color: #64748b !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
        padding: 0 !important;
    }
    .dark-mode .ps-copy-btn {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #94a3b8 !important;
    }
    .ps-copy-btn:hover {
        background: #6366f1 !important;
        border-color: #6366f1 !important;
        color: #fff !important;
    }

    /* ── Webhook Tags ──────────────────────────────────────────── */
    .ps-webhook-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
        width: fit-content;
    }
    .ps-tag-blue {
        background: rgba(99,102,241,0.1);
        color: #6366f1;
    }
    .dark-mode .ps-tag-blue {
        background: rgba(99,102,241,0.15);
        color: #818cf8;
    }
    .ps-tag-amber {
        background: rgba(245,158,11,0.1);
        color: #d97706;
    }
    .dark-mode .ps-tag-amber {
        background: rgba(245,158,11,0.15);
        color: #fbbf24;
    }

    /* ── Provider Footer ───────────────────────────────────────── */
    .ps-provider-footer {
        padding: 14px 20px;
        border-top: 1px solid #f1f5f9;
    }
    .dark-mode .ps-provider-footer {
        border-top-color: rgba(255,255,255,0.06);
    }
    .ps-btn-save {
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
    .ps-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99,102,241,0.3);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 992px) {
        .ps-providers-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 480px) {
        .ps-provider-body {
            padding: 16px;
        }
        .ps-provider-header {
            padding: 14px 16px;
            flex-wrap: wrap;
            gap: 10px;
        }
    }
</style>

<script>
    function copyPusherWebhook(btn) {
        const input = btn.closest('.ps-webhook-url-wrap').querySelector('input');
        const tempInput = document.createElement('input');
        tempInput.value = input.value;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999);

        try {
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';

            setTimeout(function() {
                btn.innerHTML = originalHTML;
            }, 2000);
        } catch (err) {
            console.error('Failed to copy: ', err);
            document.body.removeChild(tempInput);
        }
    }
</script>
