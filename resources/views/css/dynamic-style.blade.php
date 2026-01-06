<style >
    /* Dynamic Theme Variables */
    :root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
        --green-color: {{ config('themes.greenColor') }};
        --text-primary-color: {{ config('themes.textPrimaryColor') }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') }};
        --box-background-color: {{ config('themes.boxBackgroundColor') }};
        --table-background-color: {{ config('themes.tableBackGroundColor')}}
        --background-image: {{ config('themes.backgroundImage') }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
        --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
        --primary-hover-alpha: {{ config('themes.primaryColor')}}33;
        --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
        --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;


        --inverse-color: {{getLighterColor(config('themes.primaryColor'))}};
        --inverse-box-color: {{adjustTextColor(config('themes.boxBackgroundColor'))}};
        --success-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
        --primary-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
    }

    /* .col-sm-8 {
    width: 80.66666667%;
} */

    .col-sm-2 {
        width: auto;
    }

    .rtl label {
        margin: 0 !important;
    }

    /*.ltr label {*/
    /*    margin: 0 !important;*/
    /*}*/
/*
    .ltr .fields-group .form-group{

       display: flex !important;
    } */

    .pagination {
        padding-left: revert !important;
    }

    .fileinput-remove{
        display: none;

    }
    .rtl .pull-right{
        float: left !important;
    }

    .box-footer .pull-right {
    }

    .box-info .btn-group.pull-right {
        float: right !important;
    }

    .rtl .box-info .pull-right{
        float: right !important;
    }

    .rtl .box-info label {
        margin: 5px 10px 0 0 !important;
    }

    .btn-success {
        background: var(--success-button) !important;
        /*background: #FF9428 !important;*/
        /* background: linear-gradient(90deg, #2d7dffb8 0%, #21c6fba8 100%)!important; */
    }

    /* Modern Dark Filter Design */
    .box-header.with-border.filter-box {
        border-radius: 16px !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }

    .box-header.with-border.filter-box .row {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 20px 16px !important;
        align-items: start !important;
        justify-content: flex-start !important;
    }

    .box-header.with-border.filter-box .row > div[class*="col-"] {
        min-width: 0 !important;
    }

    .box-header.with-border.filter-box .row .col-md-6,
    .box-header.with-border.filter-box .row .col-md-4,
    .box-header.with-border.filter-box .row .col-md-3,
    .box-header.with-border.filter-box .row .col-md-2 {
        flex: none !important;
        width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        float: none !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .box-header.with-border.filter-box .box-body {
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
    }

    .box-header.with-border.filter-box .fields-group {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 20px !important;
        margin-bottom: 10px;
    }

    .box-header.with-border.filter-box .fields-group .form-group {
        /*display: block !important;*/
        width: auto !important;
        flex-direction: unset !important;
    }

    /* Filter Form Group Styling */
    .box-header.with-border.filter-box .form-group {
        margin-bottom: 0 !important;
        position: relative !important;
    }

    .box-header.with-border.filter-box label {
        position: absolute !important;
        top: -20% !important;
        left: 5% !important;
        transform: translateX(-50%) !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        pointer-events: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 2 !important;
        background: white !important;
        padding: 0 4px !important;
    }

    .rtl .box-header.with-border.filter-box label {
        right: 5% !important;
        left: auto !important;
    }

    /* Filter Input and Select Styling */
    .box-header.with-border.filter-box .form-control,
    .box-header.with-border.filter-box select {
        border-radius: 10px !important;
        padding: 10px 14px !important;
        height: 42px !important;
        font-size: 13px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        width: 100% !important;
    }

    .box-header.with-border.filter-box .form-control::placeholder {
        color: #7d8aad !important;
    }

    .box-header.with-border.filter-box .form-control:focus,
    .box-header.with-border.filter-box select:focus {
        outline: none !important;
    }

    .box-header.with-border.filter-box .form-control:focus + label,
    .box-header.with-border.filter-box select:focus + label,
    .box-header.with-border.filter-box .form-control:not(:placeholder-shown) + label,
    .box-header.with-border.filter-box select:not([value=""]) + label {
        top: -8px !important;
        left: 10px !important;
        font-size: 9px !important;
        padding: 0 6px !important;
        border-radius: 4px !important;
    }

    /* Select Option Styling */
    .box-header.with-border.filter-box select option {
        background: #1a1f2e !important;
        color: #ffffff !important;
    }

    /* Icon styling for inputs */
    .box-header.with-border.filter-box .input-group {
        display: flex !important;
        align-items: center !important;
    }

    .box-header.with-border.filter-box .input-group-addon {
        background: transparent !important;
        border: none !important;
        padding: 0 8px !important;
        color: #8b9dcf !important;
        font-size: 14px !important;
    }

    /* Filter Footer Buttons */
    .box-header.with-border.filter-box .box-footer {
        background: transparent !important;
        padding: 0 !important;
        border-radius: 0 !important;
        grid-column: 1 / -1 !important;
    }

    /* Responsive Filter Design */
    @media (max-width: 1400px) {
        .box-header.with-border.filter-box .row {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 1024px) {
        .box-header.with-border.filter-box {
            padding: 20px !important;
        }

        .box-header.with-border.filter-box .row {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 16px 12px !important;
        }
    }


    @media (max-width: 768px) {
        .box-header.with-border.filter-box {
            padding: 16px !important;
            margin: 0 0 16px 0 !important;
        }

        .box-header.with-border.filter-box .row {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }

        .box-header.with-border.filter-box .box-footer {
            margin-top: 12px !important;
        }
    }

    .box-header form {
        border-radius: 36px;
    }

    .input-group-sm > .form-control, .input-group-sm > .input-group-addon, .input-group-sm > .input-group-btn > .btn {
        height: 35px !important;
    }

    .box-header form .box-footer {
        border-radius: 36px;
    }

    .pagination > .active > a, .pagination > .active > a:focus, .pagination > .active > a:hover, .pagination > .active > span, .pagination > .active > span:focus, .pagination > .active > span:hover {
        z-index: 2;
        color: #fff;
        cursor: default;
        background: var(--primary-button) !important;
        border-color: #337ab7;
    }
    .skin-black-light .content-header {
    background: var(--secondary-color) !important;
    box-shadow: none;
   }

    input:checked+.slider {
        background: var(--primary-button) !important ;
    }

    .content-header>.breadcrumb>li>a {
        color: var(--primary-color) !important;
        text-decoration: none;
        font-size: var(--bs-breadcrumb-font-size);
        display: inline-block;
    }

    /* Dynamic CSS */
    .skin-black-light .main-sidebar,
    .skin-black-light .left-side {
        background-color: var(--box-background-color) !important;
    }


    .skin-black-light .main-sidebar{
        background-color: var(--secondary-color) !important;
    }

    .skin-black-light .sidebar-menu > li.header {
        color: var(--text-secondary-color) !important;
        background: var(--second-alpha) !important;
        margin-top: 9px;
    }

    * {
        color: var(--text-secondary-color);
    }

    body {
        color: var(--text-secondary-color) !important;
        background-color: var(--box-background-color) !important;
        background-image: var(--brand_background-image) !important;

        background-repeat: no-repeat !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
    }

    .skin-black-light .content-wrapper,
    /*.skin-black-light .main-footer ,*/
    /*.skin-black-light .main-header > .navbar,*/
    .skin-black-light .wrapper
    {
        border-left: 1px solid var(--second-alpha) !important;
        background-color: var(--box-background-color) !important;
        background-image: var(--brand_background-image) !important;
        background-repeat: no-repeat !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
    }

    form {
        background-color: var(--secondary-color) !important;
        filter: brightness(0.85);
    }

    .rtl .small-box .icon{
        width: 100%;
        text-align: left;
        right: -2px !important;
    }

    .skin-black-light .main-header > .navbar {
        color: var(--primary-color) !important;
        background-color: transparent !important;
        /*background-color: var(--box-background-color) a !important;*/
    }

    .content-header {
        padding: 15px 20px !important;
        margin: 24px 16px !important;
        background-color: var(--box-background-color) !important;
        filter: drop-shadow(0px 2px 8px rgba(0, 0, 0, 0.05)) !important;
        border-radius: 5px !important;
        /*padding: 28px;*/
        border: none !important;
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

    .rtl .col-md-1,
    .rtl .col-md-2,
    .rtl .col-md-3,
    .rtl .col-md-4,
    .rtl .col-md-5,
    .rtl .col-md-6,
    .rtl .col-md-7,
    .rtl .col-md-8,
    .rtl .col-md-9,
    .rtl .col-md-10,
    .rtl .col-md-11,
    .rtl .col-md-12 {
        float: right;
    }

    .rtl .box-header .pull-left {
        float: right !important;
    }

    .iti { position: relative; z-index: 1050 !important; }
    .iti__country-list {
        z-index: 3000 !important;
    }

    .iti {
        direction: ltr !important;
        text-align: left !important; /* optional, for consistent alignment */
    }

    .iti__country-list, .iti__country {
        direction: ltr !important;
        text-align: left !important;
    }

    .rtl .iti--allow-dropdown .iti__flag-container,
    .rtl .iti--separate-dial-code .iti__flag-container {
        left: auto;
        right: auto;
    }

    .box {
        background: var(--secondary-color) !important;
        filter: brightness(0.85);
        color: var(--text-secondary-color) !important;
        border-top: 3px solid var(--second-alpha) !important;
        border-radius: 20px!important;
        padding: 15px;

    }

    .table .table {
        background: var(--table-background-color) !important;
        color: var(--table-background-color) !important;
    }

    .skin-black-light .sidebar-menu > li:hover > a,
    .skin-black-light .sidebar-menu > li.active > a {
        color: var(--text-secondary-color) !important;
        background: var(--primary-hover-alpha) !important;
    }

    .skin-black-light .sidebar-menu > li:hover > a i,
    .skin-black-light .sidebar-menu > li.active > a i {
        color: var(--text-secondary-color) !important;
        background: transparent !important;

    }

    .skin-black-light .treeview-menu > li.active > a,
    .skin-black-light .treeview-menu > li > a:hover {
        color: var(--text-secondary-color) !important;
    }


    .sidebar-menu > li > .treeview-menu {
        background: var(--secondary-color) !important;
    }

    .form-control, select, .select2-container .select2-selection--single, .select2-container .select2-selection--multiple{
        background: none !important;
    }

    .table.table-hover tbody tr:hover {
        color: var(--text-secondary-color) !important;
        background-color: var(--primary-hover-alpha) !important;
    }

    .filter-box {
        color: var(--inverse-box-color) !important;
    }

    .filter-box input,
    .filter-box select {
        background-color: var(--second-alpha) !important;
    }

    .input-group .input-group-addon {
        color: var(--text-secondary-color) !important;
        background-color: var(--second-alpha) !important;
    }

    .rtl .input-group .form-control {
        float: right;
    }

    .box-footer {
        border-top: unset;
    }

    .btn-info {
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-color) 100%)!important;
        color: var(--text-secondary-color); !important;
        border-color: var(--secondary-color);
    }

    .btn-info:hover{
        color: var(--secondary-color); !important;
    }

    .btn-primary {
        background-color: var(--primary-color) !important;
        color: var(--text-secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-primary:hover{
        color: var(--secondary-color); !important;
    }

    .form-control,
    select,
    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        background-color: var(--secondary-color) !important;
        color: var(--text-secondary-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .bootstrap-switch.bootstrap-switch-on .bootstrap-switch-handle-on {
        background-color: var(--primary-color) !important;
        color: var(--text-secondary-color);
    }

    .box-header > .fa,
    .box-header > .glyphicon,
    .box-header > .ion,
    .box-header .box-title {
        color: var(--text-secondary-color) !important;
    }

    .grid-create-btn > .btn-success {
        background-color: var(--primary-color) !important;
        color: var(--inverse-color) !important;
    }

    .btn-dropbox,
    .btn-instagram,
    .btn-twitter,
    .btn-success {
        background-color: var(--primary-color) !important;
        color: var(--text-secondary-color) !important;
    }

    .btn-dropbox:hover,
    .btn-instagram:hover,
    .btn-twitter:hover,
    .btn-success:hover {
        background: var(--primary-color);
        filter: brightness(0.85);
        color: var(--secondary-color)
    }

    .content-header > .breadcrumb > li > a {
        color: var(--text-secondary-color) !important;
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
        /*border-left: 1px solid var(--second-alpha) !important;*/
    }

    .skin-black-light .main-header > .navbar .navbar-custom-menu .navbar-nav > li > a,
    .skin-black-light .main-header > .navbar .navbar-right > li > a {
        border-left: unset !important;
    }

    .skin-black-light .main-header > .logo {
        background-color: var(--second-color) !important;
        color: var(--primary-color) !important;
        border-right: 1px solid var(--second-alpha) !important;
    }

    .cardHome {
        color: var(--inverse-box-color) !important;
        background-color: var(--secondary-color) !important;
    }

    .small-box {
        background-color: var(--secondary-color);
    }

    .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-primary, .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-primary {
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

    .nav-tabs > li {
        float: left;
    }

    .rtl .nav-tabs > li {
        float: right;
    }

    .rtl .box-body .fields-group [class*="col-md-12"] {
        float: left; !important;
    }

    .rtl [class*="col-md-12"] {
        float: none; !important;
    }


    .rtl [class*="col-md-6"] {
        float: right;
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
        border: 1px solid var(--primary-color) !important;
        border-radius: 4px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--text-secondary-color) !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--text-secondary-color) !important;

    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-secondary-color) !important;

    }


    .input-group .input-group-addon {
        border-radius: 0;
        border-color: var(--primary-hover-alpha) !important;
        background-color: #fff;
    }

    .modal-backdrop {
        position: static; !important;
    }

    .modal-content {
        position: relative;
        background-color: var(--box-background-color) !important;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;

        border-radius: 6px;
        outline: 0;
        -webkit-box-shadow: 0 3px 9px rgba(0,0,0,.5);
        box-shadow: 0 3px 9px rgba(0,0,0,.5);
    }

    .modal-header {
        min-height: 16.43px;
        padding: 15px;
        border-bottom: 1px solid var(--primary-hover-alpha) !important;
    }

    .modal-footer {
        padding: 15px;
        text-align: right;
        border-top: 1px solid var(--primary-hover-alpha) !important;
    }

    .main-footer {
        background: transparent; !important;
        padding: 15px;
        color: #444;
        border-top: 1px solid var(--primary-hover-alpha) !important;
    }

    .user-type-badges {
    display: flex;
    align-items: center;
    gap: 5px;
}

.user-type-badges img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    transition: all 0.2s ease;
}


    .skin-black-light .content-wrapper, .skin-black-light .main-footer{
        background-image: none; !important;
    }

    .dropdown-toggle {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--inverse-box-color) !important;
        border: 1px solid transparent !important;
    }

    .btn-default {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--text-secondary-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .btn-default:hover {
        background-color: var(--second-color) !important;
        color: var(--text-secondary-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .rtl .modal-footer {
        text-align: left;
    }

    .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-default, .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-default {



        background: var(--primary-hover-alpha) !important;
        color: var(--text-secondary-color) !important;

    }

    .img-thumbnail {
        display: inline-block;
        max-width: 100%;
        height: auto;
        padding: 4px;
        line-height: 1.42857143;
        background-color: transparent !important;
        border: 1px solid var(--primary-hover-alpha) !important;
        border-radius: 4px;
        -webkit-transition: all .2s ease-in-out;
        -o-transition: all .2s ease-in-out;
        transition: all .2s ease-in-out;
    }

    /* Default for web */
    .nprogress-custom-parent {
        overflow: hidden;
        position: relative !important;
    }

    .rtl .pull-right>.dropdown-menu {
        right: auto;
        left: 0;
    }

    .popover {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1060;
        display: none;
        max-width: 276px;
        padding: 1px;
        font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: left;
        text-align: start;
        text-decoration: none;
        text-shadow: none;
        text-transform: none;
        letter-spacing: normal;
        word-break: normal;
        word-spacing: normal;
        word-wrap: normal;
        white-space: normal;
        background-color: var(--box-background-color) !important ;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;
        border: 1px solid rgba(0,0,0,.2);
        border-radius: 6px;
        -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
        box-shadow: 0 5px 10px rgba(0,0,0,.2);
        line-break: auto;
    }

    .popover.top>.arrow:after {
        bottom: 1px;
        margin-left: -10px;
        content: " ";
        border-top-color: var(--box-background-color) !important ;
        border-bottom-width: 0;
    }

    /* .dropdown-menu {
        position: absolute;
        top: 100%;
        z-index: 1000;
        display: none;
        float: left;
        min-width: 160px;
        padding: 5px 0;
        margin: 2px 0 0;
        font-size: 14px;
        text-align: right;
        list-style: none;
        background-color: var(--secondary-color) !important;
        filter: brightness(0.80);
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        -webkit-box-shadow: 0 6px 12px rgba(0,0,0,.175);
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
        color: var(--inverse-box-color) !important;
    } */

    .dropdown-menu {
    position: absolute;
    top: 100%;
    z-index: 1000;
    display: none;
    min-width: 160px;
    padding: 5px 0;
    margin: 2px 0 0;
    font-size: 14px;
    list-style: none;
    background-color: var(--secondary-color) !important;
    filter: brightness(0.80);
    -webkit-background-clip: padding-box;
    background-clip: padding-box;
    border: 1px solid var(--primary-hover-alpha) !important;
    border-radius: 4px;
    -webkit-box-shadow: 0 6px 12px rgba(0,0,0,.175);
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
    color: var(--inverse-box-color) !important;
}
.flag-image {
    height: 30px;  /* adjust size */
    width: auto;
    margin-top: 10px;
    border-radius: 4px; /* optional */
}

/* RTL override */
html.rtl .dropdown-menu {
    text-align: right;
    right: 0;
    left: auto;
    float: right;
}

/* LTR override */
html.ltr .dropdown-menu {
    text-align: left;
    left: 0;
    right: auto;
    float: left;
}




    .rtl .dropdown-menu {
        left: unset !important;
    }

    .ltr .dropdown-menu {
        left: 0;
    }

    .rtl .dropdown-menu {
        right: 0;
    }


    .rtl  .column-reward .rtlSvga {
            direction: ltr !important;
        }

    select>option{
        background-color: var(--box-background-color) !important;
        color: var(--inverse-box-color) !important;

    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--primary-hover-alpha) !important;
        transition: .4s;
        border-radius: 34px;
    }

    .navbar-nav>.user-menu>.dropdown-menu>.user-footer {
        background-color: var(--box-background-color) !important;
        padding: 10px;
    }

    .skin-black-light .main-header li.user-header {
        background-color: var(--box-background-color) !important;
    }

    .navbar-nav>.user-menu>.dropdown-menu>li.user-header>img {
        z-index: 5;
        height: 90px;
        width: 90px;
        border: 3px solid  var(--primary-hover-alpha) !important;

    }

    .navbar-nav>.user-menu>.dropdown-menu>li.user-header>p {
        z-index: 5;
        color: var(--inverse-box-color) !important;
        font-size: 17px;
        margin-top: 10px;
    }

    .inputs_cus_form {
        color: var( --text-secondary-color)!important;
        background-color: var(--box-background-color) !important;
    }

    .dd-handle {
        display: block;
        margin: 1px 0;
        padding: 8px 10px;
        color: var( --text-secondary-color)!important;
        text-decoration: none;
        border: 1px solid #ddd;
        background:  var(--box-background-color) !important;
    }

    a {
        color: var(--text-secondary-color); !important;
    }
    .dropdown-menu>li>a {
        color: var(--text-secondary-color) !important;
    }

    .ltr .pull-role {
        width: 70px;
        position: relative;
        font-size: 10px;

        position: relative;
        right: -91px;
        top: 6px;
    }

    .rtl {
        direction: rtl;
        text-align: right;
    }

    .rtl .sidebar-menu {
        text-align: right;
    }

    .rtl .main-sidebar {
        right: -var(--sidebar-width);
        left: auto;
        transition: right 0.3s ease;
        border-radius: 20px 0 0 20px !important;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.1) !important;
    }

    .rtl.sidebar-open .main-sidebar {
        right: 0;
    }

    .rtl .content-wrapper,
    .rtl .main-footer {
        margin-left: 0;
        margin-right: 17.5%;
    }

    .rtl .treeview-menu {
        padding-right: 10px;
    }

    .rtl .fa-angle-left {
        direction: rtl;
        left: 0px;
    }

    .box-header {
        padding: 30px;
        color: #444;
        display: block;
        position: relative;

    }

    .rtl .breadcrumb {
        left: 10px !important;
        right: auto !important;
        direction: rtl;
        display: flex;
        justify-content: flex-start;
    }

    /* .rtl .content-header {
        display: flex ;
         height: 56px;
    }
    .rtl .content-header h1{
        left: 0px;
        position: absolute;
    } */
    .rtl .sidebar-toggle {
        direction: rtl !important;
        float: right !important;
    }

    .rtl .navbar-custom-menu {
        float: right !important;

    }

    .rtl .main-header .logo {
        float: right !important;
    }

    .main-header .logo {
        height: auto;
    }

    .rtl .navbar-static-top {
        margin-left: 0 !important;
        float: left;
        width: 81.5%;
    }

    .main-header .logo {
        width: 17.3% !important;
    }

    .rtl .main-header .logo {
        width: 17.4% !important;
    }

    .sidebar-mini.sidebar-collapse .main-header .logo {
        width: 5.3% !important;
        height: 100% !important;
    }

    .rtl .sidebar-mini.sidebar-collapse .main-header .logo {
        width: 5.4% !important;
    }

    .sidebar-mini.sidebar-collapse .main-sidebar {
        width: 6% !important;
    }

    .rtl .sidebar-mini.sidebar-collapse .content-wrapper, .sidebar-mini.sidebar-collapse .right-side, .sidebar-mini.sidebar-collapse .main-footer {
        margin-left: 0% !important;
    }

    .sidebar-mini.sidebar-collapse .content-wrapper, .sidebar-mini.sidebar-collapse .right-side, .sidebar-mini.sidebar-collapse .main-footer {
        margin-left: 5% !important;
    }

    .sidebar-mini.sidebar-collapse .main-header .navbar {
        width: 93.5% !important;
    }

    .rtl .sidebar-mini.sidebar-collapse .main-header .navbar {
        width: 93.5% !important;
    }

    .ltr .navbar-static-top {
        float: right;
        width: 81.5%;
    }

    .ltr .main-header > .navbar {
        margin-left: 0 !important;
    }

    .rtl .navbar-custom-menu > .navbar-nav > li > .dropdown-menu {
        position: absolute;
        right: 0;
        left: 0;
    }

    /*.rtl .skin-black-light .main-header > .navbar .nav > li {*/
    /*    float: right !important;*/
    /*}*/

    .rtl .skin-black-light .main-header > .navbar .nav > li > a {
        float: right !important;
    }

    /* RTL Header Layout: Select menus (dropdowns) on right, other icons on left */
    /*.rtl .skin-black-light .main-header > .navbar .nav > li.dropdown,*/
    /*.rtl .skin-black-light .main-header > .navbar .nav > li.user-menu {*/
    /*    float: right !important;*/
    /*}*/

    .rtl .skin-black-light .main-header > .navbar .nav > li:not(.dropdown):not(.user-menu) {
        float: left !important;
    }

    .rtl .skin-black-light .main-header > .navbar .nav > li:not(.dropdown):not(.user-menu) > a {
        float: left !important;
    }

    .rtl th {
        text-align: start;
    }

    .rtl .box-header .form-horizontal .row {
        display: block !important;
        direction: rtl !important;
    }

    .ltr .form-horizontal .row {
        display: block !important;
    }

    .ltr .main-header .logo {
        float: left !important;
    }

    .rtl .form-horizontal .box-footer .btn-group {
        float: right;
    }

    .rtl .content-wrapper-rtl {
        margin-right: 5.5% !important;
    }

    .ltr .content-wrapper {
        margin-left: 17% !important;
    }

    .rtl .fields-group .form-group {

        display: flex !important;
    }

    .rtl /**.box-header**/ .box-tools {
        float: left;
        top: -8px;
        position: relative;
        left: 107px;

    }

    .rtl .wallet_posation {
        /* position: absolute; */

    }

    .rtl .wallet_div {
        width: 82%;
        margin-right: -14px !important;


    }

    .rtl .column-show_img .rtlSvga {
        direction: ltr;

    }

    .rtl .column-img2 .rtlSvga {
        direction: ltr;

    }

    .rtl .column-img .rtlSvga {
        direction: ltr;

    }

    .rtl .column-image .rtlSvga {
        direction: ltr;

    }

    .rtl .colorpicker-element .color {
        float: right !important;
    }

    .rtl .form-horizontal .control-label {
        padding-top: 7px;
        margin-bottom: 0;
        text-align: center;
    }

    .rtl .asterisk:before {
        content: none !important;
    }

    .rtl .asterisk:after {
        content: "* ";
        color: red;
    }

    .rtl .box-header .box-tools {
        float: left !important;

    }

    .rtl .box-header .pull-right {
        float: left !important;

    }

    /*.rtl .column-__actions__ .grid-dropdown-actions .dropdown-menu{*/
    /*  left: 29px !important;*/
    /*}*/
    .rtl .pull-role {
        width: 70px;
        position: relative;
        top: 7px;
        font-size: 10px;
        left: -30px;
    }

    .rtl .sidebar-menu .treeview-menu > li > a > .fa-angle-left,
    .rtl .sidebar-menu .treeview-menu > li > a > .fa-angle-down {
        transform: rotate(180deg);
        text-align: left;
        top: 13px;
        right: 190px;
    }

    .rtl .sidebar-menu .treeview.active > a > .fa-angle-left,
    /*.rtl .sidebar-menu .treeview.menu-open > a > .fa-angle-left,*/
    .rtl .sidebar-menu .treeview.active > a > .fa-angle-down,
    .rtl .sidebar-menu .treeview.menu-open > a > .fa-angle-down,
    .rtl .sidebar-menu .treeview-menu>li.active>a>.fa-angle-left,
    .rtl .sidebar-menu .treeview-menu>li.active>a>.fa-angle-down {
        transform: rotate(-90deg);
    }

    .tab-buttons {
        display: flex;
        width: 100%;
        margin-bottom: 20px;
        gap: 10px;
    }

    .tab-button {
        background-color: var(--secondary-color);
        flex: 1;
        padding: 5px;
        text-align: center;
        font-size: 18px;
        color: white;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    button {
        background: var(--primary-color);
        padding: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    button:hover {
        background: var(--primary-color);
        filter: brightness(0.85);
        color: var(--secondary-color);
    }

    .tab-button:hover {
        opacity: 0.8;
    }

    button.active {
        background-color: var(--secondary-color); !important;
        color: var(--text-secondary-color) !important;
    }

    .btn-warning {
        background-color: var(--primary-color);
        filter: brightness(4);
        color: var(--text-secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-warning:hover {
        background-color: var(--primary-color);
        color: var(--secondary-color);
        filter: brightness(0.85);
    }

/*    tr[data-key="18"] {
        background-color: var(--secondary-color) !important;
        filter: brightness(2);
    }*/

    .tab-button.active {
        background-color: var(--primary-color);
        border: 2px solid #fff;
        opacity: 1;
    }


    .colorpicker.dropdown-menu.colorpicker-visible {
    top: 282.8px;
    right: 500.475px;
    position: absolute; /* Required to make top/right take effect */
}
    /*.small-input {*/
    /*    width: 80px; !* Adjust width as needed *!*/
    /*    padding: 5px;*/
    /*    text-align: center;*/
    /*    border: 1px solid #ccc;*/
    /*    border-radius: 4px;*/
    /*    background-color: #f9f9f9;*/
    /*    margin-bottom: 10px;*/
    /*}*/

    /*.amount-text {*/
    /*    display: block;*/
    /*    margin-top: 5px; !* Adjust spacing as needed *!*/
    /*    font-size: 14px; !* Adjust font size as needed *!*/
    /*    color: #666;*/
    /*    text-align: center;*/
    /*}*/

    .rtl .sidebar-menu > li > a .fa-angle-left {
        transform: rotate(180deg);
    }

    .rtl .sidebar-menu > li.active > a .fa-angle-left {
        transform: rotate(-90deg);
        top: 23px;
        right: 207px;
    }

    .box-footer {
        flex-direction: row-reverse;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
    }

    .pagination-info {
        margin: 5px 0;
        white-space: nowrap;
        text-align: right;
        width: auto;
        order: 2;
    }

   /* [lang="en"] .form-horizontal .form-group {
    margin-left: -15px;
    margin-right: -1500px;

}
/* .col-sm-8 {
    width: 1000px;
} */

/* html[dir="ltr"] .col-sm-8 {
    width: 1000px !important;
} */


/* [lang="en"] .col-sm-8 {
    width: 1000px !important;
} */


    .box-footer .pull-right {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        /*margin: 5px 0;*/
        order: 1;
    }

    .box-footer .pull-right .dropdown {
        margin-left: 5px;
    }

    .pagination > li > a,
    .pagination > li > span {
        min-width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
    }

    .pagination {
        margin: 0;
        padding: 0;
        display: flex;
    }

    .rtl label.control-label.pull-right small:last-of-type{
        margin-left: 50px;
    }

    .small-box h3{
        font-size: x-large !important;
    }

    .small-box:hover .icon{
        font-size: 80px;
        transform: translateY(-37px);
        transition: all 0.3s ease;
    }

    .small-box .icon{
        font-size: 50px;
        top: 25px;
        right: 2px;
    }

    .preview-superadmin-btn,
    .exit-preview-btn{
        border: none;
        border-radius: 4px;
        padding: 4px 14px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .select-country .select2-container{
        margin-top: 12px;
    }

    #go-superadmin i {
        font-size: 15px;
    }

    .payment-card {
        height: 580px;
    }

    #area-Manager-select,
    #country-select {
        width: 190px !important;
    }

    .select2-container {
        width: 190px !important;
    }

    /*.settings-section {*/
    /*    overflow-x: auto;*/
    /*    overflow-y: auto;*/
    /*}*/

    /*.settings-section table {*/
    /*    width: 100%;*/
    /*}*/

    html {
        overflow-x: auto !important;
    }

    body {
        overflow-x: auto !important;
    }

    @media (max-width: 767px) {
        .ltr .content-wrapper {
            margin-left: 0 !important;
        }

        .ltr .main-header .logo {

            display: none !important;
        }

        .rtl .main-header .logo {
            display: none !important;
        }

        .rtl .navbar-static-top {
            margin-left: 1% !important;
            float: left;
            width: 99%;
        }

        .ltr .navbar-static-top {
            margin-left: 1% !important;
            float: left;
            width: 99%;
        }

        #app {
            margin-top: 19% !important;
        }

        .sidebar-open .content-wrapper {
            margin-left: 0 !important;
            padding-left: 24px !important;
        }

        .sidebar-open .skin-black-light .main-header > .navbar {
            margin-left: 0 !important;
        }

        .skin-black-light .main-sidebar {
            width: 100% !important;
            left: -100% !important;
            border-radius: 0 !important;
        }

        .sidebar-open .main-sidebar {
            left: 0 !important;

        }

        .rtl .navbar-custom-menu {
            position: absolute;
            left: 0px;
        }

        .ltr .navbar-custom-menu {
            position: absolute;
            right: 0px;
        }
    }

    .navbar.navbar-static-top {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        transition: transform 0.3s ease-in-out;
    }

    .navbar.navbar-static-top.navbar-hidden {
        transform: translateY(-100%);
    }

    /* Fix header blocking */
    header.main-header {
        pointer-events: none !important;
    }

    /* But enable clicks on actual navbar content */
    header.main-header .navbar,
    header.main-header .nav,
    header.main-header .nav-pills,
    header.main-header a,
    header.main-header button,
    header.main-header .logo {
        pointer-events: auto !important;
    }

    /* If navbar is hidden */
    .navbar-hidden {
        pointer-events: none !important;
        visibility: hidden !important;
    }

    /* Make sure tabs are clickable */
    .nav-pills,
    .nav-pills li,
    .nav-pills a,
    .charge_action {
        pointer-events: auto !important;
        position: relative;
        z-index: 100;
    }

    /* form inputs */
    .form-horizontal .fields-group > .col-md-12 {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
        width: 100% !important;
        padding: 15px !important;
    }

    .rtl .form-horizontal .fields-group > .col-md-12 {
        direction: rtl !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group {
        margin: 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .form-horizontal .fields-group > .col-md-12 > input[type="hidden"] {
        display: none !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group .control-label {
        width: 100% !important;
        text-align: left !important;
        margin-bottom: 8px !important;
        padding: 0 5px !important;
        font-weight: 600;
        order: 1;
    }

    .rtl .form-horizontal .fields-group > .col-md-12 > .form-group .control-label {
        text-align: right !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group .col-sm-2,
    .form-horizontal .fields-group > .col-md-12 > .form-group .col-sm-8,
    .form-horizontal .fields-group > .col-md-12 > .form-group [class*="col-sm-"] {
        width: 100% !important;
        float: none !important;
        padding: 0 5px !important;
        order: 2;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group .input-group-addon {
        display: none !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group .input-group {
        display: table !important;
        width: 100% !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group .input-group .form-control,
    .form-horizontal .fields-group > .col-md-12 > .form-group .form-control {
        border-radius: 8px !important;
        width: 100% !important;
    }

    .form-horizontal .box-footer {
        width: 100%;
        clear: both;
    }

    .file-input .input-group.file-caption-main {
        position: relative !important;
        display: block !important;
        direction: ltr !important;
    }

    .rtl .file-input .input-group.file-caption-main .file-caption {
        direction: rtl !important;
    }

    .file-input .input-group.file-caption-main .input-group-btn {
        position: relative !important;
    }

    .rtl .file-input .input-group.file-caption-main .input-group-btn {
        position: absolute !important;
    }

    .file-input .input-group.file-caption-main .btn-file {
        padding: 5px 15px !important;
        border-radius: 6px !important;
        font-size: 12px !important;
        margin-left: -105% !important;
    }

    .rtl .file-input .input-group.file-caption-main .btn-file {
        margin-top: 65% !important;
        margin-left: 100% !important;
    }

    .form-control:focus {
        border-color: var(--secondary-color) !important;
    }

    .form-horizontal + .box-footer,
    .form-horizontal .box-footer {
        display: grid !important;
        padding: 16px 24px !important;
        direction: rtl !important;
    }

    .rtl .form-horizontal + .box-footer,
    .rtl .form-horizontal .box-footer {
        direction: ltr !important;
    }

    .form-horizontal + .box-footer .col-md-8,
    .form-horizontal .box-footer .col-md-8 {
        float: none !important;
        width: auto !important;
        display: flex !important;
        align-items: center !important;
        gap: 16px !important;
        margin-right: auto !important;
    }

    .form-horizontal + .box-footer .btn-group.pull-right,
    .form-horizontal .box-footer .btn-group.pull-right {
        order: -2 !important;
        float: none !important;
    }

    .form-horizontal + .box-footer .btn-group.pull-left,
    .form-horizontal .box-footer .btn-group.pull-left {
        order: -1 !important;
        float: none !important;
    }

    .form-horizontal + .box-footer .pull-right:not(.btn-group),
    .form-horizontal .box-footer .pull-right:not(.btn-group) {
        float: none !important;
    }

    .form-horizontal + .box-footer .checkbox,
    .form-horizontal .box-footer .checkbox {
        margin: 0 !important;
    }

    .form-horizontal .fields-group > .col-md-12 > .form-group:has(.full-column-width) {
        grid-column: 1 / -1 !important;
    }

    @media (max-width: 768px) {
        .form-horizontal .fields-group > .col-md-12 {
            grid-template-columns: 1fr !important;
        }
    }

    .iti--separate-dial-code .iti__selected-flag {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    .grid-table td .dropdown,
    .grid-table td .dropup,
    .table td .dropdown,
    .table td .dropup {
        position: relative;
    }


    /* ==========================================
   MODERN FILTER DESIGN - LARAVEL ADMIN
   ========================================== */

    .col-md-12 .box-body .fields-group {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .fields-group > .form-group {
        display: flex;
        align-items: center;
        /*gap: 35px;*/
        margin-bottom: 0;
    }

    .fields-group .col-sm-2.control-label {
        font-weight: 500;
        font-size: 14px;
        text-align: right;
        margin-bottom: 0;
    }

    .fields-group .col-sm-8 {
        flex: 1;
    }

    .fields-group .input-group.input-group-sm {
        background: white;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .fields-group .input-group.input-group-sm:has(.bootstrap-datetimepicker-widget) {
        position: absolute !important;
    }

    .fields-group .input-group.input-group-sm:has(.bootstrap-datetimepicker-widget) {
        position: absolute !important;
    }

    .fields-group .input-group.input-group-sm:hover {
        border-color: rgba(255, 255, 255, 0.15);
    }

    .fields-group .input-group.input-group-sm:focus-within {
        border-color: rgba(99, 102, 241, 0.5);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .fields-group .input-group-addon {
        display: none !important;
    }

    .fields-group .input-group.input-group-sm .form-control {
        border-radius: 12px !important;
        padding: 12px 16px;
        font-size: 14px;
        height: auto;
        box-shadow: none !important;
    }

    .fields-group .form-control::placeholder {
        color: #64748b;
        opacity: 1;
    }

    .fields-group .form-control:focus {
        outline: none !important;
        box-shadow: none !important;
    }

    .modal-dialog {
        margin: 5% auto !important;
    }

    /* ==========================================
   AGENCY HEADER - RESPONSIVE DESIGN
   ========================================== */

    /* Main Header Container */
    .agency-header {
        display: flex;
        flex-direction: row;
        gap: 24px;
        padding: 24px;
        background: linear-gradient(145deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.5));
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Avatar Section */
    .agency-avatar {
        flex-shrink: 0;
    }

    .agency-avatar .logo-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255, 255, 255, 0.1);
    }

    /* Info Section */
    .agency-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .agency-name {
        font-size: 24px;
        font-weight: 700;
        color: #f1f5f9;
        margin: 0;
    }

    /* Meta Items */
    .agency-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .meta-label {
        color: #94a3b8;
        font-size: 13px;
        font-weight: 500;
    }

    .meta-value {
        color: #e2e8f0;
        font-size: 13px;
        font-weight: 600;
    }

    .flag-image {
        height: 16px;
        border-radius: 2px;
        margin-right: 4px;
    }

    /* Stats Section */
    .agency-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }

    /* ==========================================
       BUTTONS CARD
       ========================================== */

    .card.p-3.bg-danger-subtle {
        border: 1px solid rgba(0, 0, 0, 0.08) !important;
        border-radius: 12px !important;
        padding: 20px !important;
        align-self: flex-start;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 12px;
    }

    .card.p-3.bg-danger-subtle .d-flex {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
    }

    /* All Buttons */
    /*.card.p-3.bg-danger-subtle .btn {*/
    /*    border-radius: 10px !important;*/
    /*    padding: 10px 18px !important;*/
    /*    font-size: 13px !important;*/
    /*    font-weight: 500 !important;*/
    /*    transition: all 0.3s ease !important;*/
    /*    white-space: nowrap !important;*/
    /*}*/

    /*!* Back Button *!*/
    /*.card.p-3.bg-danger-subtle .btn-light {*/
    /*    background: rgba(255, 255, 255, 0.08) !important;*/
    /*    color: #e2e8f0 !important;*/
    /*    border: 1px solid rgba(255, 255, 255, 0.1) !important;*/
    /*}*/

    /*.card.p-3.bg-danger-subtle .btn-light:hover {*/
    /*    background: rgba(255, 255, 255, 0.15) !important;*/
    /*}*/

    /*!* Edit Button *!*/
    /*.card.p-3.bg-danger-subtle .btn-success {*/
    /*    background: var(--gradient-primary) !important;*/
    /*    color: #fff !important;*/
    /*    border: none !important;*/
    /*    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;*/
    /*}*/

    /*.card.p-3.bg-danger-subtle .btn-success:hover {*/
    /*    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;*/
    /*    transform: translateY(-2px) !important;*/
    /*}*/

    /*!* Remove BD Button *!*/
    /*.card.p-3.bg-danger-subtle .btn-danger {*/
    /*    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;*/
    /*    color: #fff !important;*/
    /*    border: none !important;*/
    /*    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;*/
    /*}*/

    /*.card.p-3.bg-danger-subtle .btn-danger:hover {*/
    /*    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;*/
    /*    transform: translateY(-2px) !important;*/
    /*}*/

    /* ==========================================
       TABLET RESPONSIVE
       ========================================== */

    @media (max-width: 992px) {
        .agency-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .agency-info {
            align-items: center;
        }

        .agency-meta {
            justify-content: center;
        }

        .agency-stats {
            justify-content: center;
        }

        .card.p-3.bg-danger-subtle {
            width: 100%;
            align-self: stretch;
        }
    }

    /* ==========================================
       MOBILE RESPONSIVE
       ========================================== */

    @media (max-width: 576px) {
        .agency-header {
            padding: 16px;
            gap: 16px;
        }

        .agency-avatar .logo-img {
            width: 80px;
            height: 80px;
        }

        .agency-name {
            font-size: 18px;
        }

        .meta-label,
        .meta-value {
            font-size: 11px;
        }

        .agency-meta {
            flex-direction: column;
            gap: 8px;
        }

        .meta-item {
            justify-content: center;
        }

        .agency-stats {
            flex-direction: column;
            gap: 8px;
        }

        /* BUTTONS ON MOBILE */
        .card.p-3.bg-danger-subtle {
            padding: 12px !important;
        }

        .card.p-3.bg-danger-subtle .d-flex {
            flex-direction: column !important;
            gap: 8px !important;
        }

        .card.p-3.bg-danger-subtle .btn {
            width: 100% !important;
            padding: 12px 16px !important;
            font-size: 13px !important;
            text-align: center !important;
        }
    }

    /* ==========================================
       EXTRA SMALL SCREENS
       ========================================== */

    @media (max-width: 380px) {
        .agency-header {
            padding: 12px;
        }

        .agency-avatar .logo-img {
            width: 60px;
            height: 60px;
        }

        .agency-name {
            font-size: 16px;
        }

        .meta-label,
        .meta-value {
            font-size: 10px;
        }

        .card.p-3.bg-danger-subtle .btn {
            padding: 10px 12px !important;
            font-size: 12px !important;
        }
    }

    .content-header > .breadcrumb > li > a,
    .content-header > h1,
    .label,
    a {
        color: #000000;
    }

    .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a {
        color: var(--text-secondary-color) !important;
    }

    ::placeholder {
        color: #3f3f3f !important;
    }

    .close:focus, .close:hover {
        color: var(--text-secondary-color);
    }

    .agency-header {
        display: flex;
        align-items: flex-start;
        gap: 25px;
        margin-bottom: 30px;
        position: relative;
        padding: 20px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        border-top: 3px solid var(--primary-color);
        transition: all 0.3s ease;
    }

    .card-header {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--off-white);
        border-bottom: 2px solid #e5e7eb;
        border-radius: 12px 12px 0 0;
    }

    .pagination-wrapper {
        padding: 15px 20px;
        display: flex;
        justify-content: center;
        border-top: 1px solid #eee;
    }

    .table tbody tr:nth-child(even) {
        background-color: var(--off-white) !important;
    }

    .stat-card {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8f9fa;
        border-bottom: 2px solid #e5e7eb;
        border-radius: 12px 12px 0 0;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        line-height: 1;
    }

    .performers-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .performers-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid black;
    }

    .section-badge {
        background: var(--primary-color);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        padding: 12px 15px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .count-badge {
        background: var(--primary-color);
        color: #7f8c8d;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .rtl .btn-back {
        position: absolute;
        top: 5px;
        left: 20px;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        color: #7f8c8d;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-back:hover {
        background: #d6e0e3;
        color: #34495e;
    }

    .section-box {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .section-box:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    }

    .table-section {
        width: 100%;
        border-collapse: collapse;
    }

    .nav-pills > li.active > a, .nav-pills > li.active > a:focus, .nav-pills > li.active > a:hover {
        background: var(--primary-color) !important;
    }

    .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
        border-top-color: var(--secondary-color) !important;
    }

    .data-table tr:hover {
        background: var(--off-white);
    }

    /* Logo Fixes */
    .main-header .logo {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 5px 15px !important;
        height: var(--header-height, 70px) !important;
        overflow: hidden !important;
    }

    .sidebar-collapse .main-header .logo .logo-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: auto !important;
        height: 100% !important;

        border-radius: 0 !important;
        overflow: unset !important;
        border: unset !important;
        margin-top: 0 !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .main-header .logo .logo-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .sidebar-collapse .main-header .logo .logo-icon img {
        max-height: 70px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        border-radius: 8px !important;
        border: unset !important;
    }

    .main-header .logo .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.1);
    }

    .main-header .logo .logo-icon img.circular-logo {
        width: 45px !important;
        height: 45px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
    }

    .rtl .main-header .logo .logo-icon {
        margin-right: 0;
        margin-left: 8px;
    }

    .main-sidebar, .left-side {
        padding-top: 5% !important;
    }

    .dark-mode .phpdebugbar-settings {
        background: white !important;
    }

    .phpdebugbar[data-theme="dark"] .phpdebugbar-settings {
        background: none !important;
    }

    .bootstrap-datetimepicker-widget {
        display: contents !important;
    }

    .datepicker table tr td.active, .datepicker table tr td.active:hover, .datepicker table tr td.active.disabled, .datepicker table tr td.active.disabled:hover {
        background: var(--primary-color) !important;
    }

    .datepicker table tr td span.active, .datepicker table tr td span.active:hover, .datepicker table tr td span.active.disabled, .datepicker table tr td span.active.disabled:hover {
        background: var(--primary-color) !important;
    }

    .dropdown-menu.show,
    .dropdown-menu[style*="display: block"] {
        position: fixed !important;
        z-index: 99999 !important;
    }

    .input-group:has(input.sort) .input-group-btn:first-child > .btn {
        height: 34px;
        padding: 0 10px !important;
    }

    .input-group:has(input.sort) .input-group-btn:last-child > .btn {
        height: 34px;
        padding: 0 10px !important;
    }

    .navbar-nav > .notifications-menu > .dropdown-menu > li .menu > li > a:hover, .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a:hover, .navbar-nav > .tasks-menu > .dropdown-menu > li .menu > li > a:hover {
        background: var(--primary-color);
    }

    .transferModal {
        display: none;
        position: fixed;
        top: 25%;
        left: 50%;
        transform: translate(-50%, -20%);
        background: white;
        border-radius: 36px;
        z-index: 9999;
        width: 520px;
        overflow: hidden;
        height: 60%;
    }

    .dropdown-menu > li > a:focus, .dropdown-menu > li > a:hover {
        background-color: var(--primary-color);
    }

    .nav > li > a:hover, .nav > li > a:active, .nav > li > a:focus {
        background: var(--secondary-color) !important;
        color: var(--text-secondary-color);
    }

    .form-divider {
        position: relative;
        margin: 30px 0 20px;
        border-bottom: 1px solid #e5e5e5;
    }

    .form-divider span {
        position: absolute;
        top: -10px;
        left: 50%;
        background: #fff;
        padding: 0 10px;
        font-weight: 600;
        font-size: 14px;
        color: #555;
    }

    .ltr .box-tools .btn-group.pull-right {
        margin-left: 5px;
        margin-right: 0 !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: var(--primary-color) !important;
    }
</style>
