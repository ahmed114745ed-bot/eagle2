@php
    use Illuminate\Support\Str;
    $grouped = $permissions->groupBy('category');
@endphp


<body>
<ul class="nav nav-tabs" role="tablist">
    @foreach($grouped as $category => $perms)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ Str::slug($category) }}" role="tab">
                {{ $category }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content mt-3">
    @foreach($grouped as $category => $perms)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ Str::slug($category) }}" role="tabpanel">
            <div class="row">
                @foreach($perms as $perm)
                    <div class="col-md-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="permissions[]"
                                   value="{{ $perm->id }}"
                                   id="perm-{{ $perm->id }}"
                                   {{ in_array($perm->id, $selectedPermissions ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm-{{ $perm->id }}">
                                {{ $perm->name_ar ?? $perm->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<script>
   
    $(function () {
        $('.nav-tabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });

</script>

</body>





