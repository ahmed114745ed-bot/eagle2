@extends('Form::layouts.app')

@section('title', $template->title)

@section('content')
<style>
    form{
        padding: 20px;
    }
    .secondary{
        background: var(--secondary-color) !important;
    }
</style>
<div class=" mx-auto px-4 sm:px-6 lg:px-8 secondary">
    <!-- <div class="mb-6">
        <a href="{{ admin_url('form-templates') }}" class="text-blue-600 hover:underline">
            <i class="fas fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
            {{ __('Back') }}
        </a>
    </div> -->

    <div class=" rounded-lg shadow-lg p-8">
        <div class="border-b   pb-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <!-- <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $template->title }}
                    </h1>
                    <p class="text-gray-600">
                        {{ $template->description }}
                    </p> -->
                </div>
                <a  href="{{ admin_url('form-templates/' . $template->id . '/edit') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-edit {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('Edit') }}
                </a>
            </div>

            @if($template->admin_notice)
            <div class="  border-{{ app()->getLocale() == 'ar' ? 'r' : 'l' }}-4 border-blue-500 p-4 rounded">
                <p class="text-blue-800">
                    <i class="fas fa-info-circle {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
                    {{ $template->admin_notice }}
                </p>
            </div>
            @endif
        </div>

        <form action="#" method="POST">
            @csrf
            <input type="hidden" name="form_template_id" value="{{ $template->id }}">

            @foreach($template->sections as $section)
            <div class="mb-8 rounded-lg p-6 border-2  ">
                <h2 class="text-2xl font-bold text-blue-700 mb-2 border-b-2 border-blue-200 pb-2">
                    {{ $section->title }}
                </h2>
                
                @if($section->description)
                <p class="text-gray-600 mb-4 text-sm">
                    {{ $section->description }}
                </p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($section->fields as $field)
                    <div class=" p-4 rounded-lg border">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ $field->field_label }}
                            @if($field->is_required)
                            <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if($field->help_text)
                        <p class="text-xs text-gray-500 mb-2">
                            <i class="fas fa-question-circle"></i>
                            {{ $field->help_text }}
                        </p>
                        @endif

                        @switch($field->field_type)
                            @case('textarea')
                                <textarea 
                                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                    rows="4"
                                    placeholder="{{ $field->placeholder }}"
                                    disabled
                                ></textarea>
                                @break

                            @case('select')
                                <select 
                                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                    disabled
                                >
                                    <option value="">{{ __('Choose...') }}</option>
                                    @if($field->options)
                                        @foreach($field->options as $option)
                                        <option value="{{ is_array($option) ? ($option[app()->getLocale()] ?? $option['en']) : $option }}">
                                            {{ is_array($option) ? ($option[app()->getLocale()] ?? $option['en']) : $option }}
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
                                @break

                            @case('checkbox')
                                <input 
                                    type="checkbox"
                                    class="w-5 h-5"
                                    disabled
                                >
                                @break

                            @case('radio')
                                <div class="space-y-2">
                                    @if($field->options)
                                        @foreach($field->options as $option)
                                        <label class="flex items-center">
                                            <input 
                                                type="radio"
                                                class="{{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"
                                                disabled
                                            >
                                            <span>{{ is_array($option) ? ($option[app()->getLocale()] ?? $option['en']) : $option }}</span>
                                        </label>
                                        @endforeach
                                    @endif
                                </div>
                                @break

                            @case('file')
                                <input 
                                    type="file"
                                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                    disabled
                                >
                                @break

                            @default
                                <input 
                                    type="{{ $field->field_type }}"
                                    class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                    placeholder="{{ $field->placeholder }}"
                                    disabled
                                >
                        @endswitch
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div class="flex justify-end mt-6">
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg cursor-not-allowed"
                        disabled>
                    <i class="fas fa-paper-plane {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
                    {{ __('Submit Form') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection