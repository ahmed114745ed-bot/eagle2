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
</style>

@php
    $currentLocale = app()->getLocale();
@endphp

<div class="container py-5 form-container" data-current-locale="{{ $currentLocale }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">
            {{ $template->getTranslation('title', $currentLocale) }}
        </h2>

        <button type="button" class="btn btn-outline-primary" id="toggle-lang">
            <i class="fa fa-globe"></i>
            <span>{{ $currentLocale === 'ar' ? 'English' : 'عربي' }}</span>
        </button>
    </div>

    <form action="{{ route('form.submit', $template->form_type) }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                                                if (is_array($value)) {
                                                    $displayValue = $value[$currentLocale] ?? $value['en'] ?? $key;
                                                } else {
                                                    $displayValue = $value;
                                                }
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
                                                if (is_array($value)) {
                                                    $displayValue = $value[$currentLocale] ?? $value['en'] ?? $key;
                                                } else {
                                                    $displayValue = $value;
                                                }
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
                                                if (is_array($value)) {
                                                    $displayValue = $value[$currentLocale] ?? $value['en'] ?? $key;
                                                } else {
                                                    $displayValue = $value;
                                                }
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
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

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
    const toggleBtn = document.getElementById('toggle-lang');
    const formId = "{{ $template->id }}";
    let currentLang = container.dataset.currentLocale;

    updateDirection(currentLang);

    toggleBtn.addEventListener('click', async () => {
        currentLang = currentLang === 'en' ? 'ar' : 'en';
        updateDirection(currentLang);
        await loadTranslations(currentLang);
        toggleBtn.querySelector('span').textContent = currentLang === 'en' ? 'عربي' : 'English';
    });

    function updateDirection(lang) {
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = lang;
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
                        if (fields[fIndex]) {
                            // تحديث Label
                            const label = fields[fIndex].querySelector('.form-label');
                            const requiredStar = label.querySelector('.text-danger');
                            label.textContent = field.label;
                            if (requiredStar) label.appendChild(requiredStar);
                            
                            // تحديث Input/Textarea
                            const input = fields[fIndex].querySelector('input:not([type="radio"]):not([type="checkbox"]), textarea');
                            if (input && field.placeholder) {
                                input.placeholder = field.placeholder;
                            }
                            
                            // تحديث Select
                            const select = fields[fIndex].querySelector('select');
                            if (select && field.options) {
                                const opts = select.querySelectorAll('option:not(:first-child)');
                                Object.values(field.options).forEach((text, i) => {
                                    if (opts[i]) opts[i].textContent = text;
                                });
                            }

                            // تحديث Radio
                            const radioLabels = fields[fIndex].querySelectorAll('.radio-group .form-check-label');
                            if (radioLabels.length && field.options) {
                                Object.values(field.options).forEach((text, i) => {
                                    if (radioLabels[i]) radioLabels[i].textContent = text;
                                });
                            }

                            // تحديث Checkbox
                            const checkboxLabels = fields[fIndex].querySelectorAll('.checkbox-group .form-check-label');
                            if (checkboxLabels.length && field.options) {
                                Object.values(field.options).forEach((text, i) => {
                                    if (checkboxLabels[i]) checkboxLabels[i].textContent = text;
                                });
                            }
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
