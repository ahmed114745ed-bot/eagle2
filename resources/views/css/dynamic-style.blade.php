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
        --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
        --primary-hover-alpha: {{ config('themes.primaryColor')}}33;
        --scroll-second-color: {{ config('themes.secondaryColor') }}cc;
        --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;


        --inverse-color: {{getLighterColor(config('themes.primaryColor'))}};
        --inverse-box-color: {{adjustTextColor(config('themes.boxBackgroundColor'))}};
        --success-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
        --primary-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
    }

 
    .btn-success {
        background: var(--success-button) !important;
        /*background: #FF9428 !important;*/
        /* background: linear-gradient(90deg, #2d7dffb8 0%, #21c6fba8 100%)!important; */
    }

    .pagination > .active > a, .pagination > .active > a:focus, .pagination > .active > a:hover, .pagination > .active > span, .pagination > .active > span:focus, .pagination > .active > span:hover {
        z-index: 2;
        color: #fff;
        cursor: default;
        background: var(--primary-button) !important;
        border-color: #337ab7
    }
    .skin-black-light .content-header {
    background: var(--second-alpha) !important;
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

    .box {
        background: var(--box-background-color) !important;
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
        color: var(--text-primary-color) !important;
        background: var(--primary-hover-alpha) !important;
    }

    .skin-black-light .sidebar-menu > li:hover > a i,
    .skin-black-light .sidebar-menu > li.active > a i {
        color: var(--text-primary-color) !important;
        background: transparent !important;

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
        color: var(--inverse-box-color) !important;
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
        background-color: var(--box-background-color) !important;
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
        color: var(--inverse-color) !important;
    }

    .btn-dropbox,
    .btn-instagram,
    .btn-success {
        background-color: var(--primary-color) !important;
        color: var(--inverse-color) !important;
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
        color: var(--inverse-box-color) !important;
        background-color: var(--box-background-color) !important;
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
        background-color: var(--box-background-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
        border-radius: 4px;

    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--inverse-box-color) !important;
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
        background: #fff;
        padding: 15px;
        color: #444;
        border-top: 1px solid var(--primary-hover-alpha) !important;
    }

    .dropdown-toggle {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--inverse-box-color) !important;
        border: 1px solid transparent !important;
    }

    .btn-default {
        background-color: var(--primary-hover-alpha) !important;
        color: var(--inverse-box-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .btn-default:hover {
        background-color: var(--second-color) !important;
        color: var(--inverse-color) !important;
        border: 1px solid var(--primary-hover-alpha) !important;
    }

    .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-default, .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-default {



        background: var(--primary-hover-alpha) !important;
        color: var(--inverse-box-color) !important;

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

    /* Mobile styles (adjust max-width as needed) */
    @media (max-width: 768px) {
        .nprogress-custom-parent {
            position: absolute !important;
        }
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

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        float: left;
        min-width: 160px;
        padding: 5px 0;
        margin: 2px 0 0;
        font-size: 14px;
        text-align: left;
        list-style: none;
        background-color: var(--box-background-color) !important;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 4px;
        -webkit-box-shadow: 0 6px 12px rgba(0,0,0,.175);
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
        color: var(--inverse-box-color) !important;
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
        color: var( --text-primary-color)!important;
        background-color: var(--box-background-color) !important;
    }

    .dd-handle {
        display: block;
        margin: 1px 0;
        padding: 8px 10px;
        color: var( --text-primary-color)!important;
        text-decoration: none;
        border: 1px solid #ddd;
        background:  var(--box-background-color) !important;
    }

    .dropdown-menu>li>a {
        color: var(--inverse-box-color) !important;
    }


            .rtl {
                direction: rtl;
                text-align: right;
            }

            .rtl .sidebar-menu {
                  text-align: right;
            }

            .rtl .main-sidebar {
                right: 0;
                left: auto;
            }

          .rtl .content-wrapper,
            .rtl .main-footer {
                margin-left: 0;
                margin-right: 230px;
            } 

            .rtl .treeview-menu {
                 padding-right: 10px;
            }

            .rtl .fa-angle-left{
                direction: rtl;
                left: 0px;
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
                float: left !important;
            }

            .rtl .main-header .logo{
                float: right !important;

            }
            .rtl .navbar-static-top{
                margin-left: 16px !important;

            }

            .rtl .navbar-custom-menu>.navbar-nav>li>.dropdown-menu {
                position: absolute;
                right: -238px;
            }

            .rtl th{
                text-align: start;
            }
            .rtl .form-horizontal .row {
                display: grid;
                direction: rtl !important;
                flex-direction: row-reverse !important;
            
            }

           
           .rtl .form-horizontal .box-footer .btn-group {
                float: right !important; 
            }
            .rtl .content-wrapper-rtl{
                margin-right: 42px !important;
            }

            .rtl .fields-group .form-group{

                display: flex !important;
            }
                   
            .rtl .box-header .box-tools {
                float: left;
                top: -8px;
                position: relative;
                left: 107px;

            }
            .rtl .wallet_posation{
                position: absolute;

            }
            .rtl .column-show_img .rtlSvga{
                direction: ltr;

            }
            .rtl .column-img2 .rtlSvga{
                direction: ltr;

            }
            .rtl .column-img .rtlSvga{
                direction: ltr;

            }
            
            .rtl .colorpicker-element .color{
                float: right !important;
            }
           .rtl .form-horizontal .control-label {
                padding-top: 7px;
                margin-bottom: 0;
                text-align: center;
            }
            .rtl .asterisk:before {
                content: none;
            }
            .rtl .asterisk:after {
                content: "* ";
                color: red;
            }

 

    @media (max-width: 768px) {
        .rtl .main-sidebar {
            right: 0 !important;
            left: auto !important;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }

        .active_hide{
            transform: translateX(1%) !important;

        }

        .rtl .main-sidebar.active {
            transform: translateX(0);
        }

        .rtl .sidebar-toggle {
            float: right !important;
            margin-right: 10px;
        }

        .rtl .navbar-custom-menu {
            float: left !important;
        }

        .rtl .navbar-custom-menu>.navbar-nav>li>.dropdown-menu {
                    position: absolute;
                    right: 0 !important;
                }

        .rtl .content-wrapper,
        .rtl .main-footer {
                    margin-left: 0;
                    margin-right: auto;
                } 

            
        .rtl .content-wrapper-rtl {
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
            margin-right: 444px !important;    width: calc(100% - 0px); 
        }

        .rtl.sidebar-open .content-wrapper-rtl {
            margin-right: 444px !important;
            width: calc(100% - 444px);
        }


            .sidebar-open .content-wrapper {
                margin-right: 250px; 
        }
    }
  

.rtl .sidebar-menu > li > a .fa-angle-left {
    transform: rotate(180deg);
}



</style>
