<div id="landPageSettings" class="settings-section">
    <h3 class="mb-4">{{ __('Landing Page Settings') }}</h3>
    <div class="settings-container">
        <div class="tabs-sidebar" role="tablist" aria-orientation="vertical">
            <button class="tab-btn active" data-target="#general" type="button" role="tab"
                    aria-controls="general" aria-selected="true">
                {{ __('General Settings') }}
            </button>
            <button class="tab-btn" data-target="#stats" type="button" role="tab" aria-controls="stats"
                    aria-selected="false">
                {{ __('Statistics Settings') }}
            </button>
            <button class="tab-btn" data-target="#social" type="button" role="tab" aria-controls="social"
                    aria-selected="false">
                {{ __('Social Media Settings') }}
            </button>
        </div>

        <div class="tab-content">
            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data"
                  id="landingSettingsForm">
                @csrf
                <input type="hidden" name="current_tab" value="">
                <div class="tab-pane show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <h5>{{ __('General Settings') }}</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{ __('About Us Link') }}</label>
                            <input type="url" name="about_us_link"
                                   value="{{ $settings['about_us_link'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>{{ __('Gallery App Link') }}</label>
                            <input type="url" name="gallery_app_link"
                                   value="{{ $settings['gallery_app_link'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                    <h5>{{ __('Statistics Settings') }}</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label>{{ __('Number of Users') }}</label>
                            <input type="number" name="landing_users_count"
                                   value="{{ $settings['landing_users_count'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>{{ __('Number of Countries') }}</label>
                            <input type="number" name="landing_countries_count"
                                   value="{{ $settings['landing_countries_count'] ?? '' }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>{{ __('Number of Live Streams') }}</label>
                            <input type="number" name="landing_live_count"
                                   value="{{ $settings['landing_live_count'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="social" role="tabpanel" aria-labelledby="social-tab">
                    <h5>{{ __('Social Media Settings') }}</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label>{{ __('Facebook Link') }}</label>
                            <input type="url" name="facebook_link"
                                   value="{{ $settings['facebook_link'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>{{ __('Twitter Link') }}</label>
                            <input type="url" name="twitter_link" value="{{ $settings['twitter_link'] ?? '' }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>{{ __('WhatsApp Link') }}</label>
                            <input type="url" name="whatsapp_link"
                                   value="{{ $settings['whatsapp_link'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
