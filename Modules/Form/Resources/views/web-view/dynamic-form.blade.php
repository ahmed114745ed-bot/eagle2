@extends('Form::layouts.appUser')

@section('content')
<style>

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    
        justify-content: center;
        align-items: center;
        padding: 20px;
        position: relative;
        overflow-x: hidden;
    }


    .container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 900px;
        width: 100%;
        position: relative;
        z-index: 1;
        animation: slideUp 0.5s 
    ease-out;
    }
    .form-container {
        max-width: 950px;
        margin: 0 auto;
    }

    .card {
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
    }

    .card-header {
        font-size: 1.2rem;
        font-weight: 600;
        background: linear-gradient(90deg, #f8f9fa 0%, #eef2f7 100%);
        color: #333;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .card-body {
        background-color: #fff;
        padding: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #222;
        margin-bottom: 6px;
        font-size: 0.95rem;
    }

    .form-control, .form-select, textarea {
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #ced4da;
        transition: all 0.25s ease;
        width: 100%;
    }

    .form-control:focus, .form-select:focus, textarea:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
    }

    .field-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
    }

    .field-col {
        flex: 1 1 calc(50% - 1.25rem);
        min-width: 260px;
    }

    @media (max-width: 767px) {
        .field-col {
            flex: 1 1 100%;
        }
    }

    button[type="submit"] {
        border-radius: 10px;
        padding: 12px 30px;
        font-weight: 600;
        font-size: 1.05rem;
        background-color: #0d6efd;
        border: none;
        transition: all 0.25s ease;
    }

    button[type="submit"]:hover {
        background-color: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(13,110,253,0.3);
    }

    h2.text-primary {
        font-weight: 700;
        color: #0d6efd !important;
        letter-spacing: 0.5px;
    }

    /* زر اللغة */
    .lang-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 6px 14px;
        background-color: #f8f9fa;
        cursor: pointer;
        font-weight: 600;
        transition: 0.2s;
    }

    .lang-toggle:hover {
        background-color: #e9ecef;
    }

    .lang-toggle i {
        color: #0d6efd;
    }
    .btn-style {
        border: 2px solid blue;
        border-radius: 10px;
        background: blue;
        width: 17%;
        color: white;
    }

    /* ======== Container & Layout ======== */
.form-container {
    /* max-width: 1200px; */
    margin: auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ======== Cards ======== */
.card {
    border-radius: 10px;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.card-header {
    font-weight: 600;
    font-size: 1.1rem;
}

/* ======== Form Fields ======== */
.form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
}

/* Textareas */
textarea.form-control {
    resize: vertical;
}

/* ======== Selects ======== */
.form-select {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 0.5rem 1rem;
    font-size: 1rem;
}

/* ======== Buttons ======== */
.btn {
    border-radius: 8px;
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ======== Labels ======== */
.form-label {
    font-weight: 500;
    margin-bottom: 0.4rem;
}

/* ======== Search Results Cards ======== */
#search-results .card {
    border-radius: 10px;
    transition: all 0.3s ease;
}

#search-results .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

#search-results .card-title {
    font-weight: 600;
    font-size: 1.1rem;
}

/* ======== Badges ======== */
.badge {
    border-radius: 12px;
    padding: 0.3rem 0.7rem;
    font-size: 0.85rem;
}

/* ======== Images in Fields ======== */
input[type="file"] {
    padding: 0.5rem;
}

