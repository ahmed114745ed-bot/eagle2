<div id="gamesSettings" class="settings-section">

    <div class="form">
        <label class="d-block">{{ __('Games Settings:') }}</label>

        <div class="row mt-4">
            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                    <input type="hidden" name="provider_code" value="bytesun">
                    <input type="provider_name" name="provider_code" value="Bytesun">
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('Bytesun') }}</h4>

                                    {{-- <div class="d-flex align-items-center">
                                        <input type="radio" id="luckyFlexRadio" class="custom-radio libraryRealTime"
                                            name="games_library"
                                            value="0" {{ $gamesLibrary == '0' ? 'checked' : '' }}>
                                        <label for="luckyFlexRadio" class="switch"></label>
                                    </div> --}}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tencent_app_id">{{ __('app key') }}:</label>
                                        <input type="text" id="tencent_app_id" name="app_key"
                                               placeholder="app_key"
                                               value="{{ $bytesunSettings->app_key ?? '' }}" class="form-control" required>
                                    </div>
                                </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ url('admin/game-provider-setting') }}" method="POST" class="settings-form">
                    @csrf
                    <input type="hidden" name="provider_code" value="quantum_nexus">
                    <input type="provider_name" name="provider_code" value="Quantum Nexus">
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('Quantum Nexus') }}</h4>
                            {{-- <div class="ribbon-banner-card">
                                <span>{{ __('soon') }}</span>
                            </div> --}}
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
