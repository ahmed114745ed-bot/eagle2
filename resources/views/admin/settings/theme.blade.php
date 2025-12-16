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

        <div class="col-12 d-flex gap-3 mt-3">
            <input type="hidden" name="dark_mode" id="dark_mode_input" value="{{ $settings['dark_mode'] ?? 0 }}">
            <label style="display:inline-flex;align-items:center;gap:8px;margin-right:auto;" for="dark_mode_toggle">
                <input type="checkbox" id="dark_mode_toggle" style="width:auto;" {{ !empty($settings['dark_mode']) && $settings['dark_mode'] ? 'checked' : '' }}>
                <span>{{ __('Dark Mode') }}</span>
            </label>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            <button type="button" id="resetColors"
                    class="btn btn-secondary">{{ __('Reset Colors') }}</button>
        </div>
    </form>
</div>

<script>
(function(){
    const checkbox = document.getElementById('dark_mode_toggle');
    const input = document.getElementById('dark_mode_input');
    const apply = (enabled) => {
        document.documentElement.classList.toggle('dark-mode', enabled);
        if (input) input.value = enabled ? '1' : '0';
        try { localStorage.setItem('dark_mode_pref', enabled ? '1' : '0'); } catch(e) {}
    };
    if (checkbox) {
        checkbox.addEventListener('change', function() { apply(checkbox.checked); });
        let stored = null;
        try { stored = localStorage.getItem('dark_mode_pref'); } catch(e) {}
        const initial = stored !== null ? (stored === '1') : (checkbox.checked);
        checkbox.checked = initial;
        apply(initial);
    }
})();
</script>
