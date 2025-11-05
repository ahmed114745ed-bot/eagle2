@php
$currentLocale = $locale ?? app()->getLocale();
$direction = $currentLocale === 'ar' ? 'rtl' : 'ltr';
@endphp

<div class="container py-5 form-container" data-current-locale="{{ $currentLocale }}" dir="{{ $direction }}">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0">
            {{ $template->getTranslation('title', $currentLocale) }}
        </h2>


    <a href="{{ admin_url('form-templates/' . $template->id . '/edit') }}" 
       class="btn btn-primary">
        <i class="fa fa-edit me-2"></i> {{ __('Edit Form') }}
    </a>
</div>

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
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            {{ $section->getTranslation('title', $currentLocale) }}
        </div>
        <div class="card-body">
            @if($section->description)
                <p class="text-muted mb-3">{{ $section->getTranslation('description', $currentLocale) }}</p>
            @endif

            <div class="row">
                @foreach ($section->fields as $field)
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">
                            {{ $field->getTranslation('field_label', $currentLocale) }}
                            @if($field->is_required)
                                <span class="text-danger">*</span>
                            @endif
                        </label>

                        @php
                            $options = is_array($field->options) 
                                ? $field->options 
                                : (is_string($field->options) ? json_decode($field->options, true) : []);
                        @endphp

                        {{-- Preview mode for each field type --}}
                        @switch($field->field_type)
                            @case('textarea')
                                <textarea class="form-control" rows="3" placeholder="{{ $field->getTranslation('placeholder', $currentLocale) }}" disabled></textarea>
                                @break

                            @case('select')
                                <select class="form-select" disabled>
                                    <option value="">-- {{ __('Select') }} --</option>
                                    @foreach ($options as $key => $value)
                                        @php
                                            $displayValue = is_array($value) ? ($value[$currentLocale] ?? $value['en'] ?? $key) : $value;
                                        @endphp
                                        <option value="{{ $key }}">{{ $displayValue }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('checkbox')
                                <div class="checkbox-group">
                                    @foreach ($options as $key => $value)
                                        @php
                                            $displayValue = is_array($value) ? ($value[$currentLocale] ?? $value['en'] ?? $key) : $value;
                                        @endphp
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" disabled>
                                            <label class="form-check-label">{{ $displayValue }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('radio')
                                <div class="radio-group">
                                    @foreach ($options as $key => $value)
                                        @php
                                            $displayValue = is_array($value) ? ($value[$currentLocale] ?? $value['en'] ?? $key) : $value;
                                        @endphp
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" disabled>
                                            <label class="form-check-label">{{ $displayValue }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @case('file')
                                <input type="file" class="form-control" disabled>
                                @break

                            @case('custom')
                                <div class="border rounded p-3 bg-light">
                                    <p class="text-muted mb-2">
                                        <i class="fa fa-plus-circle me-2 text-secondary"></i>
                                        {{ __('Custom Field List (Example)') }}
                                    </p>
                                    <ul class="list-group" id="custom-list-{{ $section->id }}-{{ $field->id }}">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ __('Custom Item 1') }}</span>
                                            <span class="badge bg-secondary">{{ __('Example') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ __('Custom Item 2') }}</span>
                                            <span class="badge bg-secondary">{{ __('Example') }}</span>
                                        </li>
                                    </ul>
                                </div>
                                @break

                            @default
                                <input 
                                    type="{{ $field->field_type }}"
                                    class="form-control"
                                    placeholder="{{ $field->getTranslation('placeholder', $currentLocale) }}"
                                    disabled>
                        @endswitch

                        @if($field->help_text)
                            <div class="form-text text-muted mt-1">
                                <i class="fa fa-question-circle me-1"></i>
                                {{ $field->getTranslation('help_text', $currentLocale) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach




</div>
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
<style>


@media (min-width: 1200px) {
    .container {
        width: 100% !important;
    }
}
    /* .form-container {
        background: #f9fafb;
        border-radius: 12px;
    }
    .form-label {
        color: #0d6efd;
    }
    .form-control:disabled, .form-select:disabled {
        background: #f3f3f3;
        cursor: not-allowed;
    }
    .card-header {
        font-weight: bold;
    } */
</style>
