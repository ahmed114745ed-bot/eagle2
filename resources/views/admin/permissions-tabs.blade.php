@php
    use Illuminate\Support\Str;
    $grouped = $permissions->groupBy('category');
    $selected = $selectedPermissions ?? [];
@endphp



<ul class="nav nav-tabs" role="tablist">
    @foreach($grouped as $category => $perms)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
               data-toggle="tab"
               href="#tab-{{ Str::slug($category) }}"
               role="tab">
                {{ $category }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content mt-3">
    @foreach($grouped as $category => $perms)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="tab-{{ Str::slug($category) }}"
             role="tabpanel">
             
            <div class="row">
                @foreach($perms->chunk(ceil($perms->count() / 3)) as $chunk)
                    <div class="col-md-4">
                        @foreach($chunk as $perm)
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perm->id }}"
                                       id="perm-{{ $perm->id }}"
                                       {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                    {{ $perm->name_ar ?? $perm->name }}
                                </label>
                            </div>
                        @endforeach
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