<div id="mobileLinks" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <i class="fas fa-mobile-alt"></i>
        </div>
        <div class="section-header-text">
            <h3>{{ __('Mobile App Links') }}</h3>
            <p>{{ __('Configure download links for different platforms') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data"
          class="mlinks-form">
        @csrf
        <input type="hidden" name="current_tab" value="">


        <div class="mlinks-grid">

            {{-- ── Android Card ── --}}
            <div class="mlinks-card">
                <div class="mlinks-card-header android-gradient">
                    <div class="mlinks-card-decoration"></div>
                    <div class="mlinks-card-icon-wrap">
                        <i class="fab fa-android"></i>
                    </div>
                    <div class="mlinks-card-title">
                        <h5>{{ __('Android') }}</h5>
                        <span>Google Play Store</span>
                    </div>
                    <div class="mlinks-card-badge">
                        <i class="fab fa-google-play"></i>
                    </div>
                </div>
                <div class="mlinks-card-body">
                    <label class="mlinks-label">
                        <i class="fas fa-link"></i> {{ __('Store URL') }}
                    </label>
                    <div class="mlinks-input-wrap">
                        <span class="mlinks-input-prefix">
                            <i class="fas fa-globe"></i>
                        </span>
                        <input type="url" name="android_link"
                               value="{{ $settings['android_link'] ?? '' }}"
                               placeholder="{{ __('https://play.google.com/store/apps/...') }}"
                               class="form-control mlinks-input">
                        @if(!empty($settings['android_link']))
                            <a href="{{ $settings['android_link'] }}" target="_blank" class="mlinks-input-action"
                               title="{{ __('Open link') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                    </div>
                    <div class="mlinks-status">
                        @if(!empty($settings['android_link']))
                            <span class="mlinks-status-dot active"></span>
                            <span class="mlinks-status-text">{{ __('Link configured') }}</span>
                        @else
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── iOS Card ── --}}
            <div class="mlinks-card">
                <div class="mlinks-card-header ios-gradient">
                    <div class="mlinks-card-decoration"></div>
                    <div class="mlinks-card-icon-wrap">
                        <i class="fab fa-apple"></i>
                    </div>
                    <div class="mlinks-card-title">
                        <h5>{{ __('iOS') }}</h5>
                        <span>App Store</span>
                    </div>
                    <div class="mlinks-card-badge">
                        <i class="fab fa-app-store"></i>
                    </div>
                </div>
                <div class="mlinks-card-body">
                    <label class="mlinks-label">
                        <i class="fas fa-link"></i> {{ __('Store URL') }}
                    </label>
                    <div class="mlinks-input-wrap">
                        <span class="mlinks-input-prefix">
                            <i class="fas fa-globe"></i>
                        </span>
                        <input type="url" name="ios_link"
                               value="{{ $settings['ios_link'] ?? '' }}"
                               placeholder="{{ __('https://apps.apple.com/app/...') }}"
                               class="form-control mlinks-input">
                        @if(!empty($settings['ios_link']))
                            <a href="{{ $settings['ios_link'] }}" target="_blank" class="mlinks-input-action"
                               title="{{ __('Open link') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                    </div>
                    <div class="mlinks-status">
                        @if(!empty($settings['ios_link']))
                            <span class="mlinks-status-dot active"></span>
                            <span class="mlinks-status-text">{{ __('Link configured') }}</span>
                        @else
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Huawei Card ── --}}
            <div class="mlinks-card">
                <div class="mlinks-card-header huawei-gradient">
                    <div class="mlinks-card-decoration"></div>
                    <div class="mlinks-card-icon-wrap">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="mlinks-card-title">
                        <h5>{{ __('Huawei') }}</h5>
                        <span>AppGallery</span>
                    </div>
                    <div class="mlinks-card-badge">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <div class="mlinks-card-body">
                    <label class="mlinks-label">
                        <i class="fas fa-link"></i> {{ __('Store URL') }}
                    </label>
                    <div class="mlinks-input-wrap">
                        <span class="mlinks-input-prefix">
                            <i class="fas fa-globe"></i>
                        </span>
                        <input type="url" name="huawei_link"
                               value="{{ $settings['huawei_link'] ?? '' }}"
                               placeholder="{{ __('https://appgallery.huawei.com/app/...') }}"
                               class="form-control mlinks-input">
                        @if(!empty($settings['huawei_link']))
                            <a href="{{ $settings['huawei_link'] }}" target="_blank" class="mlinks-input-action"
                               title="{{ __('Open link') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        @endif
                    </div>
                    <div class="mlinks-status">
                        @if(!empty($settings['huawei_link']))
                            <span class="mlinks-status-dot active"></span>
                            <span class="mlinks-status-text">{{ __('Link configured') }}</span>
                        @else
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Save Button --}}
        <div class="mlinks-footer">
            <button type="submit" class="btn btn-primary mlinks-save-btn">
                <i class="fas fa-save"></i> {{ __('Save All Links') }}
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════
     MOBILE LINKS — Scoped Styles
     ═══════════════════════════════════════════════════ --}}
