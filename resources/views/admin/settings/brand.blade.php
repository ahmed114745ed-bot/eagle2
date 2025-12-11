<div id="brandSettings" class="settings-section active">
    <h3> {{ __('Brand settings') }}</h3>
    <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="current_tab" value="">
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Application title en:') }} </label>
                    <input type="text" name="app_title_en" value="{{ $settings['app_title_en'] ?? '' }}" class="form-control">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Application title ar:') }} </label>
                    <input type="text" name="app_title_ar" value="{{ $settings['app_title_ar'] ?? '' }}" class="form-control">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Application logo:') }}</label>
                    <input type="file" name="app_logo" class="form-control" onchange="previewImage(event)">
                    <img id="imagePreview"
                         src="{{ !empty($settings['app_logo']) ? getImagePath($settings['app_logo']) : '' }}"
                         width="100" class="mt-2"
                         style="{{ !empty($settings['app_logo']) ? '' : 'display:none;' }}"
                         onclick="openFullScreen(this)">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ __('Application Fav Icon:') }}</label>
                    <input type="file" name="app_fav_icon" class="form-control" onchange="previewFavIcon(event)">
                    <img id="favIconPreview"
                         src="{{ !empty($settings['app_fav_icon']) ? getImagePath($settings['app_fav_icon']) : '' }}"
                         width="100" class="mt-2"
                         style="{{ !empty($settings['app_fav_icon']) ? '' : 'display:none;' }}"
                         onclick="openFullScreen(this)">
                </div>
            </div>

            <button type="submit">{{ __('save') }}</button>
        </div>
    </form>
</div>
