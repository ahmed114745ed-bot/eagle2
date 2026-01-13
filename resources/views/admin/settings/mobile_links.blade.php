<div id="mobileLinks" class="settings-section">
    <div class="form">
        <div class="row mt-4">
            <div class="col-md-6 mb-3 ms-0 me-auto" style="margin-top: 40px;">
                <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="current_tab" value="">
                    <div class="card exp-card-cont p-3 shadow">
                        <div class="card-header exp-card d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('Android link') }}</h4>
                        </div>

    <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
        @csrf
        <input type="hidden" name="current_tab" value="">

        <div class="mobile-links-grid">
            <div class="link-card">
                <div class="link-card-icon android">
                    <i class="fab fa-android"></i>
                </div>
                <div class="link-card-content">
                    <div class="link-card-info">
                        <h5>{{ __('Android') }}</h5>
                        <small>Google Play Store</small>
                    </div>
                    <div class="link-card-input">
                        <input type="url" name="android_link"
                               value="{{ $settings['android_link'] ?? '' }}"
                               placeholder="{{ __('Enter link') }}"
                               class="form-control">
                    </div>
                </div>
            </div>

            <div class="link-card">
                <div class="link-card-icon ios">
                    <i class="fab fa-apple"></i>
                </div>
                <div class="link-card-content">
                    <div class="link-card-info">
                        <h5>{{ __('iOS') }}</h5>
                        <small>App Store</small>
                    </div>
                    <div class="link-card-input">
                        <input type="url" name="ios_link"
                               value="{{ $settings['ios_link'] ?? '' }}"
                               placeholder="{{ __('Enter link') }}"
                               class="form-control">
                    </div>
                </div>
            </div>

            <div class="link-card">
                <div class="link-card-icon huawei">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="link-card-content">
                    <div class="link-card-info">
                        <h5>{{ __('Huawei') }}</h5>
                        <small>AppGallery</small>
                    </div>
                    <div class="link-card-input">
                        <input type="url" name="huawei_link"
                               value="{{ $settings['huawei_link'] ?? '' }}"
                               placeholder="{{ __('Enter link') }}"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons mt-4">
            <button type="submit" class="btn btn-primary w-100 btn-save">
                <i class="fas fa-save"></i> {{ __('Save All Links') }}
            </button>
        </div>
    </form>
</div>
