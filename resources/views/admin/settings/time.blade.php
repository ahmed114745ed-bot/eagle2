<div id="timeSettings" class="settings-section">
    <h3>{{ __('Timing settings') }}</h3>
    <form action="{{ route('admin.app.settings.update') }}" method="POST">
        @csrf
        <div class="form">
            <label>{{ __('Time zone:') }}</label>

            <select name="timezone" class="form-control">
                @foreach ($timezones as $timezone)
                    <option value="{{ $timezone->name }}"
                        {{ $timezone->name == ($settings['timezone'] ?? '') ? 'selected' : '' }}>
                        {{ $timezone->name }} ({{ $timezone->offset }})
                    </option>
                @endforeach
            </select>

            <label>{{ __('Default Country:') }}</label>

            <select name="default_country" class="form-control select2-country">
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                        {{ $country->id == ($settings['default_country'] ?? '') ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? $country->name : $country->e_name }}
                    </option>
                @endforeach
            </select>

            <label class="mt-3">{{ __('Start of week:') }}</label>

            <select name="week_start" class="form-control">
                @foreach ([
                    'sunday' => __('Sunday'),
                    'monday' => __('Monday'),
                    'tuesday' => __('Tuesday'),
                    'wednesday' => __('Wednesday'),
                    'thursday' => __('Thursday'),
                    'friday' => __('Friday'),
                    'saturday' => __('Saturday'),
                ] as $key => $day)
                    <option
                        value="{{ $key }}" {{ ($settings['week_start'] ?? 'monday') == $key ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>

            <label class="mt-3">{{ __('End of week:') }}</label>

            <select name="week_end" class="form-control">
                @foreach ([
                    'saturday' => __('Saturday'),
                    'sunday' => __('Sunday'),
                    'monday' => __('Monday'),
                    'tuesday' => __('Tuesday'),
                    'wednesday' => __('Wednesday'),
                    'thursday' => __('Thursday'),
                    'friday' => __('Friday'),
                ] as $key => $day)
                    <option
                        value="{{ $key }}" {{ ($settings['week_end'] ?? 'sunday') == $key ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary mt-3">{{ __('Save') }}</button>
        </div>
    </form>
</div>
