<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


<style>
    .achievement-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
         width: 800px; /* Fixed width instead of max-width */
        height: auto; /* Height will adjust to content */
        min-height: 500px; /* Minimum height to prevent container from being too small */
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    
    .form-label {
        font-weight: 600;
       /* // color: #495057; */
         color: #000; /* Pure black */
    font-size: 1.25rem; /* 20px equivalent */
        margin-bottom: 0.5rem;
    }
    
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #ced4da;
        border-radius: 4px;
         color: #000;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    
    .image-option {
        display: inline-block;
        margin-right: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        border-radius: 5px;
    }
    
    .image-option:hover {
        transform: scale(1.05);
    }
    
    .image-option.selected {
        border-color: #0d6efd;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
    }
    
    .image-scroll-container {
        display: flex;
        overflow-x: auto;
        padding: 10px 0;
        gap: 15px;
    }
    
    .image-scroll-container::-webkit-scrollbar {
        height: 8px;
    }
    
    .image-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .image-scroll-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }
    
    .image-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .submit-btn {
        width: 100%;
        padding: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .form-section {
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .form-section.active {
        /* background: #e7f1ff; */
        /* border-left: 4px solid #0d6efd; */
    }
    
    .file-upload-wrapper {
        position: relative;
        margin-top: 10px;
    }
    
    .file-upload-label {
        display: block;
        padding: 10px 15px;
        background: #e9ecef;
        border: 1px dashed #adb5bd;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .file-upload-label:hover {
        background: #dee2e6;
    }
    
    .file-upload-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .achievement-container {
            padding: 1rem;
            margin: 1rem;
        }
    }
</style>

<div class="achievement-container">
    <h3 class="text-center mb-4">{{ __('assign achievement') }}</h3>
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <form method="POST" action="{{ route('admin.store-user-achievement') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
        @csrf
<br>
<br>
        <div class="form-section active">
            <div class="form-group mb-3">
                <label for="user_id" class="form-label">{{ __('admin.users') }}</label>
                <select name="user_id" id="user_id" class="form-control select2" required>
                    <option value="">{{ __('admin.selectUser') }}</option>
                </select>
                
            </div>
        </div>

        <input type="hidden" id="achievement_id" value="" name="achievement_id">
        <br>
        <br>
        <div class="form-section" id="achievementLevelDiv" style="display: none;">
            <div class="form-group mb-3">
                <label for="achievement_level_id" class="form-label">{{ __('admin.achievementLevel') }}</label>
                <select name="achievement_level_id" id="achievement_level_id" class="form-control">
                    <option value="">{{ __('admin.selectAchievementLevel') }}</option>
                </select>
            </div>
        </div>

        <div class="form-section" id="file_image">
            <div class="form-group mb-3">
                <label for="file_image_select" class="form-label">{{ __('admin.type_file') }}</label>
                <select name="file_image_select" id="file_image_select" class="form-control" required>
                    <option value="">{{ __('admin.type_file') }}</option>
                    <option value="file">{{ __('admin.file') }}</option>
                    <option value="image">{{ __('admin.Image') }}</option>
                </select>
            </div>
        </div>

        <div class="form-section" id="file_input" style="display: none;">
            <div class="form-group mb-3">
                <label class="form-label">{{ __('admin.select_file') }}</label>
                <div class="file-upload-wrapper">
                    <label for="custom_file" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        {{ __('admin.choose_file') }}
                        <span id="file-name" class="d-block text-muted small mt-1"></span>
                    </label>
                    <input type="file" id="custom_file" name="custom_file" class="file-upload-input" onchange="document.getElementById('file-name').textContent = this.files[0]?.name || '{{ __('admin.no_file_chosen') }}'">
                </div>
            </div>
        </div>

        <div class="form-section" id="imageDiv" style="display: none;">
            <div class="form-group mb-3">
                <label class="form-label">{{ __('admin.selectImage') }}</label>
                <div class="image-scroll-container">
                    @foreach ($achievementValidImage as $data)
                    <label class="image-option">
                        <input type="radio" name="custom_image" value="{{ $data->file }}" class="d-none">
                        <img src="{{ getImagePath($data->file) }}" class="img-thumbnail" width="100" height="100">
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    <br>
    <br>
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary submit-btn">
                <i class="fas fa-paper-plane me-2"></i>
                {{ __('admin.submit') }}
            </button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for user search
        $('#user_id').select2({
            ajax: {
                url: '{{ route('search.users') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        type: 'public',
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data.map(function(user) {
                            return { 
                                id: user.id, 
                                text: user.name || '',
                                image: user.profile_image ? '{{ asset('') }}' + user.profile_image : '{{ asset('images/default-user.png') }}'
                            };
                        }),
                        pagination: {
                            more: (params.page * 10) < data.total
                        }
                    };
                },
                cache: true
            },
            templateResult: formatUser,
            templateSelection: formatUserSelection,
            placeholder: '{{ __('admin.searchUsers') }}',
            minimumInputLength: 1
        });

        function formatUser(user) {
            if (!user.id) return user.text;
            
            var $container = $(
                '<div class="d-flex align-items-center">' +
                '<img src="' + user.image + '" class="rounded-circle me-2" width="30" height="30">' +
                '<span>' + user.text + '</span>' +
                '</div>'
            );
            return $container;
        }

        function formatUserSelection(user) {
            if (!user.id) return user.text;
            
            return $(
                '<div class="d-flex align-items-center">' +
                '<img src="' + user.image + '" class="rounded-circle me-2" width="20" height="20">' +
                '<span>' + user.text + '</span>' +
                '</div>'
            );
        }

        // When achievement select changes
        $('#achievement_id').change(function() {
            var achievementId = $(this).val();
            $('#achievementLevelDiv, #imageDiv, #gift_achievement_div, #file_image').hide();
            let selected = $(this).find(':selected').data('type');

            if (selected == '{{\Modules\Achievement\Enums\AchievementType::GIFT_TARGET}}') {
                $('#gift_achievement_div').fadeIn();
            } else if (achievementId === '') {
                $('#file_image').show();
                $('#gift_achievement_div').hide();
                $('#achievementLevelDiv').hide();
            } else if (selected !== '{{\Modules\Achievement\Enums\AchievementType::GIFT_TARGET}}' || selected !== '') {
                $('#gift_achievement_div').hide();
                $('#achievementLevelDiv').show();
            }

            // Get achievement levels
            if (achievementId) {
                $.ajax({
                    url: '/admin/get-achievement-levels/' + achievementId,
                    type: 'GET',
                    success: function(data) {
                        $('#achievement_level_id').empty();
                        $('#achievement_level_id').append('<option value="">{{ __('admin.selectAchievementLevel') }}</option>');

                        $.each(data, function(key, value) {
                            $('#achievement_level_id').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }
        });

        // Toggle between file and image upload
        $('#file_image_select').change(function() {
            var selectedValue = $(this).val();
            
            if (selectedValue == 'file') {
                $('#file_input').fadeIn();
                $('#imageDiv').hide();
            } else if (selectedValue == 'image') {
                $('#imageDiv').show();
                $('#file_input').hide();
            } else {
                $('#file_input').hide();
                $('#imageDiv').hide();
            }
        });

        // Highlight selected image
        $(document).on('change', 'input[name="custom_image"]', function() {
            $('.image-option').removeClass('selected');
            $(this).closest('.image-option').addClass('selected');
        });

        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    });
</script>
