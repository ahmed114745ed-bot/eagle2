<div id="themeSettings" class="settings-section">
    <h3>{{ __('Theme settings') }}</h3>
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
