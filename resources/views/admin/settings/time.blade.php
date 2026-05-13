
<div id="timeSettings" class="settings-section">
    <div class="section-header-bar">
        <div class="section-header-icon"><i class="fas fa-clock"></i></div>
        <div class="section-header-text">
            <h3>{{ __('Timing Settings') }}</h3>
            <p>{{ __('Configure timezone and time-related preferences') }}</p>
        </div>
    </div>

    <form action="{{ route('admin.app.settings.update') }}" method="POST" class="settings-form modern-form">
        @csrf
        <input type="hidden" name="current_tab" value="">

        {{-- Timezone & Locale Section --}}
        <div class="form-section-title"><i class="fas fa-globe-americas"></i> {{ __('Region & Locale') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-clock text-muted"></i> {{ __('Time zone:') }}</label>
                    <select name="timezone" class="form-control" required>
                        @foreach ($timezones as $timezone)
                            <option value="{{ $timezone->name }}"
                                {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                                {{ $timezone->name }} ({{ $timezone->offset }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-flag text-muted"></i> {{ __('Default Country:') }}</label>
                    <select name="default_country" class="form-control select2-country" required>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}"
                                {{ $country->id == ($settings['default_country'] ?? '') ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $country->name : $country->e_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-language text-muted"></i> {{ __('Default language:') }}</label>
                    <select name="default_language" class="form-control select2-language" required>
                        @foreach ($languages as $language)
                            <option value="{{ $language->code }}"
                                {{ $language->code == ($settings['default_language'] ?? 'en') ? 'selected' : '' }}>
                                {{ $language->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-divider"></div>

        {{-- Week Configuration --}}
        <div class="form-section-title"><i class="fas fa-calendar-week"></i> {{ __('Week Configuration') }}</div>
        <div class="form row">
            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-play text-muted"></i> {{ __('Start of week:') }}</label>
                    <select name="week_start" class="form-control" required>
                        @foreach ([
                            'sunday' => __('Sunday'),
                            'monday' => __('Monday'),
                            'tuesday' => __('Tuesday'),
                            'wednesday' => __('Wednesday'),
                            'thursday' => __('Thursday'),
                            'friday' => __('Friday'),
                            'saturday' => __('Saturday'),
                        ] as $key => $day)
                            <option value="{{ $key }}" {{ ($settings['week_start'] ?? 'monday') == $key ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group floating-group">
                    <label><i class="fas fa-stop text-muted"></i> {{ __('End of week:') }}</label>
                    <select name="week_end" class="form-control" required>
                        @foreach ([
                            'saturday' => __('Saturday'),
                            'sunday' => __('Sunday'),
                            'monday' => __('Monday'),
                            'tuesday' => __('Tuesday'),
                            'wednesday' => __('Wednesday'),
                            'thursday' => __('Thursday'),
                            'friday' => __('Friday'),
                        ] as $key => $day)
                            <option value="{{ $key }}" {{ ($settings['week_end'] ?? 'sunday') == $key ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
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
