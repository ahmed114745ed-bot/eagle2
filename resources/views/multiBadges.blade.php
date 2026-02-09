
 @php
    $enabledLanguages = Cache::rememberForever('languages', function () {
        return \App\Models\Language::where('is_enabled', true)
            ->pluck('name', 'code')
            ->toArray();
    });

    $languages = $enabledLanguages;

    // default first tab
    $defaultLang = array_key_first($languages);

    // previously selected tab (restore)
    $selectedLang = request()->input('tab', $defaultLang);

@endphp

<ul class="nav nav-tabs" role="tablist">
    @foreach ($languages as $code => $label)
        <li class="nav-item {{ $code === $selectedLang ? 'active' : '' }}">
            <a class="nav-link"
               href="#lang-{{ $code }}"
               data-toggle="tab"
               data-lang="{{ $code }}">
                {{ __($label) }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content p-3 border border-top-0">
    @php $first = true; @endphp
    @foreach ($languages as $code => $label)
        <div class="tab-pane fade {{ $first ? 'show active' : '' }}"
             id="lang-{{ $code }}">

            {{-- Image --}}
            <div class="form-group">
                <label>{{ __('Image') }} ({{ $label }})</label>
                <input type="file"
                       name="images[{{ $code }}][image]"
                       class="form-control">
            </div>

            {{-- Default Image --}}
            <div class="form-group">
                <label>{{ __('Default Image') }} ({{ $label }})</label>
                <input type="file"
                       name="images[{{ $code }}][default_image]"
                       class="form-control">
            </div>

        </div>
        @php $first = false; @endphp
    @endforeach
</div>


<script>
    $(document).ready(function () {

        // Force first tab to show on initial load
        $('.nav-tabs li.active a').tab('show');

        // Save selected tab when user switches
        $('.nav-tabs a[data-toggle="tab"]').on('click', function () {
            let selected = $(this).data('lang');
            $('<input>').attr({
                type: 'hidden',
                name: 'tab',
                value: selected
            }).appendTo('form');
        });
    });
</script>

