<style>
    /* Dark mode: ONLY change white/light surfaces (boxes, tables, backgrounds, text) */
    /* DO NOT change dynamic colors (primary, secondary, buttons, sidebar gradients) */
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
        /* NOTE: --primary-color, --secondary-color, gradients UNCHANGED */
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
    .dark-mode .pagination > .disabled > span:hover {
        background-color: var(--dark-secondry-color) !important;
    }

    .dark-mode .skin-black-light .content-wrapper,
    .dark-mode .skin-black-light .wrapper,
    .dark-mode .select2-container--default .select2-selection--single,
    .dark-mode form,
    .dark-mode .box-footer {
        background: var(--dark-primary-color) !important;
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

    .dark-mode,
    .dark-mode .main-sidebar {
        scrollbar-color: var(--dark-secondry-color) var(--dark-primary-color) !important;
    }

    .dark-mode .content-header {
        background-color: var(--dark-secondry-color) !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: var(--text-primary-color) !important;
    }

    .dark-mode input,
    .dark-mode textarea,
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
            const darkModeSetting = '{{ !empty(settings("dark_mode")) && settings("dark_mode") ? "1" : "0" }}';
            if (darkModeSetting === '1') {
                document.documentElement.classList.add('dark-mode');
            }
        } catch (e) {
            console.error('Dark mode initialization error:', e);
        }
    })();
</script>
