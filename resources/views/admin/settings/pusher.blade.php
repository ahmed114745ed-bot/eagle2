<div id="pusherSettings" class="settings-section">
    <h3>{{ __('Real Time Setting') }}</h3>

    <div class="row">
        <div class="col-md-6 mb-3 ms-0 me-auto">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4 settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow" style="height: 450px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('pusher') }}</h4>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="pusherRadio" class="custom-radio pusherLib"
                                   name="library" value="2" {{ $library == '2' ? 'checked' : '' }}>
                            <label for="pusherRadio" class="switch"></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="pusher_app_id">{{ __('pusher_app_id') }}:</label>
                                <input type="text" id="pusher_app_id" name="pusher_app_id"
                                       placeholder="pusher_app_id" value="{{ $pusher_app_id }}" class="form-control"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="pusher_app_key">{{ __('pusher_app_key') }}:</label>
                                <input type="text" id="pusher_app_key" name="pusher_app_key"
                                       placeholder="pusher_app_key" value="{{ $pusher_app_key }}" class="form-control"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="pusher_app_secret">{{ __('pusher_app_secret') }}:</label>
                                <input type="text" id="pusher_app_secret" name="pusher_app_secret"
                                       placeholder="pusher_app_secret" value="{{ $pusher_app_secret }}"
                                       class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="pusher_app_cluster">{{ __('pusher_app_cluster') }}:</label>
                                <input type="text" id="pusher_app_cluster"
                                       name="pusher_app_cluster" placeholder="pusher_app_cluster"
                                       value="{{ $pusher_app_cluster }}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-5 btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-6 mb-3 ms-0 me-auto">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4 settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow" style="height: 450px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('firebase') }}</h4>
                        <div class="ribbon-banner-card">
                            <span>{{ __('soon') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="firebaseRadio" class="custom-radio firebaseLib"
                                   name="library" value="1" {{ $library == '1' ? 'checked' : '' }}>
                            <label for="firebaseRadio" class="switch"></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="firebase_api_key">{{ __('firebase_api_key') }}:</label>
                                <input type="text" id="firebase_api_key" name="firebase_api_key"
                                       placeholder="{{ __('firebase_api_key') }}"
                                       value="{{ $firebase_api_key }}" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="firebase_auth_domain">{{ __('firebase_auth_domain') }}:</label>
                                <input type="text" id="firebase_auth_domain" name="firebase_auth_domain"
                                       placeholder="{{ __('firebase_auth_domain') }}"
                                       value="{{ $firebase_auth_domain }}" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="firebase_database_url">{{ __('firebase_database_url') }}:</label>
                                <input type="text" id="firebase_database_url"
                                       name="firebase_database_url" placeholder="{{ __('firebase_database_url') }}"
                                       value="{{ $firebase_database_url }}" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-5 btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-6 mb-3 ms-0 me-auto card-top">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow" style="height: 450px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0">{{ __('supabase') }}</h4>
                        <div class="ribbon-banner-card">
                            <span>{{ __('soon') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <input type="radio" id="supabaseRadio" class="custom-radio supabaseLib"
                                   name="library" value="3" {{ $library == '3' ? 'checked' : '' }}>
                            <label for="supabaseRadio" class="switch"></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="supabase_url">{{ __('supabase_url') }}:</label>
                                <input type="text" id="supabase_url" name="supabase_url"
                                       placeholder="{{ __('supabase_url') }}"
                                       value="{{ $supabase_url }}" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="supabase_key">{{ __('supabase_key') }}:</label>
                                <input type="text" id="supabase_key" name="supabase_key"
                                       placeholder="{{ __('supabase_key') }}"
                                       value="{{ $supabase_key }}" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="supabase_service_role_key">{{ __('supabase_service_role_key') }}:</label>
                                <input type="text" id="supabase_service_role_key" name="supabase_service_role_key"
                                       placeholder="{{ __('supabase_service_role_key') }}"
                                       value="{{ $supabase_service_role_key }}" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-5 btn-save">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