input[type="file"]::-webkit-file-upload-button {
    border: none;
    background-color: #0d6efd;
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

input[type="file"]::-webkit-file-upload-button:hover {
    background-color: #0b5ed7;
    transform: translateY(-1px);
}

/* ======== Responsive Adjustments ======== */
@media (max-width: 768px) {
    .form-container {
        padding: 0 1rem;
    }
    #search-results .col-md-6, #search-results .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.agency-card {
    background: #fff;
    border-radius: 15px;
    border: 1px solid #dce3f0;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    width: 252px;
    /* flex: 1 1 280px; */
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.agency-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.agency-card-inner {
    padding: 1.2rem;
}
.agency-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f3f6fb;
    padding: 0.6rem 1rem;
    border-radius: 10px;
    margin-bottom: 10px;
}
.agency-name {
    font-size: 1.1rem;
    font-weight: bold;
    color: #0d6efd;
    margin: 0;
}
.agency-id {
    background: #e9efff;
    color: #0d6efd;
    font-size: 0.9rem;
    padding: 3px 8px;
    border-radius: 8px;
}
.agency-info p {
    margin: 0.3rem 0;
    font-size: 0.9rem;
    color: #444;
}
.agency-bio {
    font-size: 0.85rem;
    color: #666;
    margin-top: 10px;
    text-align: center;
    min-height: 40px;
}
.agency-divider {
    border-bottom: 1px solid #e0e6ef;
    margin: 10px 0;
}
.agency-list {
    text-align: left;
    font-size: 0.9rem;
}
.card-selected {
    border: 3px solid #0d6efd;
    box-shadow: 0 12px 30px rgba(13,110,253,0.2);
}

.custom-field-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.custom-item {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 12px;
    background: #f9fafc;
    position: relative;
}
.custom-item input {
    min-width: 130px;
}
.remove-btn {
    margin-left: 6px;
    border-radius: 50%;
    padding: 4px 8px;
    font-size: 0.8rem;
    line-height: 1;
}
.remove-btn i {
    pointer-events: none;
}
</style>

@php
    $currentLocale = $locale ?? app()->getLocale();
    $direction = $currentLocale === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="container py-5 form-container" data-current-locale="{{ $currentLocale }}">
    <!-- <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">
            {{ $template->getTranslation('title', $currentLocale) }}
        </h2>
    </div> -->

    <form action="{{ route('form.submit', $template->form_type) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if (!empty($linkToken))
        <input type="hidden" name="token" value="{{ request()->query('token') ?? '' }}">
        @endif

        @if($template->getTranslation('description', $currentLocale))
        <div class="card mb-5 border-info shadow-sm">
            <div class="card-body bg-light">
                <h5 class="card-title text-primary mb-3">
                    <i class="fa fa-info-circle me-2"></i>{{ __('Form Description') }}
                </h5>
                <p class="card-text fs-5 text-muted" style="white-space: pre-line;">
                    {{ $template->getTranslation('description', $currentLocale) }}
                </p>
            </div>
        </div>
    @endif
        @foreach ($template->sections as $section)
            <div class="card mb-5">
                <div class="card-header">
                    {{ $section->getTranslation('title', $currentLocale) }}
                </div>

                <div class="card-body">
                    <div class="field-row">
                        @foreach ($section->fields as $field)
                            <div class="field-col mb-4">
                                <label class="form-label">
                                    {{ $field->getTranslation('field_label', $currentLocale) }}
                                    @if($field->is_required)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                {{-- Container for dynamic/custom fields --}}
                                <div id="field-container-{{ $section->id }}-{{ $field->id }}">
                                    {{-- Text, Email, Number, Tel, Date --}}
                                    @if (in_array($field->field_type, ['text', 'email', 'number', 'tel', 'date']))
                                        <input 
                                            type="{{ $field->field_type }}"
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            placeholder="{{ $field->getTranslation('placeholder', $currentLocale) }}"
                                            @required($field->is_required)>

                                    {{-- Textarea --}}
                                    @elseif ($field->field_type === 'textarea')
                                        <textarea 
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            rows="3"
                                            placeholder="{{ $field->getTranslation('placeholder', $currentLocale) }}"
                                            @required($field->is_required)></textarea>

                                    {{-- File --}}
                                    @elseif ($field->field_type === 'file')
                                        <input 
                                            type="file"
                                            name="{{ $field->field_name }}"
                                            class="form-control"
                                            @required($field->is_required)>

                                    {{-- Select --}}
                                    @elseif ($field->field_type === 'select')
                                        @php
                                            $options = is_array($field->options) 
                                                ? $field->options 
                                                : (is_string($field->options) ? json_decode($field->options, true) : []);
                                               

                                        @endphp
                                        <select 
                                            name="{{ $field->field_name }}"
                                            class="form-select"
                                            @required($field->is_required)>
                                            <option value="">-- {{ __('Select') }} --</option>
                                            @foreach ($options as $key => $value)

                                                @php
                                                
                                              
                                                $displayValue = is_array($value) 
                                                            ? ($value['label'][$currentLocale] ?? $value['label']['en'] ?? $value['value'] ?? $key)
                                                            : $value;
                                                @endphp
                                                <option value="{{ $key }}">{{ $displayValue }}</option>
                                            @endforeach
                                        </select>

                                    {{-- Radio --}}
                                    @elseif ($field->field_type === 'radio')
                                        @php
                                            $options = is_array($field->options) 
                                                ? $field->options 
                                                : (is_string($field->options) ? json_decode($field->options, true) : []);
                                        @endphp
                                        <div class="radio-group">
                                            @foreach ($options as $key => $value)
                                                @php
                                                    $displayValue = is_array($value) 
                                                        ? ($value[$currentLocale] ?? $value['en'] ?? $key) 
                                                        : $value;
                                                @endphp
                                                <div class="form-check">
                                                    <input 
                                                        type="radio"
                                                        name="{{ $field->field_name }}"
                                                        id="{{ $field->field_name }}_{{ $key }}"
                                                        value="{{ $key }}"
                                                        class="form-check-input"
                                                        @if($field->is_required && $loop->first) required @endif>
                                                    <label class="form-check-label" for="{{ $field->field_name }}_{{ $key }}">
                                                        {{ $displayValue }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    {{-- Checkbox --}}
                                    @elseif ($field->field_type === 'checkbox')
                                        @php
                                            $options = is_array($field->options) 
                                                ? $field->options 
                                                : (is_string($field->options) ? json_decode($field->options, true) : []);
                                        @endphp
                                        <div class="checkbox-group">
                                            @foreach ($options as $key => $value)
                                                @php
                                                    $displayValue = is_array($value) 
                                                        ? ($value[$currentLocale] ?? $value['en'] ?? $key) 
                                                        : $value;
                                                @endphp
                                                <div class="form-check">
                                                    <input 
                                                        type="checkbox"
                                                        name="{{ $field->field_name }}[]"
                                                        id="{{ $field->field_name }}_{{ $key }}"
                                                        value="{{ $key }}"
                                                        class="form-check-input">
                                                    <label class="form-check-label" for="{{ $field->field_name }}_{{ $key }}">
                                                        {{ $displayValue }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    {{-- Custom --}}
                                    @elseif ($field->field_type === 'custom')
                                        <div class="custom-field-list d-flex flex-wrap gap-2 align-items-start"
                                            id="custom-list-{{ $section->id }}-{{ $field->id }}"></div>

                                        {{-- إذا كان allow_add_more = true في config --}}
                                        @if(optional($field->config)['allow_add_more'] ?? true)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary mt-2"
                                                    onclick="addCustomField({{ $section->id }}, {{ $field->id }})">
                                                <i class="fa fa-plus"></i> {{ __('Add More') }}
                                            </button>
                                        @endif
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach


        @if($template->form_type === 'host_agency') <div class="card mb-5 border-primary shadow-sm p-4"> <h4 class="text-primary mb-4"> <i class="fa fa-search me-2"></i>{{ __('Search for Certified Agency Manager') }} </h4>

                <div class="input-group mb-4">
                    <input type="text" id="search-query" class="form-control" placeholder="{{ __('Enter name or ID') }}">
                </div>

                <input type="hidden" name="bd_id" id="selected-bd-id">

                <div id="search-results" class="d-flex flex-wrap gap-4" style="display: flex;"></div>
            </div>
            @php
                $currentLocale = request()->query('lang') ?? app()->getLocale();
            @endphp

            <script>
                let selectedCard = null;
                let searchTimeout = null;
                let lang = "{{ $currentLocale }}"; 

                const searchInput = document.getElementById('search-query');
                const resultsContainer = document.getElementById('search-results');

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (!query) {
                        resultsContainer.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => performSearch(query), 400);
                });

                function performSearch(query) {
                    fetch(`{{ route('host_agency.search') }}?query=${encodeURIComponent(query)}&lang=${lang}`)
                        .then(res => res.json())
                        .then(res => {
                            resultsContainer.innerHTML = '';

                            if (!res.data.length) {
                                resultsContainer.innerHTML = `<div class="text-center text-muted w-100">{{ __('No results found') }}</div>`;
                                return;
                            }

                            res.data.forEach(bd => {
                                let topAgencies = '';
                                bd.top_agencies.forEach(agency => {
                                    topAgencies += `
                                        <div class="mb-1">
                                            <strong>{{ __('Agency') }}:</strong> ${agency.name}
                                            <span class="badge bg-info text-dark ms-2">${agency.members_count} {{ __('Members') }}</span>
                                        </div>
                                    `;
                                });

                                const card = document.createElement('div');
                                card.classList.add('agency-card', 'selectable-card');
                                card.innerHTML = `
                                    <div class="agency-card-inner">
                                        <div class="agency-header">
                                            <h5 class="agency-name">${bd.name}</h5>
                                            <span class="agency-id">#${bd.id}</span>
                                        </div>
                                        <div class="agency-info">
                                            <p><i class="fa fa-globe text-primary me-2"></i> <strong>{{ __('Country') }}:</strong> ${bd.country}</p>
                                            <p><i class="fa fa-briefcase text-primary me-2"></i> <strong>{{ __('Experience') }}:</strong> ${bd.years} {{ __('years') }}</p>
                                            <p><i class="fa fa-phone text-primary me-2"></i> ${bd.phone}</p>
                                        </div>
                                        <p class="agency-bio">${bd.bio}</p>
                                        <div class="agency-divider"></div>
                                        <h6 class="text-success mb-2 text-center">⭐ {{ __('Top 3 Agencies') }}</h6>
                                        <div class="agency-list">
                                            ${topAgencies}
                                        </div>
                                    </div>
                                `;


                                card.addEventListener('click', function() {
                                    if (selectedCard) selectedCard.classList.remove('card-selected');
                                    card.classList.add('card-selected');
                                    selectedCard = card;
                                    document.getElementById('selected-bd-id').value = bd.id;
                                });

                                resultsContainer.appendChild(card);
                            });
                        })
                        .catch(err => console.error(err));
                }
            </script>

            <style>
                #search-results {
                    justify-content: flex-start;
                }
                .selectable-card {
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                .selectable-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
                }
                .card-selected {
                    border: 3px solid #0d6efd;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                }
            </style>


    @endif


        <input type="hidden" name="bd_id" id="selected-bd-id">

        <div class="text-center">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-paper-plane me-2"></i> {{ __('Submit') }}
            </button>
        </div>
    </form>

   
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.form-container');
    const formId = "{{ $template->id }}";
    let currentLang = container.dataset.currentLocale;

    function updateDirection(lang) {
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = lang;
    }

    updateDirection(currentLang);

    // ---------------- Custom field dynamic addition ----------------
        window.addCustomField = function(sectionId, fieldId) {
        const list = document.getElementById(`custom-list-${sectionId}-${fieldId}`);
        const index = list.children.length;

        const fields = [
            { name: 'app_name', type: 'text', placeholder: "{{ __('Application Name') }}" },
            { name: 'work_duration', type: 'number', placeholder: "{{ __('Work Duration (months)') }}" }
        ];

        let html = `<div class="custom-item d-flex align-items-start gap-2 flex-wrap bg-light p-2 rounded border position-relative" style="min-width:250px">`;

        fields.forEach(field => {
            html += `
                <div class="flex-grow-1">
                    <input type="${field.type}" 
                        name="sections[${sectionId}][fields][${fieldId}][items][${index}][${field.name}]"
                        class="form-control form-control-sm mb-1"
                        placeholder="${field.placeholder}">
                </div>
            `;
        });

        html += `
            <button type="button" class="btn btn-sm btn-danger remove-btn" 
                onclick="this.closest('.custom-item').remove()">
                <i class="fa fa-times"></i>
            </button>
        </div>`;

        list.insertAdjacentHTML('beforeend', html);
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.custom-field-list').forEach(list => {
            const [sectionId, fieldId] = list.id.replace('custom-list-', '').split('-');
            addCustomField(sectionId, fieldId);
        });
    });

    // ---------------- Language toggle example ----------------
    const toggleBtn = document.getElementById('toggle-lang');
    if(toggleBtn){
        toggleBtn.addEventListener('click', async () => {
            currentLang = currentLang === 'en' ? 'ar' : 'en';
            updateDirection(currentLang);
            await loadTranslations(currentLang);
            toggleBtn.querySelector('span').textContent = currentLang === 'en' ? 'عربي' : 'English';
        });
    }

    async function loadTranslations(lang) {
        try {
            const response = await fetch(`/form-translations?id=${formId}&locale=${lang}`);
            const data = await response.json();
            
            document.querySelector('h2').textContent = data.title;

            data.sections.forEach((section, sIndex) => {
                const cards = document.querySelectorAll('.card');
                if (cards[sIndex]) {
                    cards[sIndex].querySelector('.card-header').textContent = section.title;
                    const fields = cards[sIndex].querySelectorAll('.field-col');
                    section.fields.forEach((field, fIndex) => {
                        if(fields[fIndex]){
                            const label = fields[fIndex].querySelector('.form-label');
                            const requiredStar = label.querySelector('.text-danger');
                            label.textContent = field.label;
                            if(requiredStar) label.appendChild(requiredStar);
                        }
                    });
                }
            });

        } catch (error) {
            console.error('Translation error:', error);
        }
    }
});
</script>


<style>
.field-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.radio-group,
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-check {
    padding: 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.form-check:hover {
    background-color: #f8f9fa;
}

.form-check-input {
    margin-top: 0.3rem;
}

.form-check-label {
    margin-bottom: 0;
    cursor: pointer;
}

[dir="rtl"] .form-check-input {
    margin-left: 0.5rem;
    margin-right: 0;
}

</style>

@endsection
