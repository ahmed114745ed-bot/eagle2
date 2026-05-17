<div id="appSettings" class="app-settings settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-sliders-h"></i></div>
        <div class="section-header-text">
            <h3>{{ __('App Settings') }}</h3>
            <p>{{ __('General application configuration and preferences') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.app-config.update') }}" method="POST" enctype="multipart/form-data" class="settings-form modern-form">
        @csrf
        <input type="hidden" name="reset" id="reset" value="3">

        {{-- ══════════════════════════════════════════════════════════
             PRIMARY & BACKGROUND
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                    <i class="fas fa-palette"></i>
                </div>
                <div>
                    <h5>{{ __('Primary & Background') }}</h5>
                    <span>{{ __('Set the main color and background style for your app') }}</span>
                </div>
            </div>

            <div class="as-fields-grid">
                {{-- Primary Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                            <i class="fas fa-tint"></i>
                        </div>
                        <label class="as-field-label">{{ __('Primary Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'app_primary_color', '#32e5ac') }};"></i>
                        </span>
                        <input type="text" name="app_primary_color" id="app_primary_color" class="form-control"
                               value="{{ data_get($settings, 'app_primary_color', '#32e5ac') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>

                {{-- Background Type --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Type') }}</label>
                    </div>
                    <div class="as-select-wrap">
                        <select name="background_type" id="background_type" class="form-control as-select" onchange="toggleBackgroundInput()">
                            <option value="color" {{ data_get($settings, 'background_type') === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                            <option value="image" {{ data_get($settings, 'background_type') === 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                            <option value="gradient" {{ data_get($settings, 'background_type') === 'gradient' ? 'selected' : '' }}>{{ __('Gradient') }}</option>
                        </select>
                        <div class="as-select-chevron"><i class="fas fa-chevron-down"></i></div>
                    </div>
                </div>

                {{-- Background Color --}}
                <div class="as-field-card" id="background_color_group"
                     style="display: {{ data_get($settings, 'background_type') === 'color' ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                            <i class="fas fa-fill-drip"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_color" id="background_color" class="form-control"
                               value="{{ data_get($settings, 'background_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                {{-- Background Image --}}
                <div class="as-field-card" id="background_image_group"
                     style="display: {{ data_get($settings, 'background_type') === 'image' ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                            <i class="fas fa-image"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Image') }}</label>
                    </div>
                    <div class="as-file-upload-wrap">
                        <input type="file" name="background_image" class="as-file-input" accept="image/*">
                        <div class="as-file-upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>{{ __('Choose image or drag here') }}</span>
                        </div>
                    </div>
                    @if(data_get($settings, 'background_type') === 'image' && !empty(data_get($settings, 'app_background')))
                        <div class="as-img-preview">
                            <img src="{{ getImagePath(data_get($settings, 'app_background')) }}" alt="">
                            <span>{{ __('Current Background') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             NAVIGATION COLORS
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                    <i class="fas fa-bars"></i>
                </div>
                <div>
                    <h5>{{ __('Navigation Colors') }}</h5>
                    <span>{{ __('Customize bottom navigation bar colors') }}</span>
                </div>
            </div>

            <div class="as-fields-grid">
                {{-- Bottom Nav Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                            <i class="fas fa-square"></i>
                        </div>
                        <label class="as-field-label">{{ __('Bottom Nav Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_bottom_color', '') }};"></i>
                        </span>
                        <input type="text" name="bottom_color" id="bottom_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_bottom_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                {{-- Bottom Nav Active Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <label class="as-field-label">{{ __('Bottom Nav Active Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_active_color', '') }};"></i>
                        </span>
                        <input type="text" name="active_color" id="active_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_active_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                {{-- Bottom Nav Inactive Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #94a3b8, #64748b);">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <label class="as-field-label">{{ __('Bottom Nav Inactive Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_inactive_color', '') }};"></i>
                        </span>
                        <input type="text" name="inactive_color" id="inactive_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_inactive_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TEXT & BUTTON COLORS
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                    <i class="fas fa-font"></i>
                </div>
                <div>
                    <h5>{{ __('Text & Button Colors') }}</h5>
                    <span>{{ __('Control text headers and button appearance') }}</span>
                </div>
            </div>

            <div class="as-fields-grid">
                {{-- Text Header Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #1e293b, #475569);">
                            <i class="fas fa-heading"></i>
                        </div>
                        <label class="as-field-label">{{ __('Text Header Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'text_header_color', '#000000') }};"></i>
                        </span>
                        <input type="text" name="text_header_color" id="text_header_color" class="form-control"
                               value="{{ data_get($settings, 'text_header_color', '#000000') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                {{-- Button Text Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <label class="as-field-label">{{ __('Button Text Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'button_text_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="button_text_color" id="button_text_color" class="form-control"
                               value="{{ data_get($settings, 'button_text_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             DARK / LIGHT MODE
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon" style="background: linear-gradient(135deg, #334155, #1e293b);">
                    <i class="fas fa-moon"></i>
                </div>
                <div>
                    <h5>{{ __('Dark / Light Mode') }}</h5>
                    <span>{{ __('Configure dark and light mode body colors') }}</span>
                </div>
            </div>

            <div class="as-fields-grid">
                {{-- Dark Body Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                            <i class="fas fa-moon"></i>
                        </div>
                        <label class="as-field-label">{{ __('Dark Body Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'dark_mode_color', '') }};"></i>
                        </span>
                        <input type="text" name="dark_mode_color" id="dark_mode_color" class="form-control"
                               value="{{ data_get($settings, 'dark_mode_color', '') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>

                {{-- Light Body Color --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                            <i class="fas fa-sun"></i>
                        </div>
                        <label class="as-field-label">{{ __('Light Body Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'light_mode_color', '') }};"></i>
                        </span>
                        <input type="text" name="light_mode_color" id="light_mode_color" class="form-control"
                               value="{{ data_get($settings, 'light_mode_color', '') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>

                {{-- Dark Mode Enabled Toggle --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                            <i class="fas fa-toggle-on"></i>
                        </div>
                        <label class="as-field-label">{{ __('Dark Mode Enabled') }}</label>
                    </div>
                    <div class="as-switch-wrap">
                        <input type="hidden" name="is_dark_mode_enabled" value="0">
                        <input type="checkbox" name="is_dark_mode_enabled" value="1"
                               data-bootstrap-switch {{ data_get($settings, 'is_dark_mode_enabled') ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             BODY THEME
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-section">
            <div class="as-section-header">
                <div class="as-section-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                    <i class="fas fa-swatchbook"></i>
                </div>
                <div>
                    <h5>{{ __('Body Theme') }}</h5>
                    <span>{{ __('Advanced body background customization') }}</span>
                </div>
            </div>

            <div class="as-fields-grid">
                {{-- Body Theme Enable --}}
                <div class="as-field-card">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-power-off"></i>
                        </div>
                        <label class="as-field-label">{{ __('Body Theme Enable') }}</label>
                    </div>
                    <div class="as-switch-wrap">
                        <input type="hidden" name="is_body_theme_enabled" value="0">
                        <input type="checkbox" name="is_body_theme_enabled" id="is_body_theme_enabled" value="1"
                               data-bootstrap-switch {{ data_get($settings, 'is_body_theme_enabled') ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Background Body Theme Type --}}
                <div class="as-field-card" id="background_body_theme_group"
                     style="display: {{ data_get($settings, 'is_body_theme_enabled') ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Body Theme') }}</label>
                    </div>
                    <div class="as-select-wrap">
                        <select name="background_body_theme" id="background_body_theme" class="form-control as-select" onchange="toggleBodyThemeBackgroundInput()">
                            <option value="color" {{ data_get($settings, 'background_body_theme') === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                            <option value="image" {{ data_get($settings, 'background_body_theme') === 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                            <option value="gradient" {{ data_get($settings, 'background_body_theme') === 'gradient' ? 'selected' : '' }}>{{ __('Gradient') }}</option>
                        </select>
                        <div class="as-select-chevron"><i class="fas fa-chevron-down"></i></div>
                    </div>
                </div>

                {{-- Body Theme Color --}}
                <div class="as-field-card" id="background_body_theme_color_group"
                     style="display: {{ data_get($settings, 'background_body_theme') === 'color' ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);">
                            <i class="fas fa-fill-drip"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Body Theme Color') }}</label>
                    </div>
                    <div class="input-group colorpicker-element as-colorpicker">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_body_theme_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_body_theme_color" id="background_body_theme_color" class="form-control"
                               value="{{ data_get($settings, 'background_body_theme_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                {{-- Body Theme Image --}}
                <div class="as-field-card" id="background_body_theme_image_group"
                     style="display: {{ data_get($settings, 'background_body_theme') === 'image' ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                            <i class="fas fa-image"></i>
                        </div>
                        <label class="as-field-label">{{ __('Background Body Theme Image') }}</label>
                    </div>
                    <div class="as-file-upload-wrap">
                        <input type="file" name="background_body_theme_image" class="as-file-input" accept="image/*">
                        <div class="as-file-upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>{{ __('Choose image or drag here') }}</span>
                        </div>
                    </div>
                    @if(data_get($settings, 'background_body_theme') === 'image' && !empty(data_get($settings, 'background_body_theme_image')))
                        <div class="as-img-preview">
                            <img src="{{ getImagePath(data_get($settings, 'background_body_theme_image')) }}" alt="">
                            <span>{{ __('Current Image') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Gradient Colors --}}
                <div class="as-field-card as-gradient-card" id="background_body_theme_gradient_group"
                     style="display: {{ data_get($settings, 'background_body_theme') === 'gradient' ? 'block' : 'none' }};">
                    <div class="as-field-top">
                        <div class="as-field-icon" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                            <i class="fas fa-fill-drip"></i>
                        </div>
                        <label class="as-field-label">{{ __('Gradient Colors') }}</label>
                    </div>
                    <div class="as-gradient-inputs">
                        <div class="as-gradient-item">
                            <span class="as-gradient-num">1</span>
                            <div class="input-group colorpicker-element as-colorpicker">
                                <span class="input-group-addon">
                                    <i style="background-color: {{ data_get($settings, 'background_body_theme_color_one', '#ffffff') }};"></i>
                                </span>
                                <input type="text" name="background_body_theme_color_one" id="background_body_theme_color_one" class="form-control"
                                       value="{{ data_get($settings, 'background_body_theme_color_one', '#ffffff') }}" placeholder="{{ __('Color One') }}">
                            </div>
                        </div>
                        <div class="as-gradient-item">
                            <span class="as-gradient-num">2</span>
                            <div class="input-group colorpicker-element as-colorpicker">
                                <span class="input-group-addon">
                                    <i style="background-color: {{ data_get($settings, 'background_body_theme_color_two', '#ffffff') }};"></i>
                                </span>
                                <input type="text" name="background_body_theme_color_two" id="background_body_theme_color_two" class="form-control"
                                       value="{{ data_get($settings, 'background_body_theme_color_two', '#ffffff') }}" placeholder="{{ __('Color Two') }}">
                            </div>
                        </div>
                        <div class="as-gradient-item">
                            <span class="as-gradient-num">3</span>
                            <div class="input-group colorpicker-element as-colorpicker">
                                <span class="input-group-addon">
                                    <i style="background-color: {{ data_get($settings, 'background_body_theme_color_three', '#ffffff') }};"></i>
                                </span>
                                <input type="text" name="background_body_theme_color_three" id="background_body_theme_color_three" class="form-control"
                                       value="{{ data_get($settings, 'background_body_theme_color_three', '#ffffff') }}" placeholder="{{ __('Color Three') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- New Theme Enabled --}}
                @if ($isThemeEnabled)
                    <div class="as-field-card">
                        <div class="as-field-top">
                            <div class="as-field-icon" style="background: linear-gradient(135deg, #14b8a6, #2dd4bf);">
                                <i class="fas fa-magic"></i>
                            </div>
                            <label class="as-field-label">{{ __('New Theme Enabled') }}</label>
                        </div>
                        <div class="as-switch-wrap">
                            <input type="hidden" name="is_new_theme_enabled" value="0">
                            <input type="checkbox" name="is_new_theme_enabled" value="1"
                                   data-bootstrap-switch {{ data_get($settings, 'is_new_theme_enabled') ? 'checked' : '' }}>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             ACTION BUTTONS
        ══════════════════════════════════════════════════════════ --}}
        <div class="as-actions">
            <button type="submit" class="btn as-btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <button type="button" id="resetAppColorsSettings" class="btn as-btn-reset">
                <i class="fas fa-undo"></i> {{ __('Reset Colors') }}
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Section Container ─────────────────────────────────────── */
    .as-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    .dark-mode .as-section {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }

    /* ── Section Header ────────────────────────────────────────── */
    .as-section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark-mode .as-section-header {
        border-bottom-color: rgba(255,255,255,0.06);
    }
    .as-section-icon {
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
    .as-section-header h5 {
        margin: 0 0 2px 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
    .dark-mode .as-section-header h5 {
        color: #f1f5f9;
    }
    .as-section-header span {
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Fields Grid ───────────────────────────────────────────── */
    .as-fields-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    /* ── Field Card ────────────────────────────────────────────── */
    .as-field-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        transition: all 0.3s ease;
    }
    .dark-mode .as-field-card {
        background: #0f172a;
        border-color: rgba(255,255,255,0.06);
    }
    .as-field-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .dark-mode .as-field-card:hover {
        border-color: rgba(255,255,255,0.12);
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }
    .as-field-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .as-field-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 14px;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    .as-field-label {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin: 0 !important;
        letter-spacing: 0.02em;
    }
    .dark-mode .as-field-label {
        color: #cbd5e1;
    }

    /* ── Select Wrap ───────────────────────────────────────────── */
    .as-select-wrap {
        position: relative;
    }
    .as-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        padding: 12px 40px 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-size: 14px !important;
        font-weight: 500;
        background: #fff !important;
        color: #334155 !important;
        cursor: pointer;
        transition: all 0.3s ease;
        height: auto !important;
        line-height: 1.5 !important;
    }
    .dark-mode .as-select {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }
    .as-select:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1) !important;
        outline: none !important;
    }
    .as-select:hover {
        border-color: #cbd5e1 !important;
    }
    .dark-mode .as-select:hover {
        border-color: rgba(255,255,255,0.15) !important;
    }
    .as-select-chevron {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 12px;
    }
    .rtl .as-select-chevron {
        right: auto;
        left: 14px;
    }

    /* ── Colorpicker Override ──────────────────────────────────── */
    .as-colorpicker {
        border: 1px solid #e2e8f0;
        border-radius: 12px !important;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .dark-mode .as-colorpicker {
        border-color: rgba(255,255,255,0.08);
    }
    .as-colorpicker:hover {
        border-color: #cbd5e1;
    }
    .as-colorpicker .input-group-addon {
        background: #f8fafc;
        border: none;
        padding: 0 12px;
        display: flex;
        align-items: center;
    }
    .dark-mode .as-colorpicker .input-group-addon {
        background: rgba(255,255,255,0.04);
    }
    .as-colorpicker .input-group-addon i {
        width: 28px;
        height: 28px;
        display: block;
        border-radius: 8px;
        border: 2px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .as-colorpicker .form-control {
        border: none !important;
        font-weight: 500;
        letter-spacing: 0.03em;
    }

    /* ── Switch Wrap ───────────────────────────────────────────── */
    .as-switch-wrap {
        padding-top: 4px;
    }

    /* ── File Upload ───────────────────────────────────────────── */
    .as-file-upload-wrap {
        position: relative;
        margin-bottom: 12px;
    }
    .as-file-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
    .as-file-upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 24px 20px;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        color: #94a3b8;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    .dark-mode .as-file-upload-placeholder {
        border-color: rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.02);
    }
    .as-file-upload-wrap:hover .as-file-upload-placeholder {
        border-color: #6366f1;
        color: #6366f1;
        background: rgba(99,102,241,0.04);
    }
    .as-file-upload-placeholder i {
        font-size: 26px;
    }
    .as-file-upload-placeholder span {
        font-size: 13px;
        font-weight: 500;
    }

    /* ── Image Preview ─────────────────────────────────────────── */
    .as-img-preview {
        margin-top: 10px;
        text-align: center;
    }
    .as-img-preview img {
        width: 100% !important;
        max-width: 200px !important;
        height: auto !important;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .dark-mode .as-img-preview img {
        border-color: rgba(255,255,255,0.08);
    }
    .as-img-preview span {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #94a3b8;
        font-weight: 500;
    }

    /* ── Gradient Card ─────────────────────────────────────────── */
    .as-gradient-card {
        grid-column: span 2;
    }
    .as-gradient-inputs {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .as-gradient-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .as-gradient-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #818cf8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(99,102,241,0.3);
    }
    .as-gradient-item .as-colorpicker {
        flex: 1;
    }

    /* ── Action Buttons ────────────────────────────────────────── */
    .as-actions {
        display: flex;
        gap: 12px;
        padding-top: 8px;
    }
    .as-btn-save {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 12px 32px !important;
        background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 14px rgba(99,102,241,0.3);
        width: auto !important;
    }
    .as-btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(99,102,241,0.35);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }
    .as-btn-save:active {
        transform: translateY(0);
    }
    .as-btn-reset {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 12px 28px !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.3s ease;
        width: auto !important;
    }
    .dark-mode .as-btn-reset {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: rgba(255,255,255,0.08) !important;
    }
    .as-btn-reset:hover {
        background: #e2e8f0 !important;
        color: #1e293b !important;
        transform: translateY(-1px);
    }
    .dark-mode .as-btn-reset:hover {
        background: #334155 !important;
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .as-fields-grid {
            grid-template-columns: 1fr;
        }
        .as-gradient-card {
            grid-column: span 1;
        }
        .as-actions {
            flex-direction: column;
        }
        .as-btn-save,
        .as-btn-reset {
            width: 100% !important;
            justify-content: center;
        }
        .as-section {
            padding: 16px;
        }
    }
</style>
