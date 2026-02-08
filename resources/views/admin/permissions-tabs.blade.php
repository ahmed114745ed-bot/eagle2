@php
    use App\Models\RoleCategory;
    use App\Enums\PermissionType;
    // Group permissions by category first
    $grouped = $permissions->groupBy('category');
    $categories = RoleCategory::orderBy('sort')
    ->select('slug', 'type')
    ->where(function ($q) {
        $q->where('type', PermissionType::ADMIN->value)
          ->orWhere('slug', 'general');
    })->get();
    //dd($categories);
    $selected = $selectedPermissions ?? [];

    // Pre-process all permissions by category and group
    $allGroupedPermissions = [];
 foreach ($grouped as $categorySlug => $categoryPermissions) {
    $allGroupedPermissions[$categorySlug] = $categoryPermissions->groupBy(function ($permission) {
        $slug = $permission->slug;

        if (str_contains($slug, '-switch-')) {
            // Split by '-switch-'
            $parts = explode('-switch-', $slug);
            // $parts[1] is what comes after 'switch-'
            return $parts[1];  // group by 'user' or 'agency' or whatever after switch-
        } else {
            // Normal grouping: remove first part and group by the rest
            $parts = explode('-', $slug);
            array_shift($parts);
            return implode('-', $parts);
        }
    });
}
    //dd($allGroupedPermissions);
    $firstCategory = $categories->first()->slug ?? null;
@endphp

{{-- <style>
    .label-small-font {
    font-size: 12px;
}
    .nav-tabs{
        background: #f9f9f9;
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
    }
    .permission-group {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
    }
    .permission-group-title {
        /* text-align: center; */
        font-size: 16px;
        font-weight: bold;
        color: #333;
        /* margin-bottom: 15px; */
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
        display: flex;
        /* align-items: center;
        justify-content: center; */
        gap: 10px;
    }
    .group-select-all {
        margin: 0;
    }
    .form-check {
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .form-check-input {
        margin: 0;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .form-check-label {
        font-size: 14px;
        color: #444;
        line-height: 1.4;
        word-wrap: break-word;
        flex: 1;
    }

    /* RTL Specific Styles */
    [dir="rtl"] .form-check {
        padding-right: 0;
        padding-left: 25px;
        flex-direction: row-reverse;
    }
    [dir="rtl"] .form-check-input {
        margin-right: 0;
        margin-left: 0;
    }
    [dir="rtl"] .permission-group-title {
        flex-direction: row-reverse;
    }

    .rtl .fields-group .form-group {
    display: block !important;
}
</style> --}}

<style>
/* ===============================
   GLOBAL TUNING
================================ */
:root {
    --soft-bg: #f6f7fb;
    --card-bg: #ffffff;
    --border-color: #e5e7eb;
    --text-muted: #6b7280;
    --radius: 12px;
    --shadow-soft: 0 6px 18px rgba(0,0,0,.05);
}

/* ===============================
   TABS
================================ */
.nav-tabs {
    background: #f9f9f9;
    padding: 6px;
    border-radius: var(--radius);
    border: none;
}

.nav-tabs .nav-item {
    margin: 0 4px;
}

.nav-tabs .nav-link {
    border-radius: 10px;
    padding: 8px 18px;
    color: #555;
    font-weight: 500;
    transition: all .25s ease;
}

.nav-tabs .nav-link:hover {
    background: rgba(0,0,0,.05);
}

.nav-tabs .nav-link.active {
    background: var(--primary-color);
    color: #fff;
    box-shadow: var(--shadow-soft);
}

/* ===============================
   CATEGORY SELECT ALL
================================ */
.category-select-all-container {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 12px 20px;
    box-shadow: var(--shadow-soft);
}

/* ===============================
   PERMISSIONS GRID
================================ */
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 18px;
}

/* ===============================
   PERMISSION GROUP CARD
================================ */
.permission-group {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 16px 16px 14px;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border-color);
    transition: all .25s ease;
}

