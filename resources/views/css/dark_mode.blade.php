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
    .dark-mode .pagination > .disabled > span:hover {
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
    .dark-mode .box-footer {
        background: var(--dark-primary-color) !important;
    }

    .dark-mode .nav-tabs-custom>.nav-tabs>li.active>a,
    .dark-mode .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover,
    .dark-mode .table-striped>tbody>tr:nth-of-type(odd),
    .dark-mode .pagination-wrapper,
    .dark-mode .nav-tabs-custom>.nav-tabs>li.active:hover>a {
        background: none !important;
    }

    .dark-mode .table > thead > tr > th {
        background-color: var(--dark-secondry-color) !important;
        color: var(--text-primary-color) !important;
        border-bottom-color: rgba(255, 255, 255, 0.08) !important;
    }

    .dark-mode .select2-dropdown {
        background-color: var(--dark-secondry-color) !important;
    }

    .dark-mode,
    .dark-mode .content-header > .breadcrumb > li > a,
    .dark-mode .content-header > h1,
    .dark-mode * {
        color: #ffffff !important;
    }

    .dark-mode ::placeholder {
        color: #aaaaaa !important;
    }

    .dark-mode .table > tbody > tr > td {
        color: var(--text-primary-color) !important;
        border-top-color: rgba(255, 255, 255, 0.05) !important;
    }

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

    /* Apply dark mode on page load if saved */
    document {
    }
</style>

<script>
    (function () {
        try {
            const darkModeSetting = '{{ \App\Models\Setting::where("key", "dark_mode")->value("value") ?? "0" }}';

            if (darkModeSetting === '1' || darkModeSetting === 1) {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
        } catch (e) {
            console.error('Dark mode initialization error:', e);
        }
    })();
</script>
