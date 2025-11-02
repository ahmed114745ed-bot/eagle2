@csrf

@php
    use KevinSoft\MultiLanguage\MultiLanguage;
    use Modules\Form\Entities\CustomFieldWidget;

    $locales = MultiLanguage::config('languages'); 

    $allLocaleNames = [
        'ar' => 'العربية',
        'en' => 'English',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
        'hi' => 'हिन्दी',
    ];

    $localeCodes = array_keys($locales);

    $localeNames = array_intersect_key($allLocaleNames, array_flip($localeCodes));

     //dd($localeNames, $locales);

    $availableWidgets = CustomFieldWidget::where('is_active', true)->get();
@endphp

<style>

.checkbox {
        margin: 0px 8px !important
    }
    .text-lg {
    font-size: 1.425rem !important;
    }
    .secondary{
        background: var(--secondary-color) !important;
    }
    
    .field-item{
        /* background: #959595 !important; */
        border: 2px solid #959595 !important;
    }
    
    </style>

{{-- Form Template Info --}}
<div class="mb-8 p-6  rounded-lg border-2  ">
    <div class="flex justify-between items-center mb-4">
        <h2 class=" font-bold">{{ __('Template Information') }}</h2>
        <button type="button" onclick="toggleSection(this)" class="text-gray-600 hover:text-gray-800" style="margin: 0px 11px;">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>

    <div class="section-content">
        {{-- Title Fields (dynamic locales) --}}
        <div class="mb-6">
            <h3 class=" font-semibold mb-3">{{ __('Form Title') }} *</h3>
            <div class="grid grid-cols-1 md:grid-cols-{{ min(4, count($locales)) }} gap-4">
                @foreach($locales as $locale)
                    <div class="relative">
                        <div class="absolute top-0 right-0 bg-blue-100 text-blue-800 text-lg px-2 py-1 rounded">{{ strtoupper($locale) }}</div>
                        <input type="text" name="title[{{ $locale }}]" {{ $loop->first ? 'required' : '' }}
                               @if($locale === 'ar') dir="rtl" @endif
                               class="w-full px-4 py-4 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                               placeholder="{{ __('Form Title') }}"
                               value="{{ old('title.'.$locale, $template->getTranslation('title', $locale)) }}">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-lg font-semibold mb-2">{{ __('Form Type') }} *</label>
            <input type="text" name="form_type" required
                   class="w-full px-4 py-4 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                   placeholder="agency" value="{{ old('form_type', $template->form_type) }}" readonly>
        </div>

        {{-- Description Fields (dynamic locales) --}}
        <div id="descriptionInputs" class="grid grid-cols-1 md:grid-cols-{{ min(4, count($locales)) }} gap-4">
            @foreach($locales as $locale)
                <div class="relative">
                    <div class="absolute top-0 right-0 bg-blue-100 text-blue-800 text-lg px-2 py-1 rounded">{{ strtoupper($locale) }}</div>
                    <textarea name="description[{{ $locale }}]" rows="3" @if($locale === 'ar') dir="rtl" @endif
                              class="w-full px-4 py-4 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                              placeholder="{{ __('Form description') }}">{{ old('description.'.$locale, $template->getTranslation('description', $locale)) }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Sections --}}
<div id="sectionsContainer" class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class=" font-bold">{{ __('Section') }}</h2>
        <button type="button" onclick="addSection()"
                class="bg-green-600 text-white px-4 py-4 rounded-lg hover:bg-green-700">
            <i class="fas fa-plus {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
            {{ __('Add Section') }}
        </button>
    </div>
    <div id="sections"></div>
</div>

<div class="flex justify-end space-x-4">
    <a href="{{ admin_url('form-templates') }}"
       class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
        {{ __('Cancel') }}
    </a>
    <button type="submit"
             style="margin: 0px 13px;"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg">
        <i class="fas fa-save {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
        {{ $buttonText ?? __('Create Template') }}
    </button>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
const locales = @json(array_keys($locales));
const localeNames = @json($localeNames);
const availableWidgets = @json($availableWidgets);

