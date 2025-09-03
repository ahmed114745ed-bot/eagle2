<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

{{--        @if($value)--}}
{{--            @php--}}
{{--                $url = \Illuminate\Support\Facades\Storage::disk(config('admin.upload.disk'))->url($value);--}}
{{--                $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));--}}
{{--                $uniqueId = 'file_' . uniqid();--}}
{{--            @endphp--}}

{{--            @if(!in_array($ext, ['png','jpg','jpeg','gif','webp','svg']))--}}
{{--                <div style="margin-bottom:10px;">--}}
{{--                    {!! handleShowImageWithTypes($uniqueId, $url, 100, 100, 10) !!}--}}
{{--                </div>--}}
{{--            @endif--}}
{{--        @endif--}}

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>
</div>

<script src="https://unpkg.com/svgaplayerweb@2.3.0/build/svga.min.js"></script>
