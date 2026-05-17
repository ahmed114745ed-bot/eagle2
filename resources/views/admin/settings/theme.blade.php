<div id="themeSettings" class="theme-settings settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-paint-brush"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Theme Settings') }}</h3>
            <p>{{ __('Customize colors, appearance and visual style') }}</p>
        </div>
    </div>
    <form id="themeSettingsForm" action="{{ route('admin.app.settings.update') }}" method="POST"
          enctype="multipart/form-data" class="settings-form">
        @csrf
        <input type="hidden" name="current_tab" value="">

        {{-- ══════════════════════════════════════════════════════════
             DARK MODE TOGGLE
        ══════════════════════════════════════════════════════════ --}}
        <div class="ts-dark-mode-card">
            <div class="ts-dark-mode-inner">
                <div class="ts-dark-mode-left">
                    <div class="ts-dark-mode-icon-wrap">
                        <span class="ts-dark-mode-sun"><i class="fas fa-sun"></i></span>
                        <span class="ts-dark-mode-moon"><i class="fas fa-moon"></i></span>
                    </div>
                    <div class="ts-dark-mode-text">
                        <h5>{{ __('Dark Mode') }}</h5>
                        <span>{{ __('Toggle dark/light theme') }}</span>
                    </div>
                </div>
                <div class="ts-dark-mode-switch">
                    <input type="hidden" name="dark_mode" id="dark_mode_input" value="0">
                    <input type="checkbox" name="dark_mode" value="1" data-bootstrap-switch
                        {{ !empty($settings['dark_mode']) && $settings['dark_mode'] ? 'checked' : '' }}>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             PRESET THEMES
        ══════════════════════════════════════════════════════════ --}}
        <div class="ts-section">
            <div class="ts-section-header">
                <div class="ts-section-icon"><i class="fas fa-palette"></i></div>
                <div>
                    <h5>{{ __('Preset Themes') }}</h5>
                    <span>{{ __('Quick apply a complete color scheme') }}</span>
                </div>
            </div>
            <div class="ts-presets-grid" id="colorPresets">
                {{-- Blue Ocean --}}
                <div class="ts-preset-card" onclick="applyPreset('blue-ocean')" data-preset="blue-ocean">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #3b82f6, #0ea5e9);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #3b82f6;"></span>
                            <span style="background: #0ea5e9;"></span>
                            <span style="background: #e0f2fe;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Blue Ocean</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>

                {{-- Purple Dream --}}
                <div class="ts-preset-card" onclick="applyPreset('purple-dream')" data-preset="purple-dream">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #8b5cf6, #a855f7);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #8b5cf6;"></span>
                            <span style="background: #a855f7;"></span>
                            <span style="background: #f3e8ff;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Purple Dream</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>

                {{-- Sunset --}}
                <div class="ts-preset-card" onclick="applyPreset('sunset')" data-preset="sunset">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #f97316, #f59e0b);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #f97316;"></span>
                            <span style="background: #f59e0b;"></span>
                            <span style="background: #ffedd5;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Sunset</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>

                {{-- Forest --}}
                <div class="ts-preset-card" onclick="applyPreset('forest')" data-preset="forest">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #10b981, #059669);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #10b981;"></span>
                            <span style="background: #059669;"></span>
                            <span style="background: #d1fae5;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Forest</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>

                {{-- Royal --}}
                <div class="ts-preset-card" onclick="applyPreset('royal')" data-preset="royal">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #6366f1;"></span>
                            <span style="background: #8b5cf6;"></span>
                            <span style="background: #e0e7ff;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Royal</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>

                {{-- Dark Elegance --}}
                <div class="ts-preset-card" onclick="applyPreset('dark-elegance')" data-preset="dark-elegance">
                    <div class="ts-preset-colors">
                        <div class="ts-preset-color-main" style="background: linear-gradient(135deg, #1f2937, #374151);"></div>
                        <div class="ts-preset-color-dots">
                            <span style="background: #1f2937;"></span>
                            <span style="background: #374151;"></span>
                            <span style="background: #f3f4f6;"></span>
                        </div>
                    </div>
                    <div class="ts-preset-info">
                        <span class="ts-preset-name">Dark Elegance</span>
                        <i class="fas fa-check ts-preset-check"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             COLOR SETTINGS
        ══════════════════════════════════════════════════════════ --}}
        <div class="ts-section">
            <div class="ts-section-header">
                <div class="ts-section-icon"><i class="fas fa-eye-dropper"></i></div>
                <div>
                    <h5>{{ __('Color Settings') }}</h5>
                    <span>{{ __('Fine-tune individual color values') }}</span>
                </div>
            </div>
            <div class="ts-colors-grid">
                {{-- Primary Color --}}
                <div class="ts-color-card">
                    <label class="ts-color-label" for="secondary_color">{{ __('Primary Color') }}</label>
                    <div class="ts-color-picker-wrap">
                        <div class="ts-color-preview-ring">
                            <input type="color" id="secondary_color" name="secondary_color"
                                   value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                   class="ts-color-input-native">
                        </div>
                        <input type="text" id="secondary_color_text" class="ts-color-hex-input form-control"
                               value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                               placeholder="#FFFFFF" readonly>
                    </div>
                </div>

                {{-- Secondary Color --}}
                <div class="ts-color-card">
                    <label class="ts-color-label" for="primary_color">{{ __('Secondary Color') }}</label>
                    <div class="ts-color-picker-wrap">
                        <div class="ts-color-preview-ring">
                            <input type="color" id="primary_color" name="primary_color"
                                   value="{{ $settings['primary_color'] ?? '#000000' }}"
                                   class="ts-color-input-native">
                        </div>
                        <input type="text" id="primary_color_text" class="ts-color-hex-input form-control"
                               value="{{ $settings['primary_color'] ?? '#000000' }}"
                               placeholder="#000000" readonly>
                    </div>
                </div>

                {{-- Text Secondary Color --}}
                <div class="ts-color-card">
                    <label class="ts-color-label" for="text_secondary_color">{{ __('Text Secondary Color') }}</label>
                    <div class="ts-color-picker-wrap">
                        <div class="ts-color-preview-ring">
                            <input type="color" id="text_secondary_color" name="text_secondary_color"
                                   value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                   class="ts-color-input-native">
                        </div>
                        <input type="text" id="text_secondary_color_text" class="ts-color-hex-input form-control"
                               value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                               placeholder="#808080" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             BRAND BACKGROUND
        ══════════════════════════════════════════════════════════ --}}
        <div class="ts-section">
            <div class="ts-section-header">
                <div class="ts-section-icon"><i class="fas fa-image"></i></div>
                <div>
                    <h5>{{ __('Brand Background') }}</h5>
                    <span>{{ __('Set background color or image for branding') }}</span>
                </div>
            </div>
            <div class="ts-brand-grid">
                {{-- Background Type --}}
                <div class="ts-brand-type-card">
                    <label class="ts-color-label" for="brand_background_type">{{ __('Background Type') }}</label>
                    <div class="ts-select-wrap">
                        <select id="brand_background_type" name="brand_background_type" class="form-control ts-select"
                                onchange="toggleBrandBackgroundInput()">
                            <option value="color"
                                {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'selected' : '' }}>
                                <i class="fas fa-fill-drip"></i> {{ __('Color') }}
                            </option>
                            <option value="image"
                                {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'selected' : '' }}>
                                {{ __('Image') }}
                            </option>
                        </select>
                        <div class="ts-select-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                </div>

                {{-- Box Background Color --}}
                <div class="ts-brand-type-card" id="brand_background_color_group"
                     style="display: {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                    <label class="ts-color-label" for="box_background_color">{{ __('Box Background Color') }}</label>
                    <div class="ts-color-picker-wrap">
                        <div class="ts-color-preview-ring">
                            <input type="color" id="box_background_color" name="box_background_color"
                                   value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                   class="ts-color-input-native">
                        </div>
                        <input type="text" id="box_background_color_text" class="ts-color-hex-input form-control"
                               value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                               placeholder="#F8F9FA" readonly>
                    </div>
                </div>

                <input type="hidden" name="brand_image" id="brand_image">

                {{-- Brand Background Image --}}
                <div class="ts-brand-type-card" id="brand_background_image_group"
                     style="display: {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                    <label class="ts-color-label" for="brand_background_image">{{ __('Brand Background Image') }}</label>
                    <div class="ts-file-upload-wrap">
                        <input onchange="choose_image()" type="file" id="brand_background_image"
                               name="brand_background_image" class="ts-file-input">
                        <div class="ts-file-upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>{{ __('Choose image or drag here') }}</span>
                        </div>
                    </div>
                    <div class="ts-brand-images-gallery">
                        @foreach ($brand_images as $img)
                            <div class="ts-brand-img-thumb @if (!empty($settings['brand_background_image']) && $img->name == $settings['brand_background_image']) active @endif"
                                 onclick="select_brand_image('{{ $img->name }}', this)">
                                <img class="image_success @if (!empty($settings['brand_background_image']) && $img->name == $settings['brand_background_image']) border-success @endif"
                                     src="{{ !empty($img->name) ? getImagePath($img->name) : '' }}" alt="">
                                <div class="ts-thumb-check"><i class="fas fa-check"></i></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             ACTION BUTTONS
        ══════════════════════════════════════════════════════════ --}}
        <div class="ts-actions">
            <button type="submit" class="btn ts-btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <button type="button" id="resetColors" class="btn ts-btn-reset">
                <i class="fas fa-undo"></i> {{ __('Reset Colors') }}
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCOPED STYLES
═══════════════════════════════════════════════════════════════ --}}
<style>
    /* ── Dark Mode Toggle Card ─────────────────────────────────── */
    .ts-dark-mode-card {
        margin-bottom: 28px;
    }
    .ts-dark-mode-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    .dark-mode .ts-dark-mode-inner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-color: rgba(255,255,255,0.08);
    }
    .ts-dark-mode-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .ts-dark-mode-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(245,158,11,0.3);
    }
    .dark-mode .ts-dark-mode-icon-wrap {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }
    .ts-dark-mode-sun { display: block; }
    .ts-dark-mode-moon { display: none; }
    .dark-mode .ts-dark-mode-sun { display: none; }
    .dark-mode .ts-dark-mode-moon { display: block; }
    .ts-dark-mode-text h5 {
        margin: 0 0 2px 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .dark-mode .ts-dark-mode-text h5 {
        color: #f1f5f9;
    }
    .ts-dark-mode-text span {
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Section Container ─────────────────────────────────────── */
    .ts-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    .dark-mode .ts-section {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .ts-section-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark-mode .ts-section-header {
        border-bottom-color: rgba(255,255,255,0.06);
    }
    .ts-section-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(99,102,241,0.25);
    }
    .ts-section-header h5 {
        margin: 0 0 2px 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
    .dark-mode .ts-section-header h5 {
        color: #f1f5f9;
    }
    .ts-section-header span {
        font-size: 13px;
        color: #94a3b8;
    }

    /* ── Preset Themes Grid ────────────────────────────────────── */
    .ts-presets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 16px;
    }
    .ts-preset-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: #fff;
    }
    .dark-mode .ts-preset-card {
        border-color: rgba(255,255,255,0.08);
        background: #0f172a;
    }
    .ts-preset-card:hover {
        border-color: #6366f1;
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(99,102,241,0.15);
    }
    .ts-preset-card.active {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.2), 0 8px 24px rgba(99,102,241,0.12);
    }
    .dark-mode .ts-preset-card:hover,
    .dark-mode .ts-preset-card.active {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(129,140,248,0.15), 0 8px 24px rgba(0,0,0,0.3);
    }
    .ts-preset-colors {
        padding: 0;
    }
    .ts-preset-color-main {
        height: 72px;
        width: 100%;
    }
    .ts-preset-color-dots {
        display: flex;
        gap: 6px;
        padding: 10px 14px;
        justify-content: center;
    }
    .ts-preset-color-dots span {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #f1f5f9;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .dark-mode .ts-preset-color-dots span {
        border-color: rgba(255,255,255,0.1);
    }
    .ts-preset-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px 14px;
    }
    .ts-preset-name {
        font-weight: 600;
        font-size: 13px;
        color: #475569;
    }
    .dark-mode .ts-preset-name {
        color: #cbd5e1;
    }
    .ts-preset-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #6366f1;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        line-height: 22px;
        text-align: center;
    }
    .ts-preset-card.active .ts-preset-check {
        display: flex;
    }

    /* ── Color Picker Cards ────────────────────────────────────── */
    .ts-colors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 16px;
    }
    .ts-color-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        transition: all 0.3s ease;
    }
    .dark-mode .ts-color-card {
        background: #0f172a;
        border-color: rgba(255,255,255,0.06);
    }
    .ts-color-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .dark-mode .ts-color-card:hover {
        border-color: rgba(255,255,255,0.12);
    }
    .ts-color-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 12px;
        letter-spacing: 0.02em;
    }
    .dark-mode .ts-color-label {
        color: #cbd5e1;
    }
    .ts-color-picker-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ts-color-preview-ring {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        padding: 3px;
        background: conic-gradient(from 0deg, #f87171, #fbbf24, #34d399, #60a5fa, #a78bfa, #f87171);
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }
    .ts-color-preview-ring:hover {
        transform: scale(1.08);
    }
    .ts-color-input-native {
        width: 100%;
        height: 100%;
        border: 3px solid #fff;
        border-radius: 50%;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding: 0;
        background: none;
    }
    .ts-color-input-native::-webkit-color-swatch-wrapper {
        padding: 0;
    }
    .ts-color-input-native::-webkit-color-swatch {
        border: none;
        border-radius: 50%;
    }
    .ts-color-input-native::-moz-color-swatch {
        border: none;
        border-radius: 50%;
    }
    .dark-mode .ts-color-input-native {
        border-color: #1e293b;
    }
    .ts-color-hex-input {
        flex: 1;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-family: 'SF Mono', 'Fira Code', monospace;
        font-size: 14px !important;
        font-weight: 600;
        color: #334155 !important;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background: #fff !important;
    }
    .dark-mode .ts-color-hex-input {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0 !important;
    }

    /* ── Brand Background ──────────────────────────────────────── */
    .ts-brand-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }
    .ts-brand-type-card {
        padding: 0;
    }
    .ts-select-wrap {
        position: relative;
    }
    .ts-select {
        appearance: none;
        -webkit-appearance: none;
        padding: 12px 40px 12px 16px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        font-size: 14px;
        font-weight: 500;
        background: #f8fafc !important;
        color: #334155;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .dark-mode .ts-select {
        background: #0f172a !important;
        border-color: rgba(255,255,255,0.08) !important;
        color: #e2e8f0;
    }
    .ts-select:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        outline: none;
    }
    .ts-select-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 12px;
    }
    .rtl .ts-select-icon {
        right: auto;
        left: 14px;
    }

    /* ── File Upload ───────────────────────────────────────────── */
    .ts-file-upload-wrap {
        position: relative;
        margin-bottom: 14px;
    }
    .ts-file-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
    .ts-file-upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 28px 20px;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        color: #94a3b8;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    .dark-mode .ts-file-upload-placeholder {
        border-color: rgba(255,255,255,0.1);
        background: #0f172a;
    }
    .ts-file-upload-wrap:hover .ts-file-upload-placeholder {
        border-color: #6366f1;
        color: #6366f1;
        background: rgba(99,102,241,0.04);
    }
    .ts-file-upload-placeholder i {
        font-size: 28px;
    }
    .ts-file-upload-placeholder span {
        font-size: 13px;
        font-weight: 500;
    }

    /* ── Brand Images Gallery ──────────────────────────────────── */
    .ts-brand-images-gallery {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .ts-brand-img-thumb {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        position: relative;
        transition: all 0.3s ease;
    }
    .dark-mode .ts-brand-img-thumb {
        border-color: rgba(255,255,255,0.08);
    }
    .ts-brand-img-thumb:hover {
        border-color: #6366f1;
        transform: scale(1.08);
    }
    .ts-brand-img-thumb.active {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
    }
    .ts-brand-img-thumb img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        display: block !important;
    }
    .ts-thumb-check {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #6366f1;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 9px;
    }
    .ts-brand-img-thumb.active .ts-thumb-check {
        display: flex;
    }

    /* ── Action Buttons ────────────────────────────────────────── */
    .ts-actions {
        display: flex;
        gap: 12px;
        padding-top: 8px;
    }
    .ts-btn-save {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        padding: 12px 28px !important;
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
    .ts-btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(99,102,241,0.35);
        background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
    }
    .ts-btn-save:active {
        transform: translateY(0);
    }
    .ts-btn-reset {
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
    .dark-mode .ts-btn-reset {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: rgba(255,255,255,0.08) !important;
    }
    .ts-btn-reset:hover {
        background: #e2e8f0 !important;
        color: #1e293b !important;
        transform: translateY(-1px);
    }
    .dark-mode .ts-btn-reset:hover {
        background: #334155 !important;
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .ts-presets-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .ts-colors-grid {
            grid-template-columns: 1fr;
        }
        .ts-brand-grid {
            grid-template-columns: 1fr;
        }
        .ts-actions {
            flex-direction: column;
        }
        .ts-btn-save,
        .ts-btn-reset {
            width: 100% !important;
            justify-content: center;
        }
    }
    @media (max-width: 480px) {
        .ts-presets-grid {
            grid-template-columns: 1fr;
        }
        .ts-section {
            padding: 16px;
        }
    }
</style>

{{-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════════ --}}
<script>
    const themePresets = {
        'blue-ocean': {
            secondary_color: '#3b82f6',
            primary_color: '#0ea5e9',
            text_secondary_color: '#e0f2fe'
        },
        'purple-dream': {
            secondary_color: '#8b5cf6',
            primary_color: '#a855f7',
            text_secondary_color: '#f3e8ff'
        },
        'sunset': {
            secondary_color: '#f97316',
            primary_color: '#f59e0b',
            text_secondary_color: '#ffedd5'
        },
        'forest': {
            secondary_color: '#10b981',
            primary_color: '#059669',
            text_secondary_color: '#d1fae5'
        },
        'royal': {
            secondary_color: '#6366f1',
            primary_color: '#8b5cf6',
            text_secondary_color: '#e0e7ff'
        },
        'dark-elegance': {
            secondary_color: '#1f2937',
            primary_color: '#374151',
            text_secondary_color: '#f3f4f6'
        }
    };

    function applyPreset(presetName) {
        const preset = themePresets[presetName];
        if (!preset) return;

        document.querySelectorAll('.ts-preset-card').forEach(card => {
            card.classList.remove('active');
        });

        const clicked = document.querySelector('[data-preset="' + presetName + '"]');
        if (clicked) clicked.classList.add('active');

        updateColorInput('secondary_color', preset.secondary_color);
        updateColorInput('primary_color', preset.primary_color);
        updateColorInput('text_secondary_color', preset.text_secondary_color);
    }

    function updateColorInput(inputId, color) {
        const colorInput = document.getElementById(inputId);
        const textInput = document.getElementById(inputId + '_text');

        if (colorInput) colorInput.value = color;
        if (textInput) textInput.value = color;
    }

    function initColorSync() {
        ['secondary_color', 'primary_color', 'text_secondary_color', 'box_background_color'].forEach(id => {
            const colorInput = document.getElementById(id);
            const textInput = document.getElementById(id + '_text');

            if (colorInput && textInput) {
                colorInput.addEventListener('input', (e) => {
                    textInput.value = e.target.value;
                });
            }
        });
    }

    function initDarkModeSwitch() {
        const $switch = $('input[name="dark_mode"][data-bootstrap-switch]');
        const $input = document.getElementById('dark_mode_input');

        const userPreference = localStorage.getItem('admin_dark_mode');
        let initialState = false;

        if (userPreference !== null) {
            initialState = userPreference === '1';
        } else {
            initialState = $switch.is(':checked');
        }

        $switch.each(function () {
            $(this).bootstrapSwitch('state', initialState, true);
        });

        $switch.on('switchChange.bootstrapSwitch', function (event, state) {
            const value = state ? '1' : '0';
            if ($input) $input.value = value;
            document.documentElement.classList.toggle('dark-mode', state);
            localStorage.setItem('admin_dark_mode', value);
        });

        document.documentElement.classList.toggle('dark-mode', initialState);
    }

    $(document).ready(function() {
        initDarkModeSwitch();
        initColorSync();
    });

    $(document).on('pjax:success', function() {
        initDarkModeSwitch();
        initColorSync();
    });
</script>
