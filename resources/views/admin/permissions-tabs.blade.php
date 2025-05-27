@php
    use App\Models\RoleCategory;

    // Group permissions by category first
    $grouped = $permissions->groupBy('category');
    $categories = RoleCategory::orderBy('sort')->select('slug')->get();
    $selected = $selectedPermissions ?? [];

    // Pre-process all permissions by category and group
    $allGroupedPermissions = [];
    foreach($grouped as $categorySlug => $categoryPermissions) {
        $allGroupedPermissions[$categorySlug] = $categoryPermissions->groupBy(function($permission) {
            $parts = explode('-', $permission->slug);
            array_shift($parts);
            return implode('-', $parts);
        });
    }

    $firstCategory = $categories->first()->slug ?? null;
@endphp

<style>
    .nav-tabs{
        background: var(--box-background-color);
    }
    .nav-link.active {
        background-color: var(--primary-color);
        color: white;
    }
    .permissions-section {
        display: none;
    }
    .permissions-section.active {
        display: block;
    }
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        padding: 20px;
    }
    .permission-group {
        background-color: var(--box-background-color);
        border-radius: 8px;
        padding: 15px;
    }
    .permission-group-title {
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }
    .form-check {
        margin-bottom: 8px;
        padding-right: 25px;
    }
    .form-check-input {
        margin-left: 8px;
    }
    .form-check-label {
        font-size: 14px;
        color: #444;
    }

    /* RTL Specific Styles */
    [dir="rtl"] .form-check {
        padding-right: 30px;
        padding-left: 0;
    }
    [dir="rtl"] .form-check-input {
        float: right;
        margin-right: -25px;
        margin-left: 0;
    }
</style>

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

<div id="permissions-container">
    @foreach($allGroupedPermissions as $categorySlug => $groupedPermissions)
        <div class="permissions-section {{ $categorySlug === $firstCategory ? 'active' : '' }}"
             data-category="{{ $categorySlug }}">
            <div class="permissions-grid">
                @foreach($groupedPermissions as $group => $perms)
                    <div class="permission-group">
                        <h6 class="permission-group-title">
                            {{ __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                        </h6>
                        @foreach($perms as $perm)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox"
                                       type="checkbox"
                                       value="{{ $perm->id }}"
                                       data-slug="{{ $perm->slug }}"
                                       id="perm-{{ $perm->id }}"
                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                    {{ __($perm->name) }}
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
        let selectedPermissions = new Set(@json($selected ?? []));

        function updateHiddenInput() {
            $('#permissions_all').val([...selectedPermissions].join(','));
        }

        function getBrowsePermissionId(currentCheckbox) {
            const groupContainer = currentCheckbox.closest('.permission-group');
            const browseCheckbox = groupContainer.find('.permission-checkbox').filter(function() {
                const permSlug = $(this).data('slug');
                return permSlug && permSlug.includes('browse-');
            });

            return browseCheckbox.length ? parseInt(browseCheckbox.val()) : null;
        }

        function bindPermissionCheckboxes() {
            $('.permission-checkbox').on('change', function () {
                const id = parseInt($(this).val());
                const permSlug = $(this).data('slug');

                if ($(this).is(':checked')) {
                    selectedPermissions.add(id);

                    if (permSlug && (
                        permSlug.includes('create-') ||
                        permSlug.includes('edit-') ||
                        permSlug.includes('delete-')
                    )) {
                        const browsePermissionId = getBrowsePermissionId($(this));
                        if (browsePermissionId) {
                            selectedPermissions.add(browsePermissionId);
                            $(`#perm-${browsePermissionId}`).prop('checked', true);
                        }
                    }
                } else {
                    selectedPermissions.delete(id);

                    if (permSlug && permSlug.includes('browse-')) {
                        const groupContainer = $(this).closest('.permission-group');
                        groupContainer.find('.permission-checkbox').each(function() {
                            const relatedSlug = $(this).data('slug');
                            if (relatedSlug && (
                                relatedSlug.includes('create-') ||
                                relatedSlug.includes('edit-') ||
                                relatedSlug.includes('delete-')
                            )) {
                                const relatedId = parseInt($(this).val());
                                selectedPermissions.delete(relatedId);
                                $(this).prop('checked', false);
                            }
                        });
                    }
                }
                updateHiddenInput();
            });
        }

        bindPermissionCheckboxes();
        updateHiddenInput();

        $('#permission-tabs .nav-link').on('click', function (e) {
            e.preventDefault();
            $('#permission-tabs .nav-link').removeClass('active tab-highlight');
            $(this).addClass('active tab-highlight');
            const category = $(this).data('category');
            $('.permissions-section').removeClass('active');
            $(`.permissions-section[data-category="${category}"]`).addClass('active');
        });
    });
</script>
