<style >
    /* Dynamic Theme Variables */
    :root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
        --text-primary-color: {{ config('themes.textPrimaryColor') }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') }};
        --box-background-color: {{ config('themes.boxBackgroundColor') }};
        --background-image: {{ config('themes.backgroundImage') }};
        --second-alpha: #ffffff1a;
        --primary-hover-alpha: {{ config('themes.primaryColor') }}1a;
        --scroll-second-color: {{ config('themes.secondaryColor') }}cc;
        --scroll-first-color: {{ config('themes.primaryColor') }}1a;
    }

    /* Dynamic CSS */
    .skin-black-light .wrapper,
    .skin-black-light .main-sidebar,
    .skin-black-light .left-side {
        background-color: var(--secondary-color) !important;
    }

    .skin-black-light .sidebar-menu > li.header {
        color: var(--text-primary-color) !important;
        background: var(--second-alpha) !important;
    }

    body {
        color: var(--primary-color) !important;
        background-color: var(--secondary-color) !important;
    }

    .skin-black-light .content-wrapper,
    .skin-black-light .main-footer {
        border-left: 1px solid var(--second-alpha) !important;
        background-color: var(--secondary-color) !important;
        background-image: var(--background-image) !important;
    }

    .content-header {
        background-color: var(--box-background-color) !important;
    }

    .content-header > .breadcrumb {
        background: var(--secondary-color) !important;
    }

    .skin-black-light .sidebar a {
        color: var(--text-secondary-color) !important;
    }

    .skin-black-light .sidebar a i {
        color: var(--primary-color) !important;
    }

    .box {
        background: var(--box-background-color) !important;
        color: var(--text-secondary-color) !important;
        border-top: 3px solid var(--second-alpha) !important;
    }

    .skin-black-light .sidebar-menu > li:hover > a,
    .skin-black-light .sidebar-menu > li.active > a {
        color: var(--text-primary-color) !important;
        background: var(--primary-hover-alpha) !important;
    }

    .skin-black-light .sidebar-menu > li:hover > a i,
    .skin-black-light .sidebar-menu > li.active > a i {
        color: var(--text-primary-color) !important;
        background: var(--primary-hover-alpha) !important;

    }

    .skin-black-light .treeview-menu > li.active > a,
    .skin-black-light .treeview-menu > li > a:hover {
        color: var(--text-primary-color) !important;
    }


    .sidebar-menu > li > .treeview-menu {
        background: var(--secondary-color) !important;
    }

    .table.table-hover tbody tr:hover {
        color: var(--text-primary-color) !important;
        background-color: var(--primary-hover-alpha) !important;
    }

    .filter-box {
        color: var(--text-primary-color) !important;
    }

    .filter-box input,
    .filter-box select {
        background-color: var(--second-alpha) !important;
    }

    .input-group .input-group-addon {
        color: var(--text-primary-color) !important;
        background-color: var(--second-alpha) !important;
    }

    .box-footer {
        background-color: var(--second-color) !important;
        border-top: 1px solid var(--second-alpha) !important;

    }

    .btn-primary {
        background-color: var(--primary-color) !important;
    }

    .form-control,
    select,
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        background-color: var(--second-alpha) !important;
        color: var(--text-primary-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .bootstrap-switch.bootstrap-switch-on .bootstrap-switch-handle-on {
        background-color: var(--primary-color) !important;
        color: var(--text-primary-color);
    }

    .box-header > .fa,
    .box-header > .glyphicon,
    .box-header > .ion,
    .box-header .box-title {
        color: var(--text-secondary-color) !important;
    }

    .grid-create-btn > .btn-success {
        background-color: var(--primary-color) !important;
        color: var(--text-primary-color) !important;
    }

    .btn-dropbox,
    .btn-instagram,
    .btn-success {
        background-color: var(--primary-color) !important;
        color: var(--text-primary-color) !important;
    }

    .content-header > .breadcrumb > li > a {
        color: var(--text-primary-color) !important;
    }

    .skin-black-light .main-header > .navbar {
        color: var(--primary-color) !important;
        background-color: var(--secondary-color) a !important;

    }

    .skin-black-light .main-header > .navbar > .sidebar-toggle {
        color: var(--primary-color) !important;
        border-right: 1px solid var(--second-alpha) !important;

    }

    .skin-black-light .main-header > .navbar .nav > li > a,
    .skin-black-light .main-header > .navbar .nav > li > a:active,
    .skin-black-light .main-header > .navbar .nav > li > a:focus,
    .skin-black-light .main-header > .navbar .nav .open > a,
    .skin-black-light .main-header > .navbar .nav .open > a:hover,
    .skin-black-light .main-header > .navbar .nav .open > a:focus,
    .skin-black-light .main-header > .navbar .nav > .active > a {
        background-color: var(--second-color) !important;
        color: var(--primary-color) !important;
        border-left: 1px solid var(--second-alpha) !important;
    }

    .skin-black-light .main-header > .logo {
        background-color: var(--second-color) !important;
        color: var(--primary-color) !important;
        border-right: 1px solid var(--second-alpha) !important;
    }

    .cardHome {
        color: var(--text-primary-color) !important;
        background-color: var(--primary-color) !important;
    }

    .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-primary, .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-primary {
        color: #fff;
        background: var(--primary-color) !important;
    }

    .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-success, .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-success {
        color: #fff;
        background: var(--primary-color) !important;
    }

    .bootstrap-switch .bootstrap-switch-label {
        text-align: center;
        margin-top: -1px;
        margin-bottom: -1px;
        z-index: 100;
        color: #333;
        background: var(--second-alpha) !important;
    }

    * {
        scrollbar-color: var(--scroll-first-color) var(--scroll-second-color);
        scrollbar-width: thin;
    }


    .box-header.with-border {
        border-bottom: 1px solid var(--second-alpha) !important;
    }

    .table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
        border-top: 1px solid var(--primary-hover-alpha) !important;
    }


    .table-responsive {
        border: 1px solid var(--second-alpha) !important;
    }


    .pagination > li > a, .pagination > li > span {
        position: relative;
        float: left;
        padding: 6px 12px;
        margin-left: -1px;
        line-height: 1.42857143;
        color: #337ab7;
        text-decoration: none;
        background-color: var(--second-alpha) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .skin-black-light .main-header > .navbar .sidebar-toggle:hover {

        background: var(--second-alpha) !important;
    }

    .form-control {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: var(--text-secondary-color) !important;
        background-color: #fff;
        background-image: none;
        border: 1px solid var(--primary-hover-alpha) !important;
        border-radius: 4px;

    }


    .select2-dropdown {
        background-color: var(--secondary-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
        border-radius: 4px;

    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--second-alpha) !important;
        color: white;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--text-primary-color) !important;

    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-primary-color) !important;

    }


    .input-group .input-group-addon {
        border-radius: 0;
        border-color: var(--primary-hover-alpha) !important;
        background-color: #fff;
    }


</style>
