
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
        <li class="nav-item">
            <a class="nav-link {{ $code === $selectedLang ? 'active' : '' }}"
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
        @php
            $imageData = $badgeImages[$code] ?? null;
        @endphp

        <div class="tab-pane fade {{ $code === $selectedLang ? 'show active' : '' }}" id="lang-{{ $code }}">

           
            <div class="form-group">
                <label>{{ __('Image') }} ({{ $label }})</label>
                @if(!empty($imageData->show_image))
                    <div>
                        <img src="{{ getImagePath( $imageData->show_image) }}" alt="" height="80">
                    </div>
                @endif
                <input type="file" name="images[{{ $code }}][image]" class="form-control">
            </div>

            
            <div class="form-group">
                <label>{{ __('Default Image') }} ({{ $label }})</label>
                @if(!empty($imageData->image))
                    <div>
                        <img src="{{ getImagePath( $imageData->image) }}" alt="" height="80">
                    </div>
                @endif
                <input type="file" name="images[{{ $code }}][default_image]" class="form-control">
            </div>

            
            <div class="form-group">
                <label>{{ __('Image Type') }} ({{ $label }})</label>
                <select name="images[{{ $code }}][image_type]" class="form-control" required>
                    @foreach(\App\Enums\ImageType::options() as $key => $val)
                        <option value="{{ $key }}" {{ ($imageData->image_type ?? '') == $key ? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
        @php $first = false; @endphp
    @endforeach
</div>





<script>
    $(document).ready(function () {

        // Force selected tab to show on initial load
        $('.nav-tabs a.active').tab('show');

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




