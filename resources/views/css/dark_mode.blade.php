<style>
    .dark-mode {
        /* Background and text colors ONLY */
        --white: #152038;
        --gray-50: #0d1b2a;
        --gray-200: #1a2d4d;
        --gray-300: #233d5a;
        --box-background-color: #152038;
        --table-background-color: #0d1b2a;
        --text-primary-color: #d1d5db;
        --text-secondary-color: #9ca3af;
        --dark-primary-color: rgb(15, 23, 42);
        --dark-secondry-color: rgb(30, 41, 59);
    }

    body.dark-mode, .dark-mode body {
        background-color: #0d1b2a !important;
        color: var(--text-primary-color) !important;
    }

    .dark-mode .content,
    .dark-mode .box,
    .dark-mode .card,
    .dark-mode .table,
    .dark-mode .modal-content,
    .dark-mode .skin-black-light .main-header > .navbar,
    .dark-mode .nav-tabs-custom,
    .dark-mode .form-col,
    .dark-mode .settings-menu button,
    .dark-mode .swal2-popup,
    .dark-mode .mobile-select-menu,
    .dark-mode .form-horizontal {
        background-color: var(--dark-secondry-color) !important;
        color: var(--text-primary-color) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    .dark-mode .pagination > .disabled > a,
    .dark-mode .pagination > .disabled > a:focus,
    .dark-mode .pagination > .disabled > a:hover,
    .dark-mode .pagination > .disabled > span,
    .dark-mode .pagination > .disabled > span:focus,
    .dark-mode .pagination>li>a,
    .dark-mode .pagination > .disabled > span:hover,
    .dark-mode .stat-card,
    .dark-mode .table-section,
    .dark-mode .empty-table,
    .dark-mode .notification-item.unread,
    .dark-mode .notification-item.read,
    .dark-mode .fields-group .input-group.input-group-sm,
    .dark-mode .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .dark-mode .sm\:text-base,
    .dark-mode .user-card,
    .dark-mode .select2-dropdown {
        background-color: var(--dark-secondry-color) !important;
    }

    .dark-mode .skin-black-light .content-wrapper,
    .dark-mode .skin-black-light .wrapper,
    .dark-mode .select2-container--default .select2-selection--single,
    .dark-mode form,
    .dark-mode .nav-tabs,
    .dark-mode .tab-content,
    .dark-mode .settings-sidebar,
    .dark-mode #landPageSettings,
    .dark-mode .performers-card,
    .dark-mode .section-box,
    .dark-mode .bootstrap-datetimepicker-widget table thead tr:first-child th:hover,
    .dark-mode .datepicker table tr td.day:hover,
    .dark-mode .datepicker table tr td.active,
    .dark-mode .datepicker table tr td.active:hover,
    .dark-mode .datepicker table tr td.active.disabled,
    .dark-mode .datepicker table tr td.active.disabled:hover,
    .dark-mode .datepicker table tr td span.active,
    .dark-mode .datepicker table tr td span.active:hover,
    .dark-mode .datepicker table tr td span.active.disabled,
    .dark-mode .datepicker table tr td span.active.disabled:hover,
    .dark-mode .datepicker table tr td span:hover,
    .dark-mode .select2-container--default .select2-selection--multiple,
    .dark-mode .modal-body2,
    .dark-mode .modal-no,
    .dark-mode .interactions-panel,
    .dark-mode .reels-sidebar,
    .dark-mode .sidebarContainer,
    .dark-mode .box-footer {
        background: var(--dark-primary-color) !important;
    }

    .dark-mode .nav-tabs-custom>.nav-tabs>li.active>a,
    .dark-mode .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover,
    .dark-mode .table-striped>tbody>tr:nth-of-type(odd),
    .dark-mode .pagination-wrapper,
    .dark-mode .box-title,
    .dark-mode .modal-footer,
    .dark-mode .box-header > .fa, .dark-mode .box-header > .glyphicon,
    .dark-mode .box-header > .ion, .dark-mode .box-header .box-title,
    .dark-mode .nav-tabs-custom>.nav-tabs>li.active:hover>a {
        background: none !important;
        color: unset !important;
    }

    .dark-mode .skeleton {
        background: var(--dark-secondry-color);
    }

    .dark-mode .box-header.with-border,
    .dark-mode .section-header {
        border-bottom: 1px solid var(--white) !important;
    }

    .dark-mode .btn:hover,
    .dark-mode .btn-success:hover,
    .dark-mode .button:hover {
        color: black !important;
    }

    .dark-mode .table > thead > tr > th {
        background-color: var(--dark-secondry-color) !important;
        color: var(--text-primary-color) !important;
        border-bottom-color: rgba(255, 255, 255, 0.08) !important;
    }

    .dark-mode,
    .dark-mode .content-header > .breadcrumb > li > a,
    .dark-mode .content-header > h1,
    .dark-mode *:not([class*="phpdebugbar"]):not(.phpdebugbar *) {
        color: #ffffff !important;
    }

    .dark-mode .datepicker table tr td.old,
    .dark-mode .datepicker table tr td.new {
        color: #515151 !important;
    }

    .dark-mode ::placeholder {
        color: #aaaaaa !important;
    }

    .dark-mode .table > tbody > tr > td {
        color: var(--text-primary-color) !important;
        border-top-color: rgba(255, 255, 255, 0.05) !important;
    }

    .dark-mode,
    .dark-mode .box-body,
    .dark-mode .table-responsive,
    .dark-mode .box-body.table-responsive,
    .dark-mode .content-wrapper,
    .dark-mode .CardwalletLogsTable,
    .dark-mode .main-sidebar {
        scrollbar-color: var(--dark-secondry-color) var(--dark-primary-color) !important;
        scrollbar-width: thin;
    }

    .dark-mode .content-header {
        background-color: var(--dark-secondry-color) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: var(--text-primary-color) !important;
    }

    .dark-mode input,
    .dark-mode textarea,
    .dark-mode .table > tbody > tr:hover,
    .dark-mode .data-table tr:hover ,
    .dark-mode .agency-header,
    .dark-mode .card-header,
    .dark-mode .table tbody tr:nth-child(even),
    .dark-mode select {
        background-color: var(--dark-primary-color) !important;
        color: var(--text-primary-color) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .dark-mode .select2-container--default .select2-results__option[aria-selected=true],
    .dark-mode .select2-container--default .select2-results__option--highlighted[aria-selected] {
        color: var(--white) !important;
    }

    .dark-mode .form-control {
        background-color: var(--dark-primary-color) !important;
        color: var(--text-primary-color) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .dark-mode .form-control:focus {
        background-color: var(--dark-primary-color) !important;
        color: var(--text-primary-color) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    .dark-mode .btn-default,
    .dark-mode .btn:hover,
    .dark-mode .btn-success:hover,
    .dark-mode .button:hover {
        color: var(--text-secondary-color) !important;
    }

    /* Apply dark mode on page load if saved */
    document {
    }
</style>

<script>
    (function () {
        try {
            // Check if user has set a preference in localStorage
            const userDarkModePreference = localStorage.getItem('admin_dark_mode');

            console.log(userDarkModePreference)
            if (userDarkModePreference !== null) {
                // User has set a preference, use it
                const isDarkMode = userDarkModePreference === '1';
                document.documentElement.classList.toggle('dark-mode', isDarkMode);
            } else {
                // No user preference, fall back to global setting
                const darkModeSetting = '{{ \App\Models\Setting::where("key", "dark_mode")->value("value") ?? "0" }}';

                if (darkModeSetting === '1' || darkModeSetting === 1) {
                    document.documentElement.classList.add('dark-mode');
                } else {
                    document.documentElement.classList.remove('dark-mode');
                }
            }
        } catch (e) {
            console.error('Dark mode initialization error:', e);
        }
    })();
</script>
