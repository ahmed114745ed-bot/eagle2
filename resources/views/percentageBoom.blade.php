
<form action="{{ url('admin/room-boom.save') }}" method="POST" enctype="multipart/form-data">
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


