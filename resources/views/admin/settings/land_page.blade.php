<div id="landPageSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-globe"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Landing Page Settings') }}</h3>
            <p>{{ __('Configure your landing page content, statistics and social media links') }}</p>
        </div>
    </div>

    <div class="settings-container">
        <div class="tabs-sidebar" role="tablist" aria-orientation="vertical">
            <button class="tab-btn active" data-target="#general" type="button" role="tab"
                    aria-controls="general" aria-selected="true">
                <i class="fas fa-cog"></i> {{ __('General Settings') }}
            </button>
            <button class="tab-btn" data-target="#stats" type="button" role="tab" aria-controls="stats"
                    aria-selected="false">
                <i class="fas fa-chart-bar"></i> {{ __('Statistics Settings') }}
            </button>
            <button class="tab-btn" data-target="#social" type="button" role="tab" aria-controls="social"
                    aria-selected="false">
                <i class="fas fa-share-alt"></i> {{ __('Social Media Settings') }}
            </button>
        </div>

        <div class="tab-content">
            <form action="{{ route('admin.app.settings.update') }}" method="POST" enctype="multipart/form-data"
                  id="landingSettingsForm" class="settings-form modern-form">
                @csrf
                <input type="hidden" name="current_tab" value="">
                <input type="hidden" name="inner_tab_type_hash" value="">

                {{-- General Settings --}}
                <div class="tab-pane show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="form-section-title"><i class="fas fa-cog"></i> {{ __('General Settings') }}</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group floating-group">
                                <label><i class="fas fa-info-circle text-muted"></i> {{ __('About Us Link') }}</label>
                                <input type="url" name="about_us_link"
                                       value="{{ $settings['about_us_link'] ?? '' }}" class="form-control"
                                       placeholder="{{ __('https://example.com/about') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group floating-group">
                                <label><i class="fas fa-images text-muted"></i> {{ __('Gallery App Link') }}</label>
                                <input type="url" name="gallery_app_link"
                                       value="{{ $settings['gallery_app_link'] ?? '' }}" class="form-control"
                                       placeholder="{{ __('https://example.com/gallery') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Statistics Settings --}}
                <div class="tab-pane" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                    <div class="form-section-title"><i class="fas fa-chart-bar"></i> {{ __('Statistics Settings') }}</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fas fa-users text-muted"></i> {{ __('Number of Users') }}</label>
                                <input type="number" name="landing_users_count"
                                       value="{{ $settings['landing_users_count'] ?? '' }}" class="form-control" min="0"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fas fa-globe-americas text-muted"></i> {{ __('Number of Countries') }}</label>
                                <input type="number" name="landing_countries_count"
                                       value="{{ $settings['landing_countries_count'] ?? '' }}"
                                       class="form-control" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fas fa-broadcast-tower text-muted"></i> {{ __('Number of Live Streams') }}</label>
                                <input type="number" name="landing_live_count"
                                       value="{{ $settings['landing_live_count'] ?? '' }}" class="form-control" min="0"
                                       placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Social Media Settings --}}
                <div class="tab-pane" id="social" role="tabpanel" aria-labelledby="social-tab">
                    <div class="form-section-title"><i class="fas fa-share-alt"></i> {{ __('Social Media Settings') }}</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fab fa-facebook text-muted"></i> {{ __('Facebook Link') }}</label>
                                <input type="url" name="facebook_link"
                                       value="{{ $settings['facebook_link'] ?? '' }}" class="form-control"
                                       placeholder="https://facebook.com/...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fab fa-twitter text-muted"></i> {{ __('Twitter Link') }}</label>
                                <input type="url" name="twitter_link" value="{{ $settings['twitter_link'] ?? '' }}"
                                       class="form-control" placeholder="https://twitter.com/...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group floating-group">
                                <label><i class="fab fa-whatsapp text-muted"></i> {{ __('WhatsApp Link') }}</label>
                                <input type="url" name="whatsapp_link"
                                       value="{{ $settings['whatsapp_link'] ?? '' }}" class="form-control"
                                       placeholder="https://wa.me/...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3 text-end">
                    <button type="submit" class="btn btn-primary btn-save">
                        <i class="fas fa-save"></i> {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
