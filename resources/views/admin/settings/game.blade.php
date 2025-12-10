<div id="gamesSettings" class="settings-section">

    <div class="form">
        <label class="d-block">{{ __('Games Settings:') }}</label>

        <div class="row mt-4">
            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('lucky phonix') }}</h4>
                            <div class="d-flex align-items-center">
                                <input type="radio" id="luckyFlexRadio" class="custom-radio libraryRealTime"
                                       name="games_library"
                                       value="0" {{ $gamesLibrary == '0' ? 'checked' : '' }}>
                                <label for="luckyFlexRadio" class="switch"></label>
                            </div>
                        </div>

                        <div class="row"></div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3 ms-0 me-auto">
                <form action="{{ route('admin.update-agora-zego') }}" method="POST">
                    @csrf
                    <div class="card p-3 shadow" style="height: 495px;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="m-0">{{ __('Zynga') }}</h4>
                            <div class="ribbon-banner-card">
                                <span>{{ __('soon') }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <input type="radio" id="guessTheWordRadio" class="custom-radio libraryRealTime"
                                       name="games_library" value="1" {{ $gamesLibrary == '1' ? 'checked' : '' }}>
                                <label for="guessTheWordRadio" class="switch"></label>
                            </div>
                        </div>
                        <div class="row"></div>
                        <button type="submit" class="btn btn-primary mt-3 btn-save">{{ __('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
