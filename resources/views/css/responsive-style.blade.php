<style>
    @media (max-width: 1400px) {
        .content {
            /* width: 1340px !important; */
        }

        .payment-card {
            height: 795px;
        }

        .wrapper {
            /* min-width: max-content; */
        }
    }

    @media (max-width: 1200px) {
        .content {
            width: 1160px !important;
        }

        .wrapper {
            /* min-width: max-content; */
        }
    }

    @media (max-width: 992px) {
        .wrapper {
            /* min-width: max-content; */
        }

        .rtl .navbar-custom-menu>.navbar-nav>li>.dropdown-menu {
            left: 0 !important;
        }

        .navbar-custom-menu>.navbar-nav>li {
            position: relative !important;
        }
    }

    @media (max-width: 768px) {
        .content {
            width: 100% !important;
        }

        .rtl .box-body .fields-group [class*="col-md-12"] {
            float: none; !important;
        }

        .wrapper {
            /* min-width: max-content; */
        }

        .nprogress-custom-parent {
            position: absolute !important;
        }

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
            left: 0 !important;
        }

        .rtl .content-wrapper,
        .rtl .main-footer {
            margin-left: 0;
            margin-right: 0;
        }

        .rtl .content-wrapper-rtl {
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
            margin-right: 444px !important;    width: calc(100% - 0px);
        }

        .rtl.sidebar-open .content-wrapper-rtl {
            margin-right: 444px !important;
            width: calc(100% - 444px);
        }

        /*select header menu*/
        .rtl .select-country-rtl {
            transition: margin-right 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        .rtl .select-country-rtl {
            margin-right: 230px !important;
            top: -20px;
            position: relative;
        }
        .ltr .select-country-rtl {
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        .ltr .select-country-rtl {
            margin-left: 230px !important;
            top: -20px;
            position: relative;
        }

        .sidebar-open .content-header {
            padding: 30px 20px !important;
            margin: 35px 15px !important;
        }

        /*.sidebar-open .content-wrapper {*/
        /*    margin-right: 250px;*/
        /*}*/

        .col-md-3,
        .col-sm-6 {
            flex: 0 0 100%;
            width: 50%;
        }

        #area-Manager-select,
        #country-select {
            width: 150px !important;
        }

        .select2-container {
            width: 150px !important;
        }
    }

    @media (max-width: 576px) {
        .box-footer {
            flex-direction: column;
            align-items: center;
        }

        .pagination-info,
        .box-footer .pull-right {
            width: fit-content;
            display: flex;
            justify-content: center;
            text-align: center;
        }

        .pagination-info {
            order: 1;
            margin-bottom: 10px;
        }

        .box-footer .pull-right {
            order: 2;
        }

        .wrapper {
            /* min-width: max-content; */
        }

        .rtl .navbar-custom-menu>.navbar-nav>li>.dropdown-menu {
            left: 0 !important;
        }
    }
</style>
