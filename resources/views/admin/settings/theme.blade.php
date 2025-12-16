<div id="themeSettings" class="settings-section">
    <h3>{{ __('Theme settings') }}</h3>
    <form id="themeSettingsForm" action="{{ route('admin.app.settings.update') }}" method="POST"
          enctype="multipart/form-data">
        <div class="form row">
            @csrf
            <input type="hidden" name="current_tab" value="">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="secondary_color">{{ __('Primary Color:') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ $settings['secondary_color'] ?? '#FFFFFF' }};"></i>
                        </span>
                        <input type="text" id="secondary_color" name="secondary_color" class="form-control"
                               value="{{ $settings['secondary_color'] ?? '#FFFFFF' }}" placeholder="ادخل لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="primary_color">{{ __('Secondary Color:') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ $settings['primary_color'] ?? '#000000' }};"></i>
                        </span>
                        <input type="text" id="primary_color" name="primary_color" class="form-control"
                               value="{{ $settings['primary_color'] ?? '#000000' }}" placeholder="ادخل لون">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="text_secondary_color">{{ __('Text Secondary Color:') }}</label>
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ $settings['text_secondary_color'] ?? '#808080' }};"></i>
                        </span>
                        <input type="text" id="text_secondary_color" name="text_secondary_color" class="form-control"
                               value="{{ $settings['text_secondary_color'] ?? '#808080' }}" placeholder="ادخل لون">
                    </div>
                </div>
            </div>
        </div>

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
                    <div class="input-group colorpicker-element">
                        <span class="input-group-addon">
                            <i style="background-color: {{ $settings['box_background_color'] ?? '#F8F9FA' }};"></i>
                        </span>
                        <input type="text" id="box_background_color" name="box_background_color" class="form-control"
                               value="{{ $settings['box_background_color'] ?? '#F8F9FA' }}" placeholder="ادخل لون">
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
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @foreach ($brand_images as $img)
                            <img
                                class="image_success @if (!empty($settings['brand_background_image']) && $img->name == $settings['brand_background_image']) border-success @endif"
                                onclick="select_brand_image('{{ $img->name }}', this)"
                                src="{{ !empty($img->name) ? getImagePath($img->name) : '' }}"
                                alt="" style="width: 50px; height: 50px; cursor: pointer;">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="m-0">{{ __('Dark Mode') }}</h4>
                <div class="d-flex align-items-center gap-3">
                    <input type="hidden" name="dark_mode" id="dark_mode_input" value="0">
                    <input type="checkbox" name="dark_mode" value="1" data-bootstrap-switch
                        {{ !empty($settings['dark_mode']) && $settings['dark_mode'] ? 'checked' : '' }}>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    <button type="button" id="resetColors"
                            class="btn btn-secondary">{{ __('Reset Colors') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function initDarkModeSwitch() {
    const $switch = $('input[name="dark_mode"][data-bootstrap-switch]');
    const $input = document.getElementById('dark_mode_input');

    $switch.each(function () {
        $(this).bootstrapSwitch('state', $(this).prop('checked'), true);
    });

    $switch.on('switchChange.bootstrapSwitch', function (event, state) {
        const value = state ? '1' : '0';
        if ($input) $input.value = value;
        document.documentElement.classList.toggle('dark-mode', state);
    });
    
    // Apply dark mode if already checked on page load
    if ($switch.is(':checked')) {
        document.documentElement.classList.add('dark-mode');
    }
}

$(document).ready(initDarkModeSwitch);
$(document).on('pjax:success', initDarkModeSwitch);
</script>
