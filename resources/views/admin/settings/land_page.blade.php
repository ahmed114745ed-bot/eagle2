<div id="landPageSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-globe"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Landing Page Settings') }}</h3>
            <p>{{ __('Configure your landing page content, statistics and social media links') }}</p>
        </div>
    </div>

    <div class="settings-container">
        <div class="tabs-sidebar" role="tablist" aria-orientation="vertical">
            <button class="tab-btn active" data-target="#general" type="button" role="tab"
                    aria-controls="general" aria-selected="true">
                <i class="fas fa-cog"></i> {{ __('General Settings') }}
            </button>
            <button class="tab-btn" data-target="#stats" type="button" role="tab" aria-controls="stats"
                    aria-selected="false">
                <i class="fas fa-chart-bar"></i> {{ __('Statistics Settings') }}
            </button>
            <button class="tab-btn" data-target="#social" type="button" role="tab" aria-controls="social"
                    aria-selected="false">
                <i class="fas fa-share-alt"></i> {{ __('Social Media Settings') }}
            </button>
        </div>

        <div class="tab-content">
            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data"
                  id="landingSettingsForm" class="settings-form lp-form-reset">
                @csrf
                <input type="hidden" name="current_tab" value="">
                <input type="hidden" name="inner_tab_type_hash" value="">

                {{-- General Settings --}}
                <div class="tab-pane show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="lp-section-header">
                        <div class="lp-section-icon" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <h5>{{ __('General Settings') }}</h5>
                            <span>{{ __('Basic landing page configuration') }}</span>
                        </div>
                    </div>
                    <div class="lp-input-row">
                        <div class="lp-input-group">
                            <label><i class="fas fa-info-circle"></i> {{ __('About Us Link') }}</label>
                            <input type="url" name="about_us_link"
                                   value="{{ $settings['about_us_link'] ?? '' }}" class="form-control lp-input"
                                   placeholder="{{ __('https://example.com/about') }}">
                        </div>
                        <div class="lp-input-group">
                            <label><i class="fas fa-images"></i> {{ __('Gallery App Link') }}</label>
                            <input type="url" name="gallery_app_link"
                                   value="{{ $settings['gallery_app_link'] ?? '' }}" class="form-control lp-input"
                                   placeholder="{{ __('https://example.com/gallery') }}">
                        </div>
                    </div>
                </div>

                {{-- Statistics Settings --}}
                <div class="tab-pane" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                    <div class="lp-section-header">
                        <div class="lp-section-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <h5>{{ __('Statistics Settings') }}</h5>
                            <span>{{ __('Display numbers on your landing page') }}</span>
                        </div>
                    </div>
                    <div class="lp-stats-grid">
                        <div class="lp-stat-card">
                            <div class="lp-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="lp-input-group">
                                <label>{{ __('Number of Users') }}</label>
                                <input type="number" name="landing_users_count"
                                       value="{{ $settings['landing_users_count'] ?? '' }}" class="form-control lp-input"
                                       min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="lp-stat-card">
                            <div class="lp-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                                <i class="fas fa-globe-americas"></i>
                            </div>
                            <div class="lp-input-group">
                                <label>{{ __('Number of Countries') }}</label>
                                <input type="number" name="landing_countries_count"
                                       value="{{ $settings['landing_countries_count'] ?? '' }}" class="form-control lp-input"
                                       min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="lp-stat-card">
                            <div class="lp-stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                                <i class="fas fa-broadcast-tower"></i>
                            </div>
                            <div class="lp-input-group">
                                <label>{{ __('Number of Live Streams') }}</label>
                                <input type="number" name="landing_live_count"
                                       value="{{ $settings['landing_live_count'] ?? '' }}" class="form-control lp-input"
                                       min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Social Media Settings --}}
                <div class="tab-pane" id="social" role="tabpanel" aria-labelledby="social-tab">
                    <div class="lp-section-header">
                        <div class="lp-section-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div>
                            <h5>{{ __('Social Media Settings') }}</h5>
                            <span>{{ __('Connect your social media accounts') }}</span>
                        </div>
                    </div>
                    <div class="lp-social-list">
                        <div class="lp-social-item">
                            <div class="lp-social-icon" style="background: #1877f2;">
                                <i class="fab fa-facebook-f"></i>
                            </div>
                            <div class="lp-input-group" style="flex:1;">
                                <label>{{ __('Facebook Link') }}</label>
                                <input type="url" name="facebook_link"
                                       value="{{ $settings['facebook_link'] ?? '' }}" class="form-control lp-input"
                                       placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <div class="lp-social-item">
                            <div class="lp-social-icon" style="background: #1da1f2;">
                                <i class="fab fa-twitter"></i>
                            </div>
                            <div class="lp-input-group" style="flex:1;">
                                <label>{{ __('Twitter Link') }}</label>
                                <input type="url" name="twitter_link"
                                       value="{{ $settings['twitter_link'] ?? '' }}" class="form-control lp-input"
                                       placeholder="https://twitter.com/...">
                            </div>
                        </div>
                        <div class="lp-social-item">
                            <div class="lp-social-icon" style="background: #25d366;">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="lp-input-group" style="flex:1;">
                                <label>{{ __('WhatsApp Link') }}</label>
                                <input type="url" name="whatsapp_link"
                                       value="{{ $settings['whatsapp_link'] ?? '' }}" class="form-control lp-input"
                                       placeholder="https://wa.me/...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-footer">
                    <button type="submit" class="btn lp-btn-save">
                        <i class="fas fa-save"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .lp-form-reset {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ── Section Header ────────────────────────────────────────── */
    .lp-section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark-mode .lp-section-header { border-bottom-color: rgba(255,255,255,0.06); }
    .lp-section-icon {
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
    .lp-section-header h5 {
        margin: 0 0 2px 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .dark-mode .lp-section-header h5 { color: #f1f5f9; }
    .lp-section-header span {
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Input Group ───────────────────────────────────────────── */
    .lp-input-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .lp-input-group label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }
    .dark-mode .lp-input-group label { color: #94a3b8; }
    .lp-input-group label i { font-size: 11px; opacity: 0.7; }
    .lp-input {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-size: 13px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        background: #f8fafc !important;
    }
    .dark-mode .lp-input {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }
    .lp-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none;
    }
    .lp-input-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* ── Stats Grid ────────────────────────────────────────────── */
    .lp-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    .lp-stat-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        padding: 24px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .dark-mode .lp-stat-card {
        background: #0f172a;
        border-color: rgba(255,255,255,0.06);
    }
    .lp-stat-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .lp-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .lp-stat-card .lp-input-group { width: 100%; }
    .lp-stat-card .lp-input { text-align: center; }

    /* ── Social List ───────────────────────────────────────────── */
    .lp-social-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .lp-social-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.3s ease;
    }
    .dark-mode .lp-social-item {
        background: #0f172a;
        border-color: rgba(255,255,255,0.06);
    }
    .lp-social-item:hover { border-color: #cbd5e1; }
    .lp-social-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }

    /* ── Footer ────────────────────────────────────────────────── */
    .lp-footer {
        display: flex;
        justify-content: flex-end;
        padding-top: 20px;
        margin-top: 10px;
        border-top: 1px solid #f1f5f9;
    }
    .dark-mode .lp-footer { border-top-color: rgba(255,255,255,0.06); }
    .lp-btn-save {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 10px 28px !important;
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
    .lp-btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(99,102,241,0.3);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 992px) {
        .lp-stats-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
        .lp-input-row { grid-template-columns: 1fr; }
        .lp-stats-grid { grid-template-columns: 1fr; }
        .lp-social-item { flex-direction: column; align-items: stretch; }
    }
</style>
