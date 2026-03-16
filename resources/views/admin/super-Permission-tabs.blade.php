@php use App\Helpers\Common;use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

    <style>
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

        :root {
            --primary-color: {{ config('themes.primaryColor') }};
            --secondary-color: {{ config('themes.secondaryColor') }};
            --text-primary-color: {{ config('themes.textPrimaryColor') }};
            --text-secondary-color: {{ config('themes.textSecondaryColor') }};
            --box-background-color: {{ config('themes.boxBackgroundColor') }};
        }

        .label-small-font {
            font-size: 12px;
        }

        .nav-tabs {
            background: var(--secondary-color);
            border-bottom: var(--primary-color);
        }

        .nav-tabs > li > a:hover {
            border-color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .nav-link {
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
            background-color: var(--text-secondary-color);
            border-radius: 8px;
            padding: 15px;
        }

        .permission-group-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 1px solid #ccc;
            display: flex;
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
            margin: 20px 0px;
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

        .agency-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-btn:hover:not(.active) {
            color: #34495e;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border-top: 1px solid #dee2e6;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--secondary-color);
        }

        .card-title {
            color: #ffffff;
            font-weight: 500;
        }

        #permissions-container {
            margin: 0px 20px !important;
        }

        .save-btn {
            margin: 17px 16px !important;
        }
    </style>

</head>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="..." crossorigin="anonymous" /> -->

<body>
  @php
           $activeTab = request('tab', 'packs');

    @endphp
        <!-- Navigation Tabs -->
    <div class="agency-tabs">

          <a href="?tab=packs" class="tab-btn" data-target="packs-tab">{{ __('country manager') }}</a>

        <a href="?tab=vips" class="tab-btn {{ $activeTab == 'vips' ? 'active' : '' }}" data-target="vips-tab">{{ __('area manager') }}</a>



    </div>
    <div id="tab-loading" style="
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            /* transform: translate(-50%, -50%); */
            background: var(--primary-color);
            color: var(--text-primary-color);
            z-index: 9999;
            padding: 30px 40px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        ">
        {{ __('Loading...') }}
    </div>


    <!-- packs Section -->

   <div class="tab-content active" id="packs-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="text-align: left;">{{ __('Permissions') }}</h4>
        </div>

        <div class="card-body">

            @php
                        use App\Models\RoleCategory;
                        use App\Enums\PermissionType;
                        // Group permissions by category first
                        $grouped = $permissions->groupBy('category');
                        $permissionType = $permissionType ??'role-country-manager';
                        $categories = RoleCategory::orderBy('sort')
                        ->select('slug', 'type')
                        ->where(function ($q) use($permissionType) {
                            $q->where('type',$permissionType);
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
            <form action="{{ url('admin/update-super-roles') }}" method="POST">
                @csrf
                <input type="hidden" name="permissions_all" id="permissions_all">
                <input type="hidden" name="role_id" id="super_role_id"value="{{ $superAdminRole->id }}">

                {{-- Category Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
                    @foreach($categories as $category)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               data-category="{{ $category->slug }}"
                               href="#">
                                {{ is_array(__($category->slug)) ? ucwords(str_replace(['-', '_'], ' ', $category->slug)) : __($category->slug) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Select All per Category --}}
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
                                    {{ __('Select All') }} {{ is_array(__($category->slug)) ? ucwords(str_replace(['-', '_'], ' ', $category->slug)) : __($category->slug) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Permissions Grid --}}
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
                                            <label for="group-{{ $categorySlug }}-{{ $group }}" class="label-small-font">
                                                {{ is_array(__(ucwords(str_replace(['-', '_'], ' ', $group)))) ? ucwords(str_replace(['-', '_'], ' ', $group)) : __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                                            </label>
                                        </h6>
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->id }}"
                                                       data-slug="{{ $perm->slug }}"
                                                       data-group="{{ $group }}"
                                                       data-category="{{ $categorySlug }}"
                                                       id="perm-{{ $perm->id }}"
                                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                                    {{ is_array(__($perm->name)) ? $perm->name : __($perm->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
               <br>
                {{-- Save Button --}}
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary save-btn">{{ __('Save Permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>



 <div class="tab-content active" id="vips-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="text-align: left;">{{ __('Permissions') }}</h4>
        </div>

        <div class="card-body">

            @php

                        // Group permissions by category first
                        $grouped = $areaPermissions->groupBy('category');
                        $permissionType = $areaPermissionType ??'role-country-manager';
                        $categories = RoleCategory::orderBy('sort')
                        ->select('slug', 'type')
                        ->where(function ($q) use($permissionType) {
                            $q->where('type',$permissionType);
                        })->get();
                        //dd($categories);
                        $selected = $areaSelectedPermissions ?? [];

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
            <form action="{{ url('admin/update-super-roles') }}" method="POST">
                @csrf
                <input type="hidden" name="area_permissions_all" id="permissions_all">
                <input type="hidden" name="role_id" id="role_id"value="{{ $areaManagerRole->id }}">

                {{-- Category Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
                    @foreach($categories as $category)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               data-category="{{ $category->slug }}"
                               href="#">
                                {{ is_array(__($category->slug)) ? ucwords(str_replace(['-', '_'], ' ', $category->slug)) : __($category->slug) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Select All per Category --}}
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
                                    {{ __('Select All') }} {{ is_array(__($category->slug)) ? ucwords(str_replace(['-', '_'], ' ', $category->slug)) : __($category->slug) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Permissions Grid --}}
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
                                            <label for="group-{{ $categorySlug }}-{{ $group }}" class="label-small-font">
                                                {{ is_array(__(ucwords(str_replace(['-', '_'], ' ', $group)))) ? ucwords(str_replace(['-', '_'], ' ', $group)) : __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                                            </label>
                                        </h6>
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->id }}"
                                                       data-slug="{{ $perm->slug }}"
                                                       data-group="{{ $group }}"
                                                       data-category="{{ $categorySlug }}"
                                                       id="perm-{{ $perm->id }}"
                                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                                    {{ is_array(__($perm->name)) ? $perm->name : __($perm->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>



                <br>
                {{-- Save Button --}}
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary save-btn">{{ __('Save Permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>












<script>





    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'packs';

        const allTabs = document.querySelectorAll('.tab-btn');
        const allTabContents = document.querySelectorAll('[id$="-tab"]');

        let targetElement = null;

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);

            if (target.startsWith(selectedTab)) {
                tab.classList.add('active');
                content.style.display = 'block';
                targetElement = content; // خزن العنصر لعمل scroll إليه لاحقًا
            } else {
                tab.classList.remove('active');
                content.style.display = 'none';
            }

            tab.addEventListener('click', function (e) {
                    e.preventDefault();

                    const currentUrl = new URL(window.location.href);
                    const href = tab.getAttribute('href');
                    const targetUrl = new URL(href, currentUrl.origin);

                    // تحقق أن التنقل داخل نفس الصفحة + تغيير التابة فقط
                    if (currentUrl.pathname === targetUrl.pathname && targetUrl.searchParams.get('tab')) {
                        document.getElementById('tab-loading').style.display = 'block';
                        allTabs.forEach(t => t.style.pointerEvents = 'none');

                        setTimeout(() => {
                            window.location.href = href;
                        }, 300);
                    } else {
                        // لا تعرض اللودر إذا الرابط خارج التابات
                        window.location.href = href;
                    }
                });
            });


        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 500); // تأخير بسيط للتأكد أن العنصر ظاهر
        }
    });


    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            btn.classList.add('active');
            const target = btn.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });



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





