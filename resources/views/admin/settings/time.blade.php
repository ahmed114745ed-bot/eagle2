<div id="timeSettings" class="settings-section">
    <h3>{{ __('Timing settings') }}</h3>
    <form action="{{ route('admin.app.settings.update') }}" method="POST" class="settings-form">
        @csrf
        <div class="form">
            <div class="form-group">
                <label>{{ __('Time zone:') }}</label>
                <select name="timezone" class="form-control" required>
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone->name }}"
                            {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                            {{ $timezone->name }} ({{ $timezone->offset }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Default Country:') }}</label>
                <select name="default_country" class="form-control select2-country" required>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}"
                            {{ $country->id == ($settings['default_country'] ?? '') ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? $country->name : $country->e_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Default language:') }}</label>
                <select name="default_language" class="form-control select2-language" required>
                    @foreach ($languages as $language)
                        <option value="{{ $language->code }}"
                            {{ $language->code == ($settings['default_language'] ?? 'en') ? 'selected' : '' }}>
                            {{  $language->name  }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Start of week:') }}</label>
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

            <div class="form-group">
                <label>{{ __('End of week:') }}</label>
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

            <button type="submit" class="btn btn-primary mt-3 btn-save">
                <i class="fas fa-save"></i> {{ __('Save') }}
            </button>
        </div>
    </form>
</div>


<style>
    select.form-control {
        height: 45px;
        line-height: 45px;
    }
</style>