let sectionCount = {{ $template->sections->count() > 0 ? $template->sections->max('section_order') : 0 }};
let fieldCounts = {};
const existingSections = @json($template->sections->keyBy('section_order'));

document.addEventListener('DOMContentLoaded', function() {
    // Enable sorting for sections
    new Sortable(document.getElementById('sections'), {
        animation: 150,
        handle: '.section-drag-handle',
        onEnd: updateSectionOrders
    });

    if (Object.keys(existingSections).length > 0) {
        for (const order in existingSections) {
            addSection(existingSections[order]);
        }
    } else {
        addSection();
    }
});

function updateSectionOrders() {
    document.querySelectorAll('.section-item').forEach((section, index) => {
        const input = section.querySelector('input[name$="[order]"]');
        if (input) input.value = index + 1;
    });
}

function updateFieldOrders(sectionId) {
    document.querySelectorAll(`#section-${sectionId}-fields .field-item`).forEach((field, index) => {
        const input = field.querySelector('input[name$="[order]"]');
        if (input) input.value = index + 1;
    });
}

function toggleSection(button) {
    const container = button.closest('.section-item, .field-item, .mb-8');
    if (!container) return;
    const content = container.querySelector('.section-content');
    const icon = button.querySelector('i');
    if (!content || !icon) return;

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function addSection(data = null) {
    if (data) {
        sectionCount = data.section_order;
    } else {
        sectionCount++;
    }

    fieldCounts[sectionCount] = data && data.fields ? data.fields.length : 0;

    // Build section titles inputs dynamically from locales
    const titlesHtml = locales.map(locale => {
        const val = data && data.title ? (data.title[locale] || '') : '';
        const dir = locale === 'ar' ? ' dir="rtl"' : '';
        return `
            <div class="relative">
                <div class="absolute top-0 right-0 bg-blue-100 text-blue-800 text-lg px-2 py-1 rounded">${locale.toUpperCase()}</div>
                <input type="text" name="sections[${sectionCount}][title][${locale}]" ${locale === locales[0] ? 'required' : ''} ${dir}
                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                    placeholder="" value="${escapeHtml(val)}">
            </div>`;
    }).join('');
    const canDeleteSection = !data || data.can_not_delete != 1;
    // console.log(data);

    const deleteSectionButton = canDeleteSection
        ? `  <button type="button" onclick="removeSection(${sectionCount})"
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
            </button>`
        : `  <button type="button" 
                            class="text-red-600 hover:text-red-800">
                <i class="fas fa-lock"></i>
            </button>`;
    const sectionHtml = `
        <div class="section-item mb-6 p-6  section-style  rounded-lg border-2 border-blue-300" data-section="${sectionCount}">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <span class="section-drag-handle cursor-move px-2">
                        <i class="fas fa-grip-vertical text-gray-500"></i>
                    </span>
                    <h3 class="text-lg font-bold text-blue-800">${'{{ __('Section') }}'} #${sectionCount}</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="toggleSection(this)" class="text-gray-600 hover:text-gray-800" style="margin: 0px 11px;">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                  ${deleteSectionButton}
                </div>
            </div>

            <div class="section-content">
                <div class="mb-4">
                    <h4 class="text-md font-semibold mb-3">${'{{ __('Section Title') }}'} *</h4>
                    <div class="grid grid-cols-1 md:grid-cols-${Math.min(4, locales.length)} gap-4">
                        ${titlesHtml}
                    </div>
                </div>

                <input type="hidden" name="sections[${sectionCount}][order]" value="${sectionCount}">

                <div class="mb-4">
                    <button type="button" onclick="addField(${sectionCount})"
                            class="bg-blue-600 text-white px-4 py-4 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus"></i> ${'{{ __('Add Field') }}'}
                    </button>

                </div>

                <div id="section-${sectionCount}-fields" class="space-y-4"></div>
            </div>
        </div>
    `;

    document.getElementById('sections').insertAdjacentHTML('beforeend', sectionHtml);

    // Initialize sorting for fields
    new Sortable(document.getElementById(`section-${sectionCount}-fields`), {
        animation: 150,
        handle: '.field-drag-handle',
        onEnd: () => updateFieldOrders(sectionCount)
    });

    if (data && data.fields) {
        data.fields.forEach(fieldData => {
            addField(sectionCount, fieldData);
        });
    }
}

function removeSection(sectionId) {
    const el = document.querySelector(`[data-section="${sectionId}"]`);
    if (el) el.remove();
    updateSectionOrders();
}

function addField(sectionId, data = null) {
    if (data) {
         fieldCounts[sectionId] = data.field_order;
    } else {
        fieldCounts[sectionId] = (fieldCounts[sectionId] || 0) + 1;
    }
    const fieldId = fieldCounts[sectionId];
  
    const canDeleteSection = data && data.can_not_delete;

    console.log(canDeleteSection);
    console.log(data);
    console.log('kkkkkk');

    // build multilingual label inputs
    const labelHtml = locales.map(locale => {
        const val = data && data.field_label ? (data.field_label[locale] || '') : '';
        const dir = locale === 'ar' ? ' dir="rtl"' : '';
        return `
            <div class="relative">
                <div class="absolute top-0 right-0 bg-blue-100 text-blue-800 text-lg px-2 py-1 rounded">${locale.toUpperCase()}</div>
                <input type="text" name="sections[${sectionId}][fields][${fieldId}][label][${locale}]" ${locale === locales[0] ? 'required' : ''} ${dir}
                    class="w-full px-5 py-4 border border-gray-300 rounded focus:border-blue-500 focus:outline-none text-lg"
                    placeholder="" value="${escapeHtml(val)}">
            </div>`;
    }).join('');

    // build multilingual placeholder inputs
    const placeholderHtml = locales.map(locale => {
        const val = data && data.placeholder ? (data.placeholder[locale] || '') : '';
        const dir = locale === 'ar' ? ' dir="rtl"' : '';
        return `
            <div class="relative">
                <div class="absolute top-0 right-0 bg-blue-100 text-blue-800 text-lg px-2 py-1 rounded">${locale.toUpperCase()}</div>
                <input type="text" name="sections[${sectionId}][fields][${fieldId}][placeholder][${locale}]" ${dir}
                    class="w-full px-5 py-4 border border-gray-300 rounded focus:border-blue-500 focus:outline-none text-lg"
                    placeholder="" value="${escapeHtml(val)}">
            </div>`;
    }).join('');

    // Build widget selector options
    const widgetOptionsHtml = availableWidgets.map(widget => {
        const widgetName = widget.widget_name.en || widget.widget_type;
        const isSelected = data && data.widget_id === widget.id ? 'selected' : '';
        return `<option value="${widget.id}" ${isSelected}>${widgetName}</option>`;
    }).join('');
    const canDelete = !data || data.can_not_delete != 1;
    console.log(data);
    
    const deleteButton = canDelete
        ? `<button type="button" onclick="removeField(${sectionId}, ${fieldId})"
            class="text-red-600 hover:text-red-800 text-lg">
            <i class="fas fa-times"></i>
        </button>`
        : `<button type="button" disabled
            class="text-gray-400 cursor-not-allowed text-lg" title="This field cannot be deleted">
            <i class="fas fa-lock"></i>
        </button>`;
    const fieldHtml = `
        <div class="field-item  p-4 rounded-lg border" data-field="${sectionId}-${fieldId}" id="field-${sectionId}-${fieldId}">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center">
                    <span class="field-drag-handle cursor-move px-2">
                        <i class="fas fa-grip-vertical text-gray-400"></i>
                    </span>
                    <h4 class="font-semibold text-gray-700">${'{{ __('Field') }}'} #${fieldId}</h4>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="toggleSection(this)" class="text-gray-600 hover:text-gray-800" style="margin: 0px 11px;">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                      ${deleteButton}
                </div>
            </div>

            <div class="section-content">
                <div class="mb-4">
                    <h5 class="text-lg font-semibold mb-2">${'{{ __('Field Label') }}'} *</h5>
                    <div class="grid grid-cols-1 md:grid-cols-${Math.min(4, locales.length)} gap-3">
                        ${labelHtml}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <div class="field-name-container">
                        <label class="block text-lg font-semibold mb-1">${'{{ __('Field Name') }}'} *</label>
                        <input type="text" ${canDeleteSection ? 'readonly' : ''} name="sections[${sectionId}][fields][${fieldId}][name]" required
                            class="w-full px-5 py-4 border border-gray-300 rounded focus:border-blue-500 focus:outline-none text-lg field-name-input"
                            placeholder="full_name" value="${data ? escapeHtml(data.field_name || '') : ''}">
                    </div>
                    <div>
                        <label class="block text-lg font-semibold mb-1">${'{{ __('Field Type') }}'} *</label>
                        <select ${canDeleteSection ? 'disabled' : ''} name="sections[${sectionId}][fields][${fieldId}][type]" required
                                class="w-full px-5 py-4 border border-gray-300 rounded focus:border-blue-500 focus:outline-none text-lg field-type-select"
                                onchange="handleFieldTypeChange(${sectionId}, ${fieldId}, this)">
                            <option value="text" ${data && data.field_type === 'text' ? 'selected' : ''}>${'{{ __('Text') }}'}</option>
                            <option value="email" ${data && data.field_type === 'email' ? 'selected' : ''}>${'{{ __('Email') }}'}</option>
                            <option value="number" ${data && data.field_type === 'number' ? 'selected' : ''}>${'{{ __('Number') }}'}</option>
                            <option value="tel" ${data && data.field_type === 'tel' ? 'selected' : ''}>${'{{ __('Phone') }}'}</option>
                            <option value="date" ${data && data.field_type === 'date' ? 'selected' : ''}>${'{{ __('Date') }}'}</option>
                            <option value="textarea" ${data && data.field_type === 'textarea' ? 'selected' : ''}>${'{{ __('Textarea') }}'}</option>
                            <option value="file" ${data && data.field_type === 'file' ? 'selected' : ''}>${'{{ __('File') }}'}</option>
                            <option value="select" ${data && data.field_type === 'select' ? 'selected' : ''}>${'{{ __('Select') }}'}</option>
                            <option value="checkbox" ${data && data.field_type === 'checkbox' ? 'selected' : ''}>${'{{ __('Checkbox') }}'}</option>
                            <option value="radio" ${data && data.field_type === 'radio' ? 'selected' : ''}>${'{{ __('Radio') }}'}</option>
                            <option value="custom" ${data && data.field_type === 'custom' ? 'selected' : ''}>${'{{ __('Custom Widget') }}'}</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Widget Selector (shown only when field type is 'custom') -->
                <div class="mb-4 widget-selector-container" style="display: ${data && data.field_type === 'custom' ? 'block' : 'none'};">
                    <label class="block text-lg font-semibold mb-1">
                        <i class="fas fa-puzzle-piece text-purple-600 mr-1"></i>
                        ${'{{ __('Select Custom Widget') }}'} *
                    </label>
                    <select name="sections[${sectionId}][fields][${fieldId}][widget_id]"
                            class="w-full px-5 py-4 border-2 border-purple-300 rounded focus:border-purple-500 focus:outline-none text-lg bg-purple-50 widget-select">
                        <option value="">-- ${'{{ __('Choose a widget') }}'} --</option>
                        ${widgetOptionsHtml}
                    </select>
                    <p class="text-lg text-gray-500 mt-1">
                        <i class="fas fa-info-circle"></i> ${'{{ __('Custom widgets provide specialized UI (BD Selector, User Picker, etc.)') }}'}
                    </p>
                </div>

                <div class="mb-4 placeholder-container" style="display: ${data && data.field_type === 'custom' ? 'none' : 'block'};">
                    <h5 class="text-lg font-semibold mb-2">${'{{ __('Placeholder Text') }}'}</h5>
                    <div class="grid grid-cols-1 md:grid-cols-${Math.min(4, locales.length)} gap-3">
                        ${placeholderHtml}
                    </div>
                </div>

                <!-- Select Options Configuration (shown only for select/checkbox/radio) -->
                <div class="mb-4 options-container" style="display: none;">
                    <h5 class="text-lg font-semibold mb-2">
                        <i class="fas fa-list-ul text-blue-600 mr-1"></i>
                        ${'{{ __('Select Options Configuration') }}'}
                    </h5>
                    
                    <!-- Options Type Selector -->
                    <div class="mb-3  p-3 rounded-lg">
                        <label class="block text-lg font-semibold mb-2">${'{{ __('Options Type') }}'}</label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="sections[${sectionId}][fields][${fieldId}][options_type]" 
                                       value="custom" ${!data || !data.data_source ? 'checked' : ''}
                                       class="mr-2 options-type-radio"
                                       onchange="toggleOptionsType(${sectionId}, ${fieldId}, 'custom')">
                                <span class="text-lg">
                                    <i class="fas fa-edit text-blue-600"></i>
                                    ${'{{ __('Custom Options') }}'}
                                </span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="sections[${sectionId}][fields][${fieldId}][options_type]" 
                                       value="predefined" ${data && data.data_source ? 'checked' : ''}
                                       class="mr-2 options-type-radio"
                                       onchange="toggleOptionsType(${sectionId}, ${fieldId}, 'predefined')">
                                <span class="text-lg">
                                    <i class="fas fa-database text-green-600"></i>
                                    ${'{{ __('Pre-defined Data') }}'}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Options Editor -->
                    <div class="custom-options-editor" style="display: ${!data || !data.data_source ? 'block' : 'none'};">
                        <textarea name="sections[${sectionId}][fields][${fieldId}][custom_options]" 
                                  rows="4" 
                                  class="w-full px-5 py-4 border border-gray-300 rounded focus:border-blue-500 focus:outline-none text-lg font-mono"
                                  placeholder="${'{{ __('Enter one option per line or JSON format') }}'}\nOption 1\nOption 2\nOption 3">${data && data.options ? (typeof data.options === 'object' ? JSON.stringify(data.options, null, 2) : data.options) : ''}</textarea>
                        <p class="text-lg text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i> 
                            ${'{{ __('Enter one option per line, or use JSON format for translations: {"en":"Option 1","ar":"خيار 1"}') }}'}
                        </p>
                    </div>

                    <!-- Pre-defined Data Selector -->
                    <div class="predefined-options-selector" style="display: ${data && data.data_source ? 'block' : 'none'};">
                        <select name="sections[${sectionId}][fields][${fieldId}][data_source]"
                                class="w-full px-5 py-4 border-2 border-green-300 rounded focus:border-green-500 focus:outline-none text-lg bg-green-50">
                            <option value="">-- ${'{{ __('Select Data Source') }}'} --</option>
                            <option value="countries" ${data && data.data_source === 'countries' ? 'selected' : ''}}>
                                <i class="fas fa-globe"></i> ${'{{ __('Countries') }}'}
                            </option>
                            <option value="cities" ${data && data.data_source === 'cities' ? 'selected' : ''}}>
                                <i class="fas fa-city"></i> ${'{{ __('Cities') }}'}
                            </option>
                            <option value="languages" ${data && data.data_source === 'languages' ? 'selected' : ''}">
                                <i class="fas fa-language"></i> ${'{{ __('Languages') }}'}
                            </option>
                            <option value="currencies" ${data && data.data_source === 'currencies' ? 'selected' : ''}}>
                                <i class="fas fa-dollar-sign"></i> ${'{{ __('Currencies') }}'}
                            </option>
                        </select>
                        <p class="text-lg text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i> 
                            ${'{{ __('Options will be loaded dynamically from server') }}'}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <label class="flex items-center text-lg">
                        <input type="checkbox" name="sections[${sectionId}][fields][${fieldId}][required]" value="1" ${data && data.is_required ? 'checked' : ''} class="mr-2 checkbox">
                        ${'{{ __('Required') }}'}
                    </label>
                    <label class="flex items-center text-lg">
                        <input type="checkbox" name="sections[${sectionId}][fields][${fieldId}][enabled]" value="1" ${data && data.is_enabled ? 'checked' : ''} class="mr-2 checkbox">
                        ${'{{ __('Enabled') }}'}
                    </label>
                </div>

                <input type="hidden" name="sections[${sectionId}][fields][${fieldId}][order]" value="${fieldId}">
            </div>
        </div>
    `;

    document.getElementById(`section-${sectionId}-fields`).insertAdjacentHTML('beforeend', fieldHtml);

    // Initialize field visibility based on field type
    setTimeout(() => {
        const fieldTypeSelect = document.querySelector(`#field-${sectionId}-${fieldId} .field-type-select`);
        if (fieldTypeSelect && data) {
            handleFieldTypeChange(sectionId, fieldId, fieldTypeSelect);
        }
    }, 50);

    // Scroll to the newly added field with smooth animation
    setTimeout(() => {
        const newField = document.getElementById(`field-${sectionId}-${fieldId}`);
        if (newField) {
            newField.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            // Add a highlight animation
            newField.classList.add('ring-4', 'ring-green-300');
            setTimeout(() => {
                newField.classList.remove('ring-4', 'ring-green-300');
            }, 2000);
        }
    }, 100);
}

// Handle field type change to show/hide widget selector
function handleFieldTypeChange(sectionId, fieldId, selectElement) {
    const fieldContainer = document.getElementById(`field-${sectionId}-${fieldId}`);
    const widgetContainer = fieldContainer.querySelector('.widget-selector-container');
    const placeholderContainer = fieldContainer.querySelector('.placeholder-container');
    const optionsContainer = fieldContainer.querySelector('.options-container');
    const fieldNameContainer = fieldContainer.querySelector('.field-name-container');
    const fieldNameInput = fieldContainer.querySelector('.field-name-input');
    const widgetSelect = fieldContainer.querySelector('.widget-select');

    // Reset visibility
    if (widgetContainer) widgetContainer.style.display = 'none';
    if (optionsContainer) optionsContainer.style.display = 'none';
    if (placeholderContainer) placeholderContainer.style.display = 'block';
    if (fieldNameContainer) fieldNameContainer.style.display = 'block';

    if (selectElement.value === 'custom') {
        // Show widget selector
        widgetContainer.style.display = 'block';
        widgetSelect.required = true;

        // Hide placeholder and field name
        if (placeholderContainer) placeholderContainer.style.display = 'none';
        if (fieldNameContainer) fieldNameContainer.style.display = 'none';
        fieldNameInput.required = false;

        // Auto-generate field name from widget selection
        widgetSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedWidget = availableWidgets.find(w => w.id == this.value);
                if (selectedWidget) {
                    // Generate field name from widget type
                    const autoName = selectedWidget.widget_type + '_' + sectionId + '_' + fieldId;
                    fieldNameInput.value = autoName;
                }
            }
        });
    } else if (selectElement.value === 'select' || selectElement.value === 'checkbox' || selectElement.value === 'radio') {
        // Show options configuration for select/checkbox/radio
        if (optionsContainer) optionsContainer.style.display = 'block';
        fieldNameInput.required = true;
        widgetSelect.required = false;
    } else {
        // Show standard fields
        widgetSelect.required = false;
        widgetSelect.value = '';
        fieldNameInput.required = true;
    }
}

// Toggle between custom and predefined options
function toggleOptionsType(sectionId, fieldId, type) {
    const fieldContainer = document.getElementById(`field-${sectionId}-${fieldId}`);
    const customEditor = fieldContainer.querySelector('.custom-options-editor');
    const predefinedSelector = fieldContainer.querySelector('.predefined-options-selector');

    if (type === 'custom') {
        customEditor.style.display = 'block';
        predefinedSelector.style.display = 'none';
    } else {
        customEditor.style.display = 'none';
        predefinedSelector.style.display = 'block';
    }
}

function removeField(sectionId, fieldId) {
    const el = document.querySelector(`[data-field="${sectionId}-${fieldId}"]`);
    if (el) el.remove();
    updateFieldOrders(sectionId);
}

// small helper to avoid XSS when inserting values
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
@endpush
