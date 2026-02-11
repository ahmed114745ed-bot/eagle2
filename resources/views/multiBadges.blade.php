


@php
use Illuminate\Support\Facades\Cache;

$enabledLanguages = Cache::rememberForever('languages', function () {
    return \App\Models\Language::where('is_enabled', true)
        ->pluck('name', 'code')
        ->toArray();
});

$languages = array_merge(
    ['default' => __('Default')],
    $enabledLanguages
);

$defaultLang  = array_key_first($languages);
$selectedLang = request()->input('tab', $defaultLang);
@endphp

{{-- Tabs --}}
<ul class="nav nav-tabs mb-0" role="tablist">
    @foreach ($languages as $code => $label)
        <li class="nav-item">
            <a class="nav-link {{ $code === $selectedLang ? 'active' : '' }}"
               href="#lang-{{ $code }}"
               data-toggle="tab"
               data-lang="{{ $code }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>

{{-- Tab Content --}}
<div class="tab-content border border-top-0 p-2">
    @foreach ($languages as $code => $label)
        @php
            $imageData = $badgeImages[$code] ?? null;
            $defaultImageData = $badgeImages['default'] ?? null;
        @endphp

        <div class="tab-pane {{ $code === $selectedLang ? 'active' : '' }}"
             id="lang-{{ $code }}"
             data-lang="{{ $code }}"
             data-overridden="false">

            {{-- Inheritance container --}}
            @if($code !== 'default')
                <div class="inherit-container"></div>
            @endif

            {{-- Default Image --}}
            <div class="form-group">
                <label>{{ __('Default Image') }} ({{ $label }})</label>
                <div class="mb-1 image-preview">
                    <img src="{{ getImagePath($imageData->show_image ?? $defaultImageData?->show_image) }}" height="70">
                </div>
                <input type="file"
                       name="images[{{ $code }}][default_image]"
                       class="form-control file-input"
                       data-lang="{{ $code }}">
            </div>

            {{-- Presentation File --}}
            <div class="form-group">
                <label>{{ __('Presentation file') }} ({{ $label }})</label>
                <div class="mb-1 image-preview">
                    <img src="{{ getImagePath($imageData->image ?? $defaultImageData?->image) }}" height="70">
                </div>
                <input type="file"
                       name="images[{{ $code }}][image]"
                       class="form-control file-input"
                       data-lang="{{ $code }}">
            </div>

            {{-- Image Type --}}
            <div class="form-group">
                <label>{{ __('Image Type') }} ({{ $label }})</label>
                <select name="images[{{ $code }}][image_type]"
                        class="form-control image-type"
                        data-lang="{{ $code }}"
                        required>
                    @foreach(\App\Enums\ImageType::options() as $key => $val)
                        <option value="{{ $key }}"
                                {{ ($imageData->image_type ?? $defaultImageData?->image_type ?? '') == $key ? 'selected' : '' }}>
                            {{ $val }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endforeach
</div>

{{-- JS for inheritance --}}
<script>
$(function() {
    const DEFAULT = 'default';

    function applyInheritance() {
        const defaultType = $('select[data-lang="default"]').val();
        const defaultShowImage = $('div#lang-default .image-preview img').attr('src');
        const defaultPresentationImage = $('div#lang-default').find('.form-group').eq(1).find('img').attr('src');

        $('.tab-pane').each(function () {
            const pane = $(this);
            const lang = pane.data('lang');
            if(lang === DEFAULT) return;

            const overridden = pane.data('overridden');

            if(!overridden) {
                pane.find('.inherit-container').html('<input type="hidden" name="images['+lang+'][inherit]" value="1">');
                pane.find('.image-type').val(defaultType);
                pane.find('.form-group').eq(0).find('img').attr('src', defaultShowImage);
                pane.find('.form-group').eq(1).find('img').attr('src', defaultPresentationImage);
            }
        });
    }

    // When default Image Type changes
    $('select[data-lang="default"]').on('change', applyInheritance);

    // When default images change
    $('div#lang-default input[type="file"]').on('change', function(e) {
        const reader = new FileReader();
        const img = $(this).closest('.form-group').find('img');

        reader.onload = function(e) {
            img.attr('src', e.target.result);
            applyInheritance();
        }

        if(this.files[0]) reader.readAsDataURL(this.files[0]);
    });

    // When uploading a file in non-default tab → mark as overridden
    $('.file-input').on('change', function () {
        const pane = $(this).closest('.tab-pane');
        const lang = pane.data('lang');
        if(lang !== DEFAULT) {
            pane.data('overridden', true);
            pane.find('.inherit-container').empty();
        }
    });

    // Init
    applyInheritance();
});
</script>
