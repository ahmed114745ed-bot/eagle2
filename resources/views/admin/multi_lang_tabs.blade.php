@php
    $enabledLanguages = Cache::rememberForever('languages', function () {
        return \App\Models\Language::where('is_enabled', true)
            ->pluck('name', 'code')
            ->toArray();
    });

    $languages = $enabledLanguages;
    $defaultLang = 'en';
    $titles = $model->title ?? []; // titles saved in DB
@endphp

<ul class="nav nav-tabs" role="tablist">
    @foreach ($languages as $code => $label)
        <li class="nav-item">
            <a class="nav-link {{ $code === $defaultLang ? 'active' : '' }}"
               id="tab-{{ $code }}"
               data-toggle="tab"
               href="#lang-{{ $code }}"
               role="tab"
               aria-controls="lang-{{ $code }}">
                {{ __($label) }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content p-3 border border-top-0 rounded-bottom">
    @foreach ($languages as $code => $label)
        <div class="tab-pane fade {{ $code === $defaultLang ? 'show active' : '' }}"
             id="lang-{{ $code }}"
             role="tabpanel"
             aria-labelledby="tab-{{ $code }}">

            <div class="form-group">
                <label>{{ __('Title') }} ({{ $label }})</label>
                <input type="text"
                       name="title[{{ $code }}]"
                       class="form-control"
                       value="{{ old("title.$code", $titles[$code] ?? '') }}">
            </div>
        </div>
    @endforeach
</div>

<script>
    $(function () {
        if ($('.nav-tabs .nav-link.active').length === 0) {
            $('.nav-tabs .nav-link:first').tab('show');
        }
    });
</script>

