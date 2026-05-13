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

        <div class="dark-mode-card mb-4">
            <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(255,255,255,0.05);border-radius: 16px;border: 1px solid #eaeaea;box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                <div class="d-flex align-items-center gap-3">
                    <div class="dark-mode-icon" style="font-size: 24px;">🌙</div>
                    <div>
                        <h5 class="m-0" style="color: black">{{ __('Dark Mode') }}</h5>
                        <small style="color: #888;">{{ __('Toggle dark/light theme') }}</small>
                    </div>
                </div>
                <div>
                    <input type="hidden" name="dark_mode" id="dark_mode_input" value="0">
                    <input type="checkbox" name="dark_mode" value="1" data-bootstrap-switch
                        {{ !empty($settings['dark_mode']) && $settings['dark_mode'] ? 'checked' : '' }}>
                </div>
            </div>
        </div>

        <!-- قسم الثيمات الجاهزة - NEW SECTION -->
        <div class="presets-section mb-4">
            <h5 class="mb-3" style="border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 10px; color: #333;">
                {{ __('Preset Themes') }}
            </h5>
            <div class="row g-3" id="colorPresets">
                <!-- Blue Ocean Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('blue-ocean')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Blue Ocean
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #3b82f6; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #0ea5e9; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #e0f2fe; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>

                <!-- Purple Dream Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('purple-dream')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Purple Dream
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #8b5cf6; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #a855f7; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f3e8ff; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>

                <!-- Sunset Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('sunset')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Sunset
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f97316; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f59e0b; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #ffedd5; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>

                <!-- Forest Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('forest')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Forest
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #10b981; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #059669; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #d1fae5; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>

                <!-- Royal Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('royal')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Royal
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #6366f1; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #8b5cf6; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #e0e7ff; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>

                <!-- Dark Elegance Theme -->
                <div class="col-md-4">
                    <div class="preset-card" onclick="applyPreset('dark-elegance')"
                         style="padding: 15px; border-radius: 12px; border: 2px solid #e5e7eb; cursor: pointer; transition: all 0.3s;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #333; font-size: 14px;">
                            Dark Elegance
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #1f2937; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #374151; border: 2px solid #e5e7eb;"></div>
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #f3f4f6; border: 2px solid #e5e7eb;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="colors-section mb-4">
            <h5 class="mb-3" style="border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 10px;">
                {{ __('Color Settings') }}
            </h5>
            <div class="form row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="secondary_color">{{ __('Primary Color:') }}</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="secondary_color" name="secondary_color"
                                   value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                   style="width: 50px; height: 45px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                            <input type="text" id="secondary_color_text" class="form-control"
                                   value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}"
                                   placeholder="ادخل لون" style="flex: 1;" readonly>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="primary_color">{{ __('Secondary Color:') }}</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="primary_color" name="primary_color"
                                   value="{{ $settings['primary_color'] ?? '#000000' }}"
                                   style="width: 50px; height: 45px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                            <input type="text" id="primary_color_text" class="form-control"
                                   value="{{ $settings['primary_color'] ?? '#000000' }}"
                                   placeholder="ادخل لون" style="flex: 1;" readonly>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="text_secondary_color" name="text_secondary_color"
                                   value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                   style="width: 50px; height: 45px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                            <input type="text" id="text_secondary_color_text" class="form-control"
                                   value="{{ $settings['text_secondary_color'] ?? '#808080' }}"
                                   placeholder="ادخل لون" style="flex: 1;" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-section mb-4">
            <h5 class="mb-3" style="border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 10px;">
                {{ __('Brand Background') }}
            </h5>
            <div class="form row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="brand_background_type">{{ __('Brand Background Type') }}</label>
                        <select id="brand_background_type" name="brand_background_type" class="form-control"
                                onchange="toggleBrandBackgroundInput()">
                            <option value="color"
                                {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'selected' : '' }}>
                                {{ __('Color') }}
                            </option>
                            <option value="image"
                                {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'selected' : '' }}>
                                {{ __('Image') }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group" id="brand_background_color_group"
                         style="display: {{ ($settings['brand_background_type'] ?? 'color') === 'color' ? 'block' : 'none' }};">
                        <label for="box_background_color">{{ __('Box Background Color:') }}</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="box_background_color" name="box_background_color"
                                   value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                   style="width: 50px; height: 45px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer;">
                            <input type="text" id="box_background_color_text" class="form-control"
                                   value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}"
                                   placeholder="ادخل لون" style="flex: 1;" readonly>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="brand_image" id="brand_image">

                <div class="col-md-6">
                    <div class="form-group" id="brand_background_image_group"
                         style="display: {{ ($settings['brand_background_type'] ?? '') === 'image' ? 'block' : 'none' }};">
                        <label for="brand_background_image">{{ __('Brand Background Image') }}</label>
                        <input onchange="choose_image()" type="file" id="brand_background_image"
                               name="brand_background_image" class="form-control">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            @foreach ($brand_images as $img)
                                <img
                                    class="image_success @if (!empty($settings['brand_background_image']) && $img->name == $settings['brand_background_image']) border-success @endif"
                                    onclick="select_brand_image('{{ $img->name }}', this)"
                                    src="{{ !empty($img->name) ? getImagePath($img->name) : '' }}"
                                    alt="" style="width: 50px; height: 50px; cursor: pointer; border-radius: 5px;">
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons"
             style="border-top: 1px solid rgba(0,0,0,0.1); padding-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <button type="button" id="resetColors" class="btn btn-info">
                <i class="fas fa-undo"></i> {{ __('Reset Colors') }}
            </button>
        </div>
    </form>
