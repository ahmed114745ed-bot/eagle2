@php
    use App\Models\RoleCategory;

    $grouped = $permissions->groupBy('category');
    $categories = RoleCategory::orderBy('sort')->select('slug')->get();
    $selected = $selectedPermissions ?? [];

    $firstCategory = $categories->first()->slug ?? null;
    $firstPermissions = $grouped[$firstCategory] ?? collect();
    $chunked = $firstPermissions->chunk(ceil(max(1, $firstPermissions->count() / 3)));
@endphp

<style>
    /* Highlight for active tab */
    .nav-link.active {
        background-color: var(--primary-color);
        color: white ;
       
    }
    
</style>

<!-- Rest of your HTML remains the same -->
<input type="hidden" name="permissions_all" id="permissions_all">
<ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
    @foreach($categories as $category)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
               data-category="{{ $category->slug }}"
               href="#">
                {{ __($category->slug) }}
            </a>
        </li>
    @endforeach
</ul>

<!-- Permission Display Container -->
<div id="permissions-container" data-loaded-category="{{ $firstCategory }}">
    <div class="row">
        @foreach($chunked as $chunk)
            <div class="col-md-4">
                @foreach($chunk as $perm)
                    <div class="form-check mb-2">
                        <input class="form-check-input permission-checkbox"
                               type="checkbox"
                               value="{{ $perm->id }}"
                               id="perm-{{ $perm->id }}"
                               {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm-{{ $perm->id }}">
                            {{ __($perm->name) }}
                        </label>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>


<script>
    $(function () {
        let selectedPermissions = new Set(@json($selected ?? []));
        let loadedCategories = new Set([$('#permissions-container').data('loaded-category')]);

        function updateHiddenInput() {
            $('#permissions_all').val([...selectedPermissions].join(','));
        }

        function bindPermissionCheckboxes() {
            $('.permission-checkbox').on('change', function () {
                const id = parseInt($(this).val());
                if ($(this).is(':checked')) {
                    selectedPermissions.add(id);
                } else {
                    selectedPermissions.delete(id);
                }
                updateHiddenInput();
            });
        }

        // Initial bind and update
        bindPermissionCheckboxes();
        updateHiddenInput();

        $('#permission-tabs .nav-link').on('click', function (e) {
            e.preventDefault();

            $('#permission-tabs .nav-link').removeClass('active tab-highlight');
            $(this).addClass('active tab-highlight');

            const category = $(this).data('category');

            $.get(`/admin/permissions/category/${encodeURIComponent(category)}`, function (response) {
                const perms = response.permissions;
                let html = '<div class="row">';
                const chunkSize = Math.ceil(perms.length / 3);
                for (let i = 0; i < 3; i++) {
                    html += '<div class="col-md-4">';
                    perms.slice(i * chunkSize, (i + 1) * chunkSize).forEach(perm => {
                        const checked = selectedPermissions.has(perm.id) ? 'checked' : '';
                        html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input permission-checkbox"
                                    type="checkbox"
                                    value="${perm.id}"
                                    id="perm-${perm.id}"
                                    ${checked}>
                                <label class="form-check-label" for="perm-${perm.id}">
                                    ${ perm.name }
                                </label>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                html += '</div>';
                $('#permissions-container').html(html);
                bindPermissionCheckboxes();
                updateHiddenInput();
            });
        });

    });
</script>

