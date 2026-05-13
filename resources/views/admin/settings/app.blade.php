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

        {{-- Primary & Background Section --}}
        <div class="form-section-title"><i class="fas fa-palette"></i> {{ __('Primary & Background') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-tint text-muted"></i> {{ __('Primary Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'app_primary_color', '#32e5ac') }};"></i>
                        </span>
                        <input type="text" name="app_primary_color" id="app_primary_color" class="form-control"
                               value="{{ data_get($settings, 'app_primary_color', '#32e5ac') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-layer-group text-muted"></i> {{ __('Background Type') }}</label>
                    <select name="background_type" id="background_type" class="form-control" onchange="toggleBackgroundInput()">
                        <option value="color" {{ data_get($settings, 'background_type') === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                        <option value="image" {{ data_get($settings, 'background_type') === 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                        <option value="gradient" {{ data_get($settings, 'background_type') === 'gradient' ? 'selected' : '' }}>{{ __('Gradient') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6" id="background_color_group"
                 style="display: {{ data_get($settings, 'background_type') === 'color' ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-fill-drip text-muted"></i> {{ __('Background Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_color" id="background_color" class="form-control"
                               value="{{ data_get($settings, 'background_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6" id="background_image_group"
                 style="display: {{ data_get($settings, 'background_type') === 'image' ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-image text-muted"></i> {{ __('Background Image') }}</label>
                    <input type="file" name="background_image" class="form-control file-input" accept="image/*">
                    @if(data_get($settings, 'background_type') === 'image' && !empty(data_get($settings, 'app_background')))
                        <div class="img-preview-card">
                            <img src="{{ getImagePath(data_get($settings, 'app_background')) }}" class="img-thumbnail">
                            <span class="img-label">{{ __('Current Background') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="form-divider"></div>

        {{-- Navigation Colors Section --}}
        <div class="form-section-title"><i class="fas fa-bars"></i> {{ __('Navigation Colors') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-square text-muted"></i> {{ __('Bottom Nav Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_bottom_color', '') }};"></i>
                        </span>
                        <input type="text" name="bottom_color" id="bottom_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_bottom_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-check-circle text-muted"></i> {{ __('Bottom Nav Active Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_active_color', '') }};"></i>
                        </span>
                        <input type="text" name="active_color" id="active_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_active_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-minus-circle text-muted"></i> {{ __('Bottom Nav Inactive Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_inactive_color', '') }};"></i>
                        </span>
                        <input type="text" name="inactive_color" id="inactive_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_inactive_color', '') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-divider"></div>

        {{-- Text & Button Colors Section --}}
        <div class="form-section-title"><i class="fas fa-font"></i> {{ __('Text & Button Colors') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-heading text-muted"></i> {{ __('Text Header Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'text_header_color', '#000000') }};"></i>
                        </span>
                        <input type="text" name="text_header_color" id="text_header_color" class="form-control"
                               value="{{ data_get($settings, 'text_header_color', '#000000') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-mouse-pointer text-muted"></i> {{ __('Button Text Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'button_text_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="button_text_color" id="button_text_color" class="form-control"
                               value="{{ data_get($settings, 'button_text_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-divider"></div>

        {{-- Dark/Light Mode Section --}}
        <div class="form-section-title"><i class="fas fa-moon"></i> {{ __('Dark / Light Mode') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-moon text-muted"></i> {{ __('Dark Body Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'dark_mode_color', '') }};"></i>
                        </span>
                        <input type="text" name="dark_mode_color" id="dark_mode_color" class="form-control"
                               value="{{ data_get($settings, 'dark_mode_color', '') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-sun text-muted"></i> {{ __('Light Body Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'light_mode_color', '') }};"></i>
                        </span>
                        <input type="text" name="light_mode_color" id="light_mode_color" class="form-control"
                               value="{{ data_get($settings, 'light_mode_color', '') }}" placeholder="{{ __('Choose color') }}" required>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-toggle-on text-muted"></i> {{ __('Dark Mode Enabled') }}</label>
                    <input type="hidden" name="is_dark_mode_enabled" value="0">
                    <input type="checkbox" name="is_dark_mode_enabled" value="1"
                           data-bootstrap-switch {{ data_get($settings, 'is_dark_mode_enabled') ? 'checked' : '' }}>
                </div>
            </div>
        </div>

        <div class="form-divider"></div>

        {{-- Body Theme Section --}}
        <div class="form-section-title"><i class="fas fa-swatchbook"></i> {{ __('Body Theme') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-power-off text-muted"></i> {{ __('Body Theme Enable') }}</label>
                    <input type="hidden" name="is_body_theme_enabled" value="0">
                    <input type="checkbox" name="is_body_theme_enabled" id="is_body_theme_enabled" value="1"
                           data-bootstrap-switch {{ data_get($settings, 'is_body_theme_enabled') ? 'checked' : '' }}>
                </div>
            </div>

            <div class="col-md-6" id="background_body_theme_group"
                 style="display: {{ data_get($settings, 'is_body_theme_enabled') ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-layer-group text-muted"></i> {{ __('Background Body Theme') }}</label>
                    <select name="background_body_theme" id="background_body_theme" class="form-control" onchange="toggleBodyThemeBackgroundInput()">
                        <option value="color" {{ data_get($settings, 'background_body_theme') === 'color' ? 'selected' : '' }}>{{ __('Color') }}</option>
                        <option value="image" {{ data_get($settings, 'background_body_theme') === 'image' ? 'selected' : '' }}>{{ __('Image') }}</option>
                        <option value="gradient" {{ data_get($settings, 'background_body_theme') === 'gradient' ? 'selected' : '' }}>{{ __('Gradient') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6" id="background_body_theme_color_group"
                 style="display: {{ data_get($settings, 'background_body_theme') === 'color' ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-fill-drip text-muted"></i> {{ __('Background Body Theme Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_body_theme_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_body_theme_color" id="background_body_theme_color" class="form-control"
                               value="{{ data_get($settings, 'background_body_theme_color', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6" id="background_body_theme_image_group"
                 style="display: {{ data_get($settings, 'background_body_theme') === 'image' ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-image text-muted"></i> {{ __('Background Body Theme Image') }}</label>
                    <input type="file" name="background_body_theme_image" class="form-control file-input" accept="image/*">
                    @if(data_get($settings, 'background_body_theme') === 'image' && !empty(data_get($settings, 'background_body_theme_image')))
                        <div class="img-preview-card">
                            <img src="{{ getImagePath(data_get($settings, 'background_body_theme_image')) }}" class="img-thumbnail">
                            <span class="img-label">{{ __('Current Image') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-6" id="background_body_theme_gradient_group"
                 style="display: {{ data_get($settings, 'background_body_theme') === 'gradient' ? 'block' : 'none' }};">
                <div class="form-group floating-group">
                    <label><i class="fas fa-fill-drip text-muted"></i> {{ __('Gradient Color One') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_body_theme_color_one', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_body_theme_color_one" id="background_body_theme_color_one" class="form-control"
                               value="{{ data_get($settings, 'background_body_theme_color_one', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                <div class="form-group floating-group">
                    <label><i class="fas fa-fill-drip text-muted"></i> {{ __('Gradient Color Two') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_body_theme_color_two', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_body_theme_color_two" id="background_body_theme_color_two" class="form-control"
                               value="{{ data_get($settings, 'background_body_theme_color_two', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>

                <div class="form-group floating-group">
                    <label><i class="fas fa-fill-drip text-muted"></i> {{ __('Gradient Color Three') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_body_theme_color_three', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_body_theme_color_three" id="background_body_theme_color_three" class="form-control"
                               value="{{ data_get($settings, 'background_body_theme_color_three', '#ffffff') }}" placeholder="{{ __('Choose color') }}">
                    </div>
                </div>
            </div>

            @if ($isThemeEnabled)
                <div class="col-md-6">
                    <div class="form-group floating-group">
                        <label><i class="fas fa-magic text-muted"></i> {{ __('New Theme Enabled') }}</label>
                        <input type="hidden" name="is_new_theme_enabled" value="0">
                        <input type="checkbox" name="is_new_theme_enabled" value="1"
                               data-bootstrap-switch {{ data_get($settings, 'is_new_theme_enabled') ? 'checked' : '' }}>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12 mt-3 text-end" style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="button" id="resetAppColorsSettings" class="btn btn-info" style="border-radius: 10px; padding: 10px 20px; font-weight: 600;">
                <i class="fas fa-undo"></i> {{ __('Reset Colors') }}
            </button>
            <button type="submit" class="btn btn-primary btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