</div>

<style>
    .preset-card:hover {
        border-color: black !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
    }

    .dark-mode .preset-card:hover {
        border-color: var(--dark-secondry-color) !important;
    }

    .preset-card.active {
        border-color: black !important;
    }

    .dark-mode .preset-card.active {
        border-color: var(--dark-secondry-color) !important;
    }
</style>

<script>
    const themePresets = {
        'blue-ocean': {
            secondary_color: '#3b82f6',
            primary_color: '#0ea5e9',
            text_secondary_color: '#e0f2fe'  // Light blue tint
        },
        'purple-dream': {
            secondary_color: '#8b5cf6',
            primary_color: '#a855f7',
            text_secondary_color: '#f3e8ff'  // Light purple tint
        },
        'sunset': {
            secondary_color: '#f97316',
            primary_color: '#f59e0b',
            text_secondary_color: '#ffedd5'  // Light orange tint
        },
        'forest': {
            secondary_color: '#10b981',
            primary_color: '#059669',
            text_secondary_color: '#d1fae5'  // Light green tint
        },
        'royal': {
            secondary_color: '#6366f1',
            primary_color: '#8b5cf6',
            text_secondary_color: '#e0e7ff'  // Light indigo tint
        },
        'dark-elegance': {
            secondary_color: '#1f2937',
            primary_color: '#374151',
            text_secondary_color: '#f3f4f6'  // Light gray
        }
    };
    function applyPreset(presetName) {
        const preset = themePresets[presetName];
        if (!preset) return;

        document.querySelectorAll('.preset-card').forEach(card => {
            card.classList.remove('active');
        });

        event.currentTarget.classList.add('active');

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

        // Check localStorage first for user preference
        const userPreference = localStorage.getItem('admin_dark_mode');
        let initialState = false;

        if (userPreference !== null) {
            // User has set a preference, use it
            initialState = userPreference === '1';
        } else {
            // No user preference, use the switch state (which comes from database)
            initialState = $switch.is(':checked');
        }

        $switch.each(function () {
            $(this).bootstrapSwitch('state', initialState, true);
        });

        $switch.on('switchChange.bootstrapSwitch', function (event, state) {
            const value = state ? '1' : '0';
            if ($input) $input.value = value;
            document.documentElement.classList.toggle('dark-mode', state);
            // Update localStorage when user changes via settings
            localStorage.setItem('admin_dark_mode', value);
        });

        // Apply the initial state
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