<style>
    /* ── Form Reset ── */
    .mlinks-form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ── Section Intro ── */
    .mlinks-intro {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
        padding: 18px 22px;
        background: var(--white, #fff);
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .dark-mode .mlinks-intro {
        background: var(--dark-secondry-color, #1e293b);
        border-color: rgba(255,255,255,0.06);
    }
    .mlinks-intro-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(139,92,246,0.25);
    }
    .mlinks-intro h4 {
        margin: 0 0 3px;
        font-size: 17px;
        font-weight: 700;
        color: var(--text-secondary-color, #1e293b);
    }
    .dark-mode .mlinks-intro h4 { color: #f1f5f9; }
    .mlinks-intro p {
        margin: 0;
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Cards Grid ── */
    .mlinks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 22px;
    }

    /* ── Individual Card ── */
    .mlinks-card {
        border-radius: 18px;
        overflow: hidden;
        background: var(--white, #fff);
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark-mode .mlinks-card {
        background: var(--dark-secondry-color, #1e293b);
        border-color: rgba(255,255,255,0.06);
    }
    .mlinks-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 36px rgba(0,0,0,0.1);
    }
    .dark-mode .mlinks-card:hover {
        box-shadow: 0 12px 36px rgba(0,0,0,0.3);
    }

    /* ── Card Header ── */
    .mlinks-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
    }
    .mlinks-card-decoration {
        position: absolute;
        top: -40%;
        right: -15%;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        pointer-events: none;
    }
    .mlinks-card-header::after {
        content: '';
        position: absolute;
        bottom: -25%;
        left: -8%;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        pointer-events: none;
    }
    .mlinks-card-icon-wrap {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .mlinks-card-title {
        flex: 1;
        position: relative;
        z-index: 1;
    }
    .mlinks-card-title h5 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: #fff;
    }
    .mlinks-card-title span {
        font-size: 12px;
        color: rgba(255,255,255,0.7);
        font-weight: 500;
    }
    .mlinks-card-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: rgba(255,255,255,0.8);
        position: relative;
        z-index: 1;
    }

    /* Gradient Themes */
    .android-gradient { background: linear-gradient(135deg, #34d399, #059669); }
    .ios-gradient      { background: linear-gradient(135deg, #64748b, #334155); }
    .huawei-gradient   { background: linear-gradient(135deg, #ef4444, #b91c1c); }

    /* ── Card Body ── */
    .mlinks-card-body {
        padding: 22px;
    }

    /* ── Label ── */
    .mlinks-label {
        display: flex !important;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary-color, #475569);
        margin-bottom: 10px !important;
    }
    .dark-mode .mlinks-label {
        color: #cbd5e1;
    }
    .mlinks-label i {
        font-size: 12px;
        color: #94a3b8;
    }

    /* ── Input Wrap ── */
    .mlinks-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .mlinks-input-prefix {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
        z-index: 2;
    }
    .rtl .mlinks-input-prefix {
        left: auto;
        right: 14px;
    }
    .mlinks-input {
        background: #f8fafc !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 12px 44px 12px 40px !important;
        font-size: 13px !important;
        transition: all 0.2s ease;
        width: 100% !important;
        direction: ltr;
    }
    .rtl .mlinks-input {
        padding: 12px 40px 12px 44px !important;
    }
    .dark-mode .mlinks-input {
        background: rgba(255,255,255,0.04) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: #f1f5f9 !important;
    }
    .mlinks-input:focus {
        background: #fff !important;
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 3px rgba(139,92,246,0.1) !important;
        outline: none !important;
    }
    .dark-mode .mlinks-input:focus {
        background: rgba(255,255,255,0.06) !important;
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 3px rgba(139,92,246,0.15) !important;
    }
    .mlinks-input-action {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(139,92,246,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8b5cf6;
        font-size: 13px;
        text-decoration: none;
        transition: all 0.2s ease;
        z-index: 2;
    }
    .rtl .mlinks-input-action {
        right: auto;
        left: 10px;
    }
    .mlinks-input-action:hover {
        background: #8b5cf6;
        color: #fff;
        transform: translateY(-50%) scale(1.05);
    }

    /* ── Status Indicator ── */
    .mlinks-status {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 8px 12px;
        border-radius: 8px;
        background: #f8fafc;
    }
    .dark-mode .mlinks-status {
        background: rgba(255,255,255,0.03);
    }
    .mlinks-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .mlinks-status-dot.active {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
    }
    .mlinks-status-dot.inactive {
        background: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245,158,11,0.15);
    }
    .mlinks-status-text {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
    }
    .dark-mode .mlinks-status-text {
        color: #94a3b8;
    }

    /* ── Footer / Save ── */
    .mlinks-footer {
        margin-top: 28px;
        display: flex;
        justify-content: flex-end;
    }
    .mlinks-save-btn {
        padding: 13px 36px !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
        border: none !important;
        color: #fff !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 4px 14px rgba(139,92,246,0.3) !important;
        width: auto !important;
    }
    .mlinks-save-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(139,92,246,0.4) !important;
    }
    .mlinks-save-btn:active {
        transform: translateY(0) !important;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .mlinks-grid {
            grid-template-columns: 1fr;
        }
        .mlinks-intro {
            padding: 14px 16px;
        }
        .mlinks-footer {
            justify-content: stretch;
        }
        .mlinks-save-btn {
            width: 100% !important;
        }
    }
</style>
