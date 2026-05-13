<div id="mobileLinks" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-mobile-alt"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Mobile App Links') }}</h3>
            <p>{{ __('Configure download links for different platforms') }}</p>
        </div>
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
