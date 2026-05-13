<div id="gamesSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-gamepad"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Games') }}</h3>
            <p>{{ __('Configure game providers and gaming settings') }}</p>
        </div>
    </div>

    <div class="form">
        <label class="d-block">{{ __('Games Settings:') }}</label>

        <div class="row mt-4">
            {{-- <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="bytesun">
                    <input type="hidden" name="provider_name" value="Bytesun">

                    <div class="card p-4 shadow-sm border-0" style="min-height: 600px; border-radius: 12px;">

                        <!-- Header -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0"style="color: white;">{{ __('Bytesun') }}</h4>
                                        <div class="d-flex align-items-center">
                                                <input type="hidden" name="active" value="0">
                                                <input type="checkbox" id="bytesunRadio"
                                                    class="custom-payment-radio libraryRealTime"
                                                    name="active" value="1"
                                                    {{ @$bytesunSettings?->is_active == 1  ? 'checked' : '' }}>
                                                <label for="bytesunRadio" class="switch"></label>
                                            </div>
                                    </div>

                        <!-- Settings Fields -->
                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tencent_app_id">{{ __('app key') }}:</label>
                                                    <input type="text" id="tencent_app_id" name="app_key"
                                                        placeholder="app_key"
                                                        value="{{ $bytesunSettings->app_key ?? '' }}" class="form-control" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tencent_app_id">{{ __('app id') }}:</label>
                                                    <input type="text" id="tencent_app_id" name="app_id"
                                                        placeholder="app_id"
                                                        value="{{ $bytesunSettings->app_id ?? '' }}" class="form-control" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tencent_app_id">{{ __('channel') }}:</label>
                                                    <input type="text" id="tencent_app_id" name="channel"
                                                        placeholder="channel"
                                                        value="{{ $bytesunSettings->channel ?? '' }}" class="form-control" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tencent_app_id">{{ __('gsp') }}:</label>
                                                    <input type="text" id="tencent_app_id" name="gsp"
                                                        placeholder="gsp"
                                                        value="{{ $bytesunSettings->gsp ?? '' }}" class="form-control" required>
                                                </div>
                                            </div>
                                    </div>

                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>

                        <!-- Divider -->
                     <hr class="my-4">

                        <h5 class="mb-3">Webhook Endpoints</h5>

                        @php
                            $routes = $bytesunSettings->webhook_routes ?? [];

                            if (is_string($routes)) {
                                $routes = json_decode($routes, true);
                            }

                            $routes = is_array($routes) ? $routes : [];
                        @endphp

                        @if(count($routes))
                            @foreach($routes as $key => $url)
                                <div class="mb-3">
                                    <label class="form-label text-capitalize fw-semibold">
                                        {{ str_replace('_', ' ', $key) }}
                                    </label>

                                    <div class="input-group">
                                        <span class="input-group-text bg-success text-white fw-bold">POST</span>
                                        <input type="text" class="form-control webhook-url font-monospace bg-light" value="{{ $url }}" readonly>
                                        <button type="button" class="btn btn-success" onclick="copyWebhook(this)">
                                            <i class="bi bi-clipboard me-1"></i>Copy
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                No webhook endpoints configured.
                            </div>
                        @endif


                    </div>
                </form>
            </div> --}}

            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                     <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="quantum_nexus">
                    <input type="hidden" name="provider_name" value="Quantum Nexus">
                    <div class="card p-4 shadow-sm border-0" style="min-height: 600px; border-radius: 12px;">

                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0"style="color: white;">{{ __('Quantum Nexus') }}</h4>
                             <div class="d-flex align-items-center">
                                    <input type="hidden" name="active" value="0">
                                    <input type="checkbox" id="quantumNexusRadio"
                                           class="custom-payment-radio libraryRealTime"
                                           name="active" value="1"
                                        {{ @$quantumNexusSettings?->is_active == 1  ? 'checked' : '' }}>
                                    <label for="quantumNexusRadio" class="switch"></label>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantum_nexus_app_id">{{ __('app key') }}:</label>
                                        <input type="text" id="quantum_nexus_app_id" name="app_key"
                                               placeholder="app_key"
                                               value="{{ $quantumNexusSettings->app_key ?? '' }}" class="form-control" required>
                                    </div>
                                </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>

                        <hr class="my-4">

                            <h5 class="mb-3">Webhook Endpoints</h5>

                            @php
                                $routes = $quantumNexusSettings->webhook_routes ?? [];

                                if (is_string($routes)) {
                                    $routes = json_decode($routes, true);
                                }

                                $routes = is_array($routes) ? $routes : [];
                            @endphp

                            @if(count($routes))
                                @foreach($routes as $key => $url)
                                    <div class="mb-3">
                                        <label class="form-label text-capitalize fw-semibold">
                                            {{ str_replace('_', ' ', $key) }}
                                        </label>

                                        {{-- <div class="input-group">
                                            <span class="input-group-text bg-success text-white fw-bold"> Type :=> POST</span>
                                            <input type="text" class="form-control webhook-url font-monospace bg-light" value="{{ $url }}" readonly>
                                            <button type="button" class="btn btn-success" onclick="copyWebhook(this)">
                                                <i class="bi bi-clipboard me-1"></i>Copy
                                            </button>
                                        </div> --}}

                                        <div class="webhook-container">
                                            <div class="webhook-label">Type =&gt; POST</div>
                                            <input type="text" class="form-control webhook-url" value="{{ $url }}" readonly>
                                            <button type="button" class="btn btn-copy" onclick="copyWebhook(this)">
                                                <i class="bi bi-clipboard"></i>Copy
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    No webhook endpoints configured.
                                </div>
                            @endif

                    </div>
                </form>
            </div>


              <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="utd_games">
                    <input type="hidden" name="provider_name" value="UTD Games">

                    <div class="card p-4 shadow-sm border-0" style="min-height: 600px; border-radius: 12px;">

                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0" style="color: white;">{{ __('UTD Games') }}</h4>
                            <div class="d-flex align-items-center">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" id="utdGamesRadio"
                                       class="custom-payment-radio libraryRealTime"
                                       name="active" value="1"
                                    {{ @$utdGamesSettings?->is_active == 1  ? 'checked' : '' }}>
                                <label for="utdGamesRadio" class="switch"></label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="utd_games_app_key">{{ __('app key') }}:</label>
                                    <input type="text" id="utd_games_app_key" name="app_key"
                                           placeholder="app_key"
                                           value="{{ $utdGamesSettings->app_key ?? '' }}" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>

                        <hr class="my-4">

                        <h5 class="mb-3">Webhook Endpoints</h5>

                        @php
                            $utdRoutes = $utdGamesSettings->webhook_routes ?? [];

                            if (is_string($utdRoutes)) {
                                $utdRoutes = json_decode($utdRoutes, true);
                            }

                            $utdRoutes = is_array($utdRoutes) ? $utdRoutes : [];
                        @endphp

                        @if(count($utdRoutes))
                            @foreach($utdRoutes as $key => $url)
                                <div class="mb-3">
                                    <label class="form-label text-capitalize fw-semibold">
                                        {{ str_replace('_', ' ', $key) }}
                                    </label>

                                    <div class="webhook-container">
                                        <div class="webhook-label">Type =&gt; POST</div>
                                        <input type="text" class="form-control webhook-url" value="{{ $url }}" readonly>
                                        <button type="button" class="btn btn-copy" onclick="copyWebhook(this)">
                                            <i class="bi bi-clipboard"></i>Copy
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                No webhook endpoints configured.
                            </div>
                        @endif

                    </div>
                </form>
            </div>

              <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="provider_code" value="zero_games">
                    <input type="hidden" name="provider_name" value="Zero Games">

                    <div class="card p-4 shadow-sm border-0" style="min-height: 600px; border-radius: 12px;">

                        <!-- Header -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="m-0"style="color: white;">{{ __('Zero Games') }}</h4>
                                        <div class="d-flex align-items-center">
                                                <input type="hidden" name="active" value="0">
                                                <input type="checkbox" id="zeroGamesRadio"
                                                    class="custom-payment-radio libraryRealTime"
                                                    name="active" value="1"
                                                    {{ @$zeroGamesSettings?->is_active == 1  ? 'checked' : '' }}>
                                                <label for="zeroGamesRadio" class="switch"></label>
                                            </div>
                                    </div>

                        <!-- Settings Fields -->
                        <div class="row">                    
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>


                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<style>

    .webhook-container {
    display: flex;
    align-items: center;
    gap: 5px; /* space between input and button */
    max-width: 500px; /* optional */
}

.webhook-container .webhook-url {
    flex: 1; /* input takes remaining space */
    font-size: 0.9rem;
}

.webhook-container .btn-copy {
    background: green !important;
    color: white;
    width: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    font-size: 0.9rem;
}
    .btn-save {
        display: block;
        margin: 20px auto 0 auto; /* top margin 20px, auto left/right */
    }
    .btn-success {
        background: green !important;
    width: 100px;
    }
        .input-group {
        flex-wrap: nowrap !important;
    }
    
    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .webhook-url {
        font-size: 0.9rem;
    }
    
    .input-group-text.bg-success {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        min-width: 65px;
        justify-content: center;
    }

</style>


<script>
    function copyWebhook(button) {
    // Find input inside the same webhook-container
    const container = button.closest('.webhook-container');
    const input = container.querySelector('.webhook-url');

    if (!input) return;

    // Copy to clipboard
    navigator.clipboard.writeText(input.value).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copied!';
        button.classList.add('btn-outline-success');
        button.classList.remove('btn-success');

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-success');
        }, 2000);
    });
}

</script>