.permission-group:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(0,0,0,.08);
}

/* ===============================
   GROUP TITLE
================================ */
.permission-group-title {
    font-size: 15px;
    font-weight: 600;
    color: #111;
    padding-bottom: 8px;
    margin-bottom: 10px;
    border-bottom: 1px dashed var(--border-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ===============================
   CHECKBOXES
================================ */
.form-check {
    padding: 6px 8px;
    border-radius: 8px;
    transition: background .2s ease;
}

.form-check:hover {
    background: #f3f4f6;
}

.form-check-input {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.form-check-label {
    font-size: 14px;
    color: #374151;
    cursor: pointer;
}

/* ===============================
   ACTIVE STATES
================================ */
.permission-checkbox:checked + .form-check-label {
    font-weight: 600;
    color: var(--primary-color);
}

/* ===============================
   RTL SUPPORT
================================ */
[dir="rtl"] .permission-group-title {
    justify-content: flex-end;
}

[dir="rtl"] .form-check {
    flex-direction: row-reverse;
    text-align: right;
}

/* ===============================
   SMALL SCREENS
================================ */
@media (max-width: 768px) {
    .permissions-grid {
        grid-template-columns: 1fr;
    }

    .nav-tabs {
        overflow-x: auto;
        white-space: nowrap;
    }
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

<div class="category-select-all-container mb-3" style="padding: 0 20px;">
    @foreach($categories as $category)
        <div class="category-select-wrapper {{ $category->slug === $firstCategory ? 'active' : '' }}"
             data-category="{{ $category->slug }}"
             style="display: {{ $category->slug === $firstCategory ? 'block' : 'none' }};">
            <div class="form-check">
                <input class="form-check-input category-select-all"
                       type="checkbox"
                       data-category="{{ $category->slug }}"
                       id="category-{{ $category->slug }}">
                <label class="form-check-label fw-bold" for="category-{{ $category->slug }}">
                    {{ __('Select All') }} {{ __($category->slug) }}
                </label>
            </div>
        </div>
    @endforeach
</div>

<div id="permissions-container">
    @foreach($allGroupedPermissions as $categorySlug => $groupedPermissions)
        <div class="permissions-section {{ $categorySlug === $firstCategory ? 'active' : '' }}"
             data-category="{{ $categorySlug }}">
            <div class="permissions-grid">
                @foreach($groupedPermissions as $group => $perms)
                    <div class="permission-group">
                        <h6 class="permission-group-title">
                            <input class="form-check-input group-select-all"
                                   type="checkbox"
                                   data-group="{{ $group }}"
                                   data-category="{{ $categorySlug }}"
                                   id="group-{{ $categorySlug }}-{{ $group }}">
                            <label for="group-{{ $categorySlug }}-{{ $group }}"class="label-small-font">
                                {{ __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                            </label>
                        </h6>
                        @foreach($perms as $perm)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox"
                                       type="checkbox"
                                       value="{{ $perm->id }}"
                                       data-slug="{{ $perm->slug }}"
                                       data-group="{{ $group }}"
                                       data-category="{{ $categorySlug }}"
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

        function updateGroupCheckboxState(group, category) {
            const groupCheckboxes = $(`.permission-checkbox[data-group="${group}"][data-category="${category}"]`);
            const groupSelectAll = $(`.group-select-all[data-group="${group}"][data-category="${category}"]`);

            const totalCheckboxes = groupCheckboxes.length;
            const checkedCheckboxes = groupCheckboxes.filter(':checked').length;

            // Only checked when ALL permissions are selected, otherwise unchecked
            if (checkedCheckboxes === totalCheckboxes) {
                groupSelectAll.prop('checked', true).prop('indeterminate', false);
            } else {
                groupSelectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        function updateCategoryCheckboxState(category) {
            const categoryCheckboxes = $(`.permission-checkbox[data-category="${category}"]`);
            const categorySelectAll = $(`.category-select-all[data-category="${category}"]`);

            const totalCheckboxes = categoryCheckboxes.length;
            const checkedCheckboxes = categoryCheckboxes.filter(':checked').length;

            // Only checked when ALL permissions are selected, otherwise unchecked
            if (checkedCheckboxes === totalCheckboxes) {
                categorySelectAll.prop('checked', true).prop('indeterminate', false);
            } else {
                categorySelectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        function bindPermissionCheckboxes() {
            $('.permission-checkbox').on('change', function () {
                const id = parseInt($(this).val());
                const permSlug = $(this).data('slug');
                const group = $(this).data('group');
                const category = $(this).data('category');

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

                updateGroupCheckboxState(group, category);
                updateCategoryCheckboxState(category);
                updateHiddenInput();
            });
        }

        function bindGroupSelectAll() {
            $('.group-select-all').on('change', function() {
                const group = $(this).data('group');
                const category = $(this).data('category');
                const isChecked = $(this).is(':checked');

                const groupCheckboxes = $(`.permission-checkbox[data-group="${group}"][data-category="${category}"]`);

                groupCheckboxes.each(function() {
                    const id = parseInt($(this).val());
                    const currentlyChecked = $(this).is(':checked');

                    if (isChecked && !currentlyChecked) {
                        selectedPermissions.add(id);
                        $(this).prop('checked', true);
                    } else if (!isChecked && currentlyChecked) {
                        selectedPermissions.delete(id);
                        $(this).prop('checked', false);
                    }
                });

                updateCategoryCheckboxState(category);
                updateHiddenInput();
            });
        }

        function bindCategorySelectAll() {
            $('.category-select-all').on('change', function() {
                const category = $(this).data('category');
                const isChecked = $(this).is(':checked');

                const categoryCheckboxes = $(`.permission-checkbox[data-category="${category}"]`);
                const categoryGroupCheckboxes = $(`.group-select-all[data-category="${category}"]`);

                categoryCheckboxes.each(function() {
                    const id = parseInt($(this).val());
                    const currentlyChecked = $(this).is(':checked');

                    if (isChecked && !currentlyChecked) {
                        selectedPermissions.add(id);
                        $(this).prop('checked', true);
                    } else if (!isChecked && currentlyChecked) {
                        selectedPermissions.delete(id);
                        $(this).prop('checked', false);
                    }
                });

                // Update all group checkboxes in this category
                categoryGroupCheckboxes.each(function() {
                    $(this).prop('checked', isChecked).prop('indeterminate', false);
                });

                updateHiddenInput();
            });
        }

        function initializeGroupCheckboxes() {
            // Initialize all group checkboxes based on current state
            $('.group-select-all').each(function() {
                const group = $(this).data('group');
                const category = $(this).data('category');
                updateGroupCheckboxState(group, category);
            });
        }

        function initializeCategoryCheckboxes() {
            // Initialize all category checkboxes based on current state
            $('.category-select-all').each(function() {
                const category = $(this).data('category');
                updateCategoryCheckboxState(category);
            });
        }

        bindPermissionCheckboxes();
        bindGroupSelectAll();
        bindCategorySelectAll();
        initializeGroupCheckboxes();
        initializeCategoryCheckboxes();
        updateHiddenInput();

        $('#permission-tabs .nav-link').on('click', function (e) {
            e.preventDefault();
            $('#permission-tabs .nav-link').removeClass('active tab-highlight');
            $(this).addClass('active tab-highlight');
            const category = $(this).data('category');
            $('.permissions-section').removeClass('active');
            $(`.permissions-section[data-category="${category}"]`).addClass('active');

            // Show/hide the appropriate category select-all checkbox
            $('.category-select-wrapper').hide().removeClass('active');
            $(`.category-select-wrapper[data-category="${category}"]`).show().addClass('active');
        });
    });
</script>
