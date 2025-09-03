{{-- custom-image.blade.php --}}
<div class="custom-image-field">

    {{-- If file exists --}}
    @if($value)
        <div style="margin-bottom: 10px;">
            @php
                // detect value url
                $url = \Illuminate\Support\Facades\Storage::disk(config('admin.upload.disk'))->url($value);

                // build a safe unique id
                $uniqueId = 'custom_img_' . (isset($id) ? $id : uniqid());

                // check file type extension
                $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            @endphp

            @if(in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))
                {{-- Normal browser-supported image --}}
                <img src="{{ $url }}" class="img img-thumbnail" style="max-height:150px">
            @else
                {{-- Custom renderer for svga/mp4/vap/etc --}}
                {!! handleShowImageWithTypes($uniqueId, $url, 100, 100, 10) !!}
            @endif
        </div>
    @endif

    {{-- Input field for uploading --}}
    <input type="file" name="{{ $name }}" {!! $attributes !!} />

    {{-- Keep old value on edit --}}
    @if($value)
        <input type="hidden" name="{{ $column }}_hidden" value="{{ $value }}">
    @endif

    @include('admin::form.help-block')

</div>
