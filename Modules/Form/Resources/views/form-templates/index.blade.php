@extends('Form::layouts.app')

@section('title', __('Form Templates'))

@section('content')
<div class=" mx-auto px-4 sm:px-6 lg:px-8">
    <!-- <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Form Templates') }}</h1>
        <a href="{{ admin_url('form-templates/create') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 shadow-lg">
            <i class="fas fa-plus {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
            {{ __('Create New Template') }}
        </a>
    </div> -->


    <style>
        .form-templates-btn {
            margin: 0px 9px;
        }
        h3,h2{
        font-size: 24px !important;
            font-weight: bold;
        }
        p{
            font-size: 13px;
        }

    </style>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col justify-between">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class=" font-bold text-gray-900 mb-2">
                            {{ $template->title }}
                        </h3>
                        <!-- <p class="text-lg text-gray-600 mb-3 h-12 overflow-hidden">
                            {{ $template->description }}
                        </p> -->
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $template->is_active ? __('Active') : __('Inactive') }}
                    </span>
                </div>

                <!-- <div class="flex items-center text-sm text-gray-500 mb-4 space-x-4">
                    <span>
                        <i class="fas fa-layer-group {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
                        {{ $template->sections->count() }} {{ __('Sections') }}
                    </span>
                    <span>
                        <i class="fas fa-list {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
                        {{ $template->sections->sum(fn($s) => $s->fields->count()) }} {{ __('Fields') }}
                    </span>
                </div> -->
            </div>

            <div class="p-6 bg-gray-50">
                {{-- Copy Form Link Button --}}
                <div class="mb-3">
                    <button     onclick="copyFormLink('{{ route('forms.showByType', ['type' => $template->form_type , 'token' => (auth()->user()?->api_token ?? '')]) }}', this)"
                            class="w-full bg-green-600 text-white text-center px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center justify-center">
                        <i class="fas fa-link {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}"></i>
                        <span class="button-text">{{ __('Copy') }}</span>
                    </button>
                </div>

                {{-- Action Buttons --}}
                <div class="flex space-x-2">
                    <a href="{{ admin_url('form-templates/' . $template->id) }}"
                       class="flex-1 bg-blue-600 text-white text-center px-4 py-2 rounded-lg hover:bg-blue-700 transition form-templates-btn">
                        <i class="fas fa-eye {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
                        {{ __('Preview') }}
                    </a>
                    <a href="{{ admin_url('form-templates/' . $template->id . '/edit') }}"
                       class="flex-1 bg-yellow-500 text-white text-center px-4 py-2 rounded-lg hover:bg-yellow-600 transition form-templates-btn">
                        <i class="fas fa-edit {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
                        {{ __('Edit') }}
                    </a>
                    <!-- <form action="{{ admin_url('form-templates/' . $template->id) }} method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}');" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white text-center px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash-alt {{ app()->getLocale() == 'ar' ? 'ml-1' : 'mr-1' }}"></i>
                            {{ __('Delete') }}
                        </button>
                    </form> -->
                </div>
            </div>
        </div>
        @empty
        <!-- <div class="col-span-full text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">{{ __('No form templates yet') }}</p>
            <a href="{{ admin_url('form-templates/create') }}" class="text-blue-600 hover:underline">
                {{ __('Create your first template') }}
            </a>
        </div> -->
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyFormLink(url, button) {
    // Copy to clipboard
    navigator.clipboard.writeText(url).then(function() {
        // Change button appearance
        const buttonText = button.querySelector('.button-text');
        const icon = button.querySelector('i');
        const originalText = buttonText.textContent;
        const originalIconClass = icon.className;
        
        // Update to success state
        buttonText.textContent = '{{ __("Link Copied!") }}';
        icon.className = 'fas fa-check {{ app()->getLocale() == 'ar' ? 'ml-2' : 'mr-2' }}';
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-green-800');
        
        // Reset after 2 seconds
        setTimeout(function() {
            buttonText.textContent = originalText;
            icon.className = originalIconClass;
            button.classList.remove('bg-green-800');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
        }, 2000);
        
        // Show toast notification
        showToast('{{ __("Form link copied to clipboard!") }}', 'success');
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        showToast('{{ __("Failed to copy link") }}', 'error');
    });
}

function showToast(message, type) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 transition-opacity duration-300 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>
@endpush
