
{{-- <form action="{{ url('admin/room-boom/save') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @foreach($percentages as $percentage)
    <div class="card mb-3 p-3 shadow-sm">
        <h5 class="mb-3">{{ __('percentage') }} {{ $percentage->percentage }}%</h5>

        <div class="row align-items-center">

            <!-- File Upload -->
            <div class="col-md-4">
                <label for="file_{{ $percentage->id }}">{{ __('img') }}</label>
                <input type="file"
                       name="files[{{ $percentage->id }}]"
                       id="file_{{ $percentage->id }}"
                       class="form-control"
                       accept=".svga,.mp4,.png,.jpg,.jpeg,.gif,.webp,.mov,.avi,.mkv,.webm"
                       onchange="previewFile(this, 'preview_{{ $percentage->id }}')">
            </div>

            <!-- Preview -->
            <div class="col-md-4">
                <label>{{ __('Preview') }}</label>
                <div class="border rounded d-flex align-items-center justify-content-center"
                     style="height:100px;">
                    <img id="preview_{{ $percentage->id }}"
                         src="{{ $percentage->image ? getImagePath($percentage->image) : '' }}"
                         style="max-height: 90px;">
                </div>
            </div>

            <!-- Type -->
            <div class="col-md-4">
                <label>{{ __('image_type') }}</label>
                <select name="types[{{ $percentage->id }}]"
                        class="form-control">
                    <option value="">{{ __('Select type') }}</option>
                    <option value="svga" {{ $percentage->image_type == 'svga' ? 'selected' : '' }}>{{__('svga')}}</option>
                    <option value="alpha" {{ $percentage->image_type == 'alpha' ? 'selected' : '' }}>{{__('alpha')}}</option>
                    <option value="mp4" {{ $percentage->image_type == 'mp4' ? 'selected' : '' }}>{{__('mp4')}}</option>
                    <option value="vap" {{ $percentage->image_type == 'vap' ? 'selected' : '' }}>{{__('vap')}}</option>
                    <option value="png" {{ $percentage->image_type == 'png' ? 'selected' : '' }}>{{ __('image:(jpg, jpeg, png,gif, bmp, tiff, svg, webp, mov, avi, wmv, flv, mkv, webm)') }}</option>
                </select>
            </div>

        </div>
    </div>
    @endforeach

    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>

 --}}


 <form action="{{ url('admin/room-boom/save') }}" method="POST" enctype="multipart/form-data">
    @csrf

    @foreach($percentages as $percentage)
    <div class="card mb-4 border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-header bg-gradient bg-primary bg-opacity-10 border-0 py-3 px-4">
            <h5 class="mb-0 fw-semibold text-primary">
                <i class="bi bi-pie-chart-fill me-2"></i>
                {{ __('percentage') }} 
                <span class="badge bg-primary ms-2 rounded-pill px-3 py-2">{{ $percentage->percentage }}%</span>
            </h5>
        </div>
        
        <div class="card-body p-4">
            <div class="row g-4">

                <!-- File Upload -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="file_{{ $percentage->id }}" class="form-label fw-medium text-secondary mb-2">
                            <i class="bi bi-cloud-upload me-1"></i>
                            {{ __('img') }}
                        </label>
                        <div class="upload-area border border-2 border-dashed rounded-4 p-3 text-center bg-light bg-opacity-25 transition-all" 
                             onmouseover="this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10')"
                             onmouseout="this.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10')">
                            <i class="bi bi-images fs-1 text-muted mb-2 d-block"></i>
                            <input type="file"
                                   name="files[{{ $percentage->id }}]"
                                   id="file_{{ $percentage->id }}"
                                   class="form-control form-control-sm"
                                   accept=".svga,.mp4,.png,.jpg,.jpeg,.gif,.webp,.mov,.avi,.mkv,.webm"
                                   onchange="previewFile(this, 'preview_{{ $percentage->id }}')">
                            <small class="text-muted d-block mt-2">SVGA, MP4, PNG, JPG, GIF, WEBP, MOV, AVI, MKV, WEBM</small>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="col-md-4">
                    <label class="form-label fw-medium text-secondary mb-2">
                        <i class="bi bi-eye me-1"></i>
                        {{ __('Preview') }}
                    </label>
                    <div class="border rounded-4 d-flex align-items-center justify-content-center bg-light bg-gradient"
                         style="height:150px; background: linear-gradient(45deg, #f8f9fa 25%, #ffffff 25%, #ffffff 50%, #f8f9fa 50%, #f8f9fa 75%, #ffffff 75%, #ffffff 100%); background-size: 20px 20px;">
                        <img id="preview_{{ $percentage->id }}"
                             src="{{ $percentage->image ? getImagePath($percentage->image) : 'https://via.placeholder.com/150x150?text=No+Image' }}"
                             class="rounded-3 shadow-sm"
                             style="max-height: 140px; max-width: 100%; object-fit: contain;"
                             onerror="this.src='https://via.placeholder.com/150x150?text=Error'">
                    </div>
                </div>

                <!-- Type -->
                <div class="col-md-4">
                    <label for="type_{{ $percentage->id }}" class="form-label fw-medium text-secondary mb-2">
                        <i class="bi bi-tag me-1"></i>
                        {{ __('image_type') }}
                    </label>
                    <select name="types[{{ $percentage->id }}]"
                            id="type_{{ $percentage->id }}"
                            class="form-select form-select-lg border-0 bg-light bg-opacity-50 rounded-4 shadow-sm">
                        <option value="" class="text-muted">{{ __('Select type') }}</option>
                        <option value="svga" {{ $percentage->image_type == 'svga' ? 'selected' : '' }} class="py-2">
                            <i class="bi bi-file-earmark me-2"></i>{{__('svga')}}
                        </option>
                        <option value="alpha" {{ $percentage->image_type == 'alpha' ? 'selected' : '' }}>
                            <i class="bi bi-transparency me-2"></i>{{__('alpha')}}
                        </option>
                        <option value="mp4" {{ $percentage->image_type == 'mp4' ? 'selected' : '' }}>
                            <i class="bi bi-film me-2"></i>{{__('mp4')}}
                        </option>
                        <option value="vap" {{ $percentage->image_type == 'vap' ? 'selected' : '' }}>
                            <i class="bi bi-camera-reels me-2"></i>{{__('vap')}}
                        </option>
                        <option value="png" {{ $percentage->image_type == 'png' ? 'selected' : '' }}>
                            <i class="bi bi-image me-2"></i>{{ __('image:(jpg, jpeg, png,gif, bmp, tiff, svg, webp, mov, avi, wmv, flv, mkv, webm)') }}
                        </option>
                    </select>
                   
                </div>

            </div>
        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary btn-lg rounded-5 px-5 py-3 shadow-lg hover-scale transition-all">
            <i class="bi bi-check-circle me-2"></i>
            {{ __('Save') }}
            <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>

<style>
.transition-all {
    transition: all 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.02);
}

.border-dashed {
    border-style: dashed !important;
}

.upload-area {
    cursor: pointer;
}

.form-select, .form-control {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background-color 0.15s ease-in-out;
}

.form-select:focus, .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    background-color: #ffffff;
}

/* Animated gradient background for cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.1) !important;
}
</style>

<!-- Add Bootstrap Icons if not already included -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">