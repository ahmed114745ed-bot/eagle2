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
    <label>{{ __('type') }}</label>
    <select name="gift_category_id" class="form-control">
        <option value="">-- {{ __('Select Category') }} --</option>
        @foreach($tabs as $id => $title)
            <option value="{{ $id }}" @if($id == $currentCategoryId) selected @endif>{{ $title }}</option>
        @endforeach
    </select>
</div>

<div id="extra_fields_container"></div> {{-- dynamic fields container --}}

<script>
$(document).ready(function() {
    const categories = @json($categories);
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
    <input type="number" name="luckyGift[win_probability]" min="0"  class="form-control" value="${data.win_probability ?? ''}">
</div>
<div class="form-group">
    <label>${labels.min}</label>
    <input type="number" name="luckyGift[min_percentag]" min="0" max="100" class="form-control" value="${data.min_percentag ?? 0}">
    <p>[${prob1.join(', ')}] - ${labels.scope}</p>
</div>
<div class="form-group">
    <label>${labels.mid}</label>
    <input type="number" name="luckyGift[mid_percentag]" min="0" max="100" class="form-control" value="${data.mid_percentag ?? 0}">
    <p>[${prob2.join(', ')}] - ${labels.scope}</p>
</div>
<div class="form-group">
    <label>${labels.max}</label>
    <input type="number" name="luckyGift[max_percentag]" min="0" max="100" class="form-control" value="${data.max_percentag ?? 0}">
    <p>[${prob3.join(', ')}] - ${labels.scope}</p>
</div>
`);
    }

    function renderVIP(level = '') {
        container.html(`
<div class="form-group">
    <label>${labels.vip}</label>
    <input type="number" name="vip_level" min="0" class="form-control" placeholder="${labels.vipPlaceholder}" value="${level}">
</div>
`);
    }

    function renderFields(type) {
        if(type === 'lucky_gift') renderLuckyGift(@json($luckyGiftData));
        else if(type === 'vip') renderVIP(@json($vipLevel));
        else container.html('');
    }

    // On page load (edit)
    const currentCategoryId = parseInt('{{ $currentCategoryId }}');
    if(currentCategoryId) {
        const category = categories.find(c => c.id === currentCategoryId);
        if(category) renderFields(category.type);
    }

    // On change
    $('select[name="gift_category_id"]').change(function() {
        const selectedId = parseInt($(this).val());
        const category = categories.find(c => c.id === selectedId);
        const type = category ? category.type : null;
        renderFields(type);
    });

    // Validate percentages before submit
    $('form').submit(function(e) {
        if($('input[name="luckyGift[win_probability]"]').length) {
            const min = parseFloat($('input[name="luckyGift[min_percentag]"]').val() || 0);
            const mid = parseFloat($('input[name="luckyGift[mid_percentag]"]').val() || 0);
            const max = parseFloat($('input[name="luckyGift[max_percentag]"]').val() || 0);
            const total = Math.round(min + mid + max);
            if(total !== 100) {
                alert('{{ __("Total percentages must equal 100%! Current: ") }}' + total + '%');
                e.preventDefault();
            }
        }
    });
});
</script>
