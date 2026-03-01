<div id="appSettings" class="app-settings settings-section">
    <h3>{{ __('App Settings') }}</h3>
    <form action="{{ route('admin.app-config.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
        @csrf
        <div class="form row">
            <input type="hidden" name="reset" id="reset" value="3">
            
            <div class="col-md-6">
                <div class="form-group">
                    <label for="app_primary_color">{{ __('Primary Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'app_primary_color', '#32e5ac') }};"></i>
                        </span>
                        <input type="text" name="app_primary_color" id="app_primary_color" class="form-control"
                               value="{{ data_get($settings, 'app_primary_color', '#32e5ac') }}" placeholder="اختر لون" required>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Background Type') }}</label>
                    <select name="background_type" id="background_type" class="form-control" onchange="toggleBackgroundInput()">
                        <option value="color" {{ data_get($settings, 'background_type') === 'color' ? 'selected' : '' }}>Color</option>
                        <option value="image" {{ data_get($settings, 'background_type') === 'image' ? 'selected' : '' }}>Image</option>
                        <option value="gradient" {{ data_get($settings, 'background_type') === 'gradient' ? 'selected' : '' }}>Gradient</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6" id="background_color_group"
                 style="display: {{ data_get($settings, 'background_type') === 'color' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label for="background_color">{{ __('Background Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'background_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="background_color" id="background_color" class="form-control"
                               value="{{ data_get($settings, 'background_color', '#ffffff') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6" id="background_image_group"
                 style="display: {{ data_get($settings, 'background_type') === 'image' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label>{{ __('Background Image') }}</label>
                    <input type="file" name="background_image" class="form-control" accept="image/*">
                    @if(data_get($settings, 'background_type') === 'image' && !empty(data_get($settings, 'app_background')))
                        <div class="mt-2">
                            <img src="{{ getImagePath(data_get($settings, 'app_background')) }}" width="100" class="img-thumbnail">
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="bottom_color">{{ __('Bottom Nav Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_bottom_color', '') }};"></i>
                        </span>
                        <input type="text" name="bottom_color" id="bottom_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_bottom_color', '') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="active_color">{{ __('Bottom Nav Active Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_active_color', '') }};"></i>
                        </span>
                        <input type="text" name="active_color" id="active_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_active_color', '') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="inactive_color">{{ __('Bottom Nav Inactive Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'bottom_nav_inactive_color', '') }};"></i>
                        </span>
                        <input type="text" name="inactive_color" id="inactive_color" class="form-control"
                               value="{{ data_get($settings, 'bottom_nav_inactive_color', '') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="text_header_color">{{ __('Text Header Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'text_header_color', '#000000') }};"></i>
                        </span>
                        <input type="text" name="text_header_color" id="text_header_color" class="form-control"
                               value="{{ data_get($settings, 'text_header_color', '#000000') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="button_text_color">{{ __('Button Text Color') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ data_get($settings, 'button_text_color', '#ffffff') }};"></i>
                        </span>
                        <input type="text" name="button_text_color" id="button_text_color" class="form-control"
                               value="{{ data_get($settings, 'button_text_color', '#ffffff') }}" placeholder="اختر لون">
                    </div>
                </div>
            </div>

            @if (in_array(env('APP_NAME'), ['Eagle', 'Lumio','Tiko Live']))
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="is_new_theme_enabled">{{ __('New Theme Enabled') }}</label>
                        <input type="hidden" name="is_new_theme_enabled" value="0">
                        <input type="checkbox" name="is_new_theme_enabled" value="1"
                               data-bootstrap-switch {{ data_get($settings, 'is_new_theme_enabled') ? 'checked' : '' }}>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12 d-flex gap-3 mt-4">
            <button type="submit" class="btn btn-primary btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
            <button type="button" id="resetAppColorsSettings" class="btn btn-info">
                <i class="fas fa-undo"></i> {{ __('Reset Colors') }}
            </button>
        </div>
    </form>
</div>
