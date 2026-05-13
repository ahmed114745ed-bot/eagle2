<div id="pusherSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-satellite-dish"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Real Time Setting') }}</h3>
            <p>{{ __('Configure Pusher, Firebase and real-time communication') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3 ms-0 me-auto">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4 settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow pusher-settings-form" style="height: auto !important; min-height: 560px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 color-white">{{ __('pusher') }}</h4>
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
                    <button type="submit" class="btn btn-primary mt-3 btn-save" style="position: relative; margin: 20px auto 0 auto; display: block;">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>

                    <hr class="my-4">

                    <h5 class="mb-3">{{ __('webhook_endpoints') }}</h5>

                    {{-- Endpoint 1: Chat Room Listener - Channel Existence --}}
                    <div class="mb-3">
                        <label class="form-label text-capitalize fw-semibold">
                            {{ __('chat_room_listener') }}
                        </label>
                        <small class="d-block text-muted mb-2" style="font-size: 12px;">
                            {{ __('chat_room_listener_desc') }}
                        </small>
                        <div class="pusher-webhook-container">
                            <input type="text" class="form-control pusher-webhook-url" value="{{ url('/api/chat-room-listener') }}" readonly>
                            <button type="button" class="btn pusher-btn-copy" onclick="copyPusherWebhook(this)">
                                {{ __('copy') }}
                            </button>
                        </div>
                        <small class="d-block mt-1" style="font-size: 11px; color: #17a2b8;">
                            <i class="fas fa-tag me-1"></i> {{ __('event') }}: {{ __('channel_existence') }}
                        </small>
                    </div>

                    {{-- Endpoint 2: Chat Room Listener - Presence --}}
                    <div class="mb-3">
                        <label class="form-label text-capitalize fw-semibold">
                            {{ __('chat_room_presence') }}
                        </label>
                        <small class="d-block text-muted mb-2" style="font-size: 12px;">
                            {{ __('chat_room_presence_desc') }}
                        </small>
                        <div class="pusher-webhook-container">
                            <input type="text" class="form-control pusher-webhook-url" value="{{ url('/api/chat-room-listener') }}" readonly>
                            <button type="button" class="btn pusher-btn-copy" onclick="copyPusherWebhook(this)">
                                {{ __('copy') }}
                            </button>
                        </div>
                        <small class="d-block mt-1" style="font-size: 11px; color: #ffc107;">
                            <i class="fas fa-tag me-1"></i> {{ __('event') }}: {{ __('presence') }}
                        </small>
                    </div>

                    {{-- Endpoint 3: Pusher Edit User - Channel Existence --}}
                    <div class="mb-3">
                        <label class="form-label text-capitalize fw-semibold">
                            {{ __('pusher_edit_user') }}
                        </label>
                        <small class="d-block text-muted mb-2" style="font-size: 12px;">
                            {{ __('pusher_edit_user_desc') }}
                        </small>
                        <div class="pusher-webhook-container">
                            <input type="text" class="form-control pusher-webhook-url" value="{{ url('/api/pusher-edit-user') }}" readonly>
                            <button type="button" class="btn pusher-btn-copy" onclick="copyPusherWebhook(this)">
                                {{ __('copy') }}
                            </button>
                        </div>
                        <small class="d-block mt-1" style="font-size: 11px; color: #17a2b8;">
                            <i class="fas fa-tag me-1"></i> {{ __('event') }}: {{ __('channel_existence') }}
                        </small>
                    </div>

                </div>
            </form>
        </div>

        <div class="col-md-6 mb-3 ms-0 me-auto">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="mb-4 settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow pusher-settings-form" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 color-white">{{ __('firebase') }}</h4>
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

                    <button type="submit" class="btn btn-primary mt-5 pusher-btn0bottom">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-6 mb-3 ms-0 me-auto card-top">
            <form action="{{ route('admin.update-agora-zego') }}" method="POST" class="settings-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                <div class="card p-3 shadow pusher-settings-form" style="">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 color-white">{{ __('supabase') }}</h4>
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

                    <button type="submit" class="btn btn-primary mt-5 pusher-btn0bottom">
                        <i class="fas fa-save"></i> {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.pusher-webhook-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.pusher-webhook-container .pusher-webhook-label {
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    color: #555;
}

.pusher-webhook-container .pusher-webhook-url {
    flex: 1;
    font-size: 0.9rem;
}

.pusher-webhook-container .pusher-btn-copy {
    background: green !important;
    color: white;
    width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    font-size: 0.9rem;
}
</style>

<script>
function copyPusherWebhook(button) {
    var container = button.closest('.pusher-webhook-container');
    var input = container.querySelector('.pusher-webhook-url');
    if (!input) return;

    navigator.clipboard.writeText(input.value).then(function () {
        var originalText = button.innerHTML;
        button.innerHTML = 'Copied!';
        button.style.background = '#218838';
        setTimeout(function () {
            button.innerHTML = originalText;
            button.style.background = 'green';
        }, 2000);
    }).catch(function () {
        var textarea = document.createElement('textarea');
        textarea.value = input.value;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        var originalText = button.innerHTML;
        button.innerHTML = 'Copied!';
        button.style.background = '#218838';
        setTimeout(function () {
            button.innerHTML = originalText;
            button.style.background = 'green';
        }, 2000);
    });
}
</script>
