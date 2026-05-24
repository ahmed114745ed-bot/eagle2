@php
$tabs = [];
foreach ($categories as $category) {
    $tabs[$category->id] = $category->title[$locale] ?? $category->title['en'] ?? '';
}
$currentCategoryId = $model->gift_category_id ?? '';
$luckyGiftData = $model->luckyGift ?? [];
$vipLevel = $model->vip_level ?? '';

$labelWinProbability = __('win probability');
$labelMin = __('min percentage') . ' (%)';
$labelMid = __('mid percentage'). ' (%)';
$labelMax = __('max percentage'). ' (%)';
$labelVIP = __('VIP Level');
$scopeText = __('scope for multiplies');
$placeholderVIP = __('Less than 256');
@endphp

<div class="form-group">

    <input type="hidden"
       name="gift_category_id"
       value="{{ request()->route('type') ?? $currentCategoryId }}">
</div>

<div id="extra_fields_container"></div> {{-- dynamic fields container --}}





<script>
$(document).ready(function () {

    const categories = @json($categories); // contains ONLY route category
    const container = $('#extra_fields_container');

    const labels = {
        win: @json($labelWinProbability),
        min: @json($labelMin),
        mid: @json($labelMid),
        max: @json($labelMax),
        vip: @json($labelVIP),
        scope: @json($scopeText),
        vipPlaceholder: @json($placeholderVIP)
    };

    function renderLuckyGift(data = {}) {
        const prob1 = @json(\Cache::get('probability_times_1', []));
        const prob2 = @json(\Cache::get('probability_times_2', []));
        const prob3 = @json(\Cache::get('probability_times_3', []));

        container.html(`
<div class="form-group">
    <label>${labels.win}</label>
    <input type="number" name="luckyGift[win_probability]" class="form-control"
           value="${data.win_probability ?? ''}">
</div>

<div class="form-group">
    <label>${labels.min}</label>
    <input type="number" name="luckyGift[min_percentag]" class="form-control"
           value="${data.min_percentag ?? 0}">
    <p>[${prob1.join(', ')}] - ${labels.scope}</p>
</div>

<div class="form-group">
    <label>${labels.mid}</label>
    <input type="number" name="luckyGift[mid_percentag]" class="form-control"
           value="${data.mid_percentag ?? 0}">
    <p>[${prob2.join(', ')}] - ${labels.scope}</p>
</div>

<div class="form-group">
    <label>${labels.max}</label>
    <input type="number" name="luckyGift[max_percentag]" class="form-control"
           value="${data.max_percentag ?? 0}">
    <p>[${prob3.join(', ')}] - ${labels.scope}</p>
</div>
`);
    }

    function renderVIP(level = '') {
        container.html(`
<div class="form-group">
    <label>${labels.vip}</label>
    <input type="number" name="vip_level" class="form-control"
           placeholder="${labels.vipPlaceholder}"
           value="${level}">
</div>
`);
    }

    // ✅ AUTO RENDER FROM ROUTE CATEGORY
    console.log('[gift-type] categories array:', categories);
    if (categories.length > 0) {
        const category = categories[0];
        console.log('[gift-type] selected category:', category);
        if (category.type === 'lucky_gift') {
            console.log('[gift-type] rendering lucky gift fields', @json($luckyGiftData));
            renderLuckyGift(@json($luckyGiftData));
        } else if (category.type === 'vip') {
            console.log('[gift-type] rendering vip fields', @json($vipLevel));
            renderVIP(@json($vipLevel));
        } else {
            console.log('[gift-type] unsupported category type:', category.type);
        }
    } else {
        console.log('[gift-type] categories array is empty');
    }

    // Validation stays the same
    $('form').submit(function (e) {
        if ($('input[name="luckyGift[min_percentag]"]').length) {
            const min = +$('input[name="luckyGift[min_percentag]"]').val() || 0;
            const mid = +$('input[name="luckyGift[mid_percentag]"]').val() || 0;
            const max = +$('input[name="luckyGift[max_percentag]"]').val() || 0;

            if (Math.round(min + mid + max) !== 100) {
                alert('{{ __("Total percentages must equal 100%") }}');
                e.preventDefault();
            }
        }
    });

});
</script>

