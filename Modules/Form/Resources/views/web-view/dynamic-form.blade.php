@extends('Form::layouts.appUser')

@section('content')
<style>
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
    max-width: 1200px;
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
</style>

@php
    $currentLocale = $locale ?? app()->getLocale();
    $direction = $currentLocale === 'ar' ? 'rtl' : 'ltr';
@endphp
<div class="container py-5 form-container" data-current-locale="{{ $currentLocale }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">
            {{ $template->getTranslation('title', $currentLocale) }}
        </h2>
    </div>

    <form action="{{ route('form.submit', $template->form_type) }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                                                        ? ($value[$currentLocale] ?? $value['en'] ?? $key) 
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
                                        <div class="custom-field-list" id="custom-list-{{ $section->id }}-{{ $field->id }}"></div>
                                        <button type="button" class="btn btn-sm btn-secondary mt-2 btn-style"
                                            onclick="addCustomField({{ $section->id }}, {{ $field->id }})">
                                            {{ __('Add More') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
        <input type="hidden" name="bd_id" id="selected-bd-id">

        <div class="text-center">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-paper-plane me-2"></i> {{ __('Submit') }}
            </button>
        </div>
    </form>

    @if($template->form_type === 'host_agency')
    <div class="card mb-5 border-primary shadow-sm p-4">
        <h4 class="text-primary mb-4"><i class="fa fa-search me-2"></i>{{ __('Search for Certified Agency Manager') }}</h4>
        <div class="input-group mb-4">
            <input type="text" id="search-query" class="form-control" placeholder="{{ __('Enter name or ID') }}">
            <button class="btn btn-primary" id="search-btn">{{ __('Search') }}</button>
        </div>

        {{-- Hidden input for selected bd_id --}}
        <input type="hidden" name="bd_id" id="selected-bd-id">

        <div id="search-results" class="d-flex flex-wrap gap-4" style="display: flex;"></div>
    
    </div>

    <script>
        let selectedCard = null;

        document.getElementById('search-btn').addEventListener('click', function() {
            const query = document.getElementById('search-query').value.trim();
            if (!query) return;

            fetch(`{{ route('host_agency.search') }}?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(res => {
                    const container = document.getElementById('search-results');
                    container.innerHTML = '';
                    if (!res.data.length) {
                        container.innerHTML = `<div class="text-center text-muted w-100">{{ __('No results found') }}</div>`;
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
                        card.classList.add('card', 'flex-fill', 'shadow-sm', 'border-primary', 'selectable-card');
                        card.style.minWidth = '300px';
                        card.style.maxWidth = '350px';
                        card.style.flex = '1 1 300px';
                        card.style.textAlign = 'center';
                        card.innerHTML = `
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-2">${bd.name}</h5>
                                <p class="card-text mb-2">
                                    <strong>ID:</strong> ${bd.id}<br>
                                    <strong>{{ __('Country') }}:</strong> ${bd.country}<br>
                                    <strong>{{ __('Title') }}:</strong> ${bd.title}<br>
                                    <strong>{{ __('Experience') }}:</strong> ${bd.years} {{ __('years') }}<br>
                                    <strong>📞</strong> ${bd.phone}
                                </p>
                                <p class="text-muted mb-3">${bd.bio}</p>
                                <hr>
                                <h6 class="text-success mb-2">⭐ {{ __('Top 3 Agencies') }}</h6>
                                ${topAgencies}
                            </div>
                        `;

                        // Click event to select card
                        card.addEventListener('click', function() {
                            if (selectedCard) {
                                selectedCard.classList.remove('card-selected');
                            }
                            card.classList.add('card-selected');
                            selectedCard = card;
                            document.getElementById('selected-bd-id').value = bd.id;
                        });

                        container.appendChild(card);
                    });
                })
                .catch(err => console.error(err));
        });
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
            border: 3px solid #0d6efd; /* اللون الأساسي عند التحديد */
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
@endif

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
        const html = `
            <div class="custom-item mb-2 border p-2 rounded">
                <input type="text" name="sections[${sectionId}][fields][${fieldId}][items][${index}][app_name]" 
                       placeholder="{{ __('Application Name') }}" class="form-control mb-1">
                <input type="number" name="sections[${sectionId}][fields][${fieldId}][items][${index}][work_duration]" 
                       placeholder="{{ __('Work Duration (months)') }}" class="form-control">
            </div>
        `;
        list.insertAdjacentHTML('beforeend', html);
    }

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
