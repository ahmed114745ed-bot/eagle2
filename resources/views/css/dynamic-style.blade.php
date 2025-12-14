<style>
    /* Modern Dynamic Theme Variables */
    :root {
        --primary-color: {{ config('themes.primaryColor') ?: '#2563eb' }};
        --secondary-color: {{ config('themes.secondaryColor') ?: '#1f2937' }};
        --green-color: {{ config('themes.greenColor') ?: '#10b981' }};
        --text-primary-color: {{ config('themes.textPrimaryColor') ?: '#ffffff' }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') ?: '#9ca3af' }};
        --box-background-color: {{ config('themes.boxBackgroundColor') ?: '#ffffff' }};
        --table-background-color: {{ config('themes.tableBackGroundColor') ?: '#f9fafb' }};
        --background-image: {{ config('themes.backgroundImage') ?: 'none' }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) ?: '' }});
        --second-alpha: rgba(31, 41, 55, 0.1);
        --primary-hover-alpha: rgba(37, 99, 235, 0.1);
        --scroll-second-color: rgba(255, 255, 255, 0.8);
        --scroll-first-color: rgba(37, 99, 235, 0.2);

        --inverse-color: #ffffff;
        --inverse-box-color: #1f2937;
        --success-button: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --primary-button: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);

        /* Additional unified color variables */
        --white: #ffffff;
        --gray-800: #1f2937;
        --gray-700: #374151;
        --gray-50: #f9fafb;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-900: #111827;

        /* Modern Design Variables */
        --sidebar-width: 280px;
        --header-height: 70px;
        --border-radius: 12px;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

        --gradient-primary: linear-gradient(90deg, var(--secondary-color) 0%, var(--gray-50) 100%);
    }

    /* .col-sm-8 {
    width: 80.66666667%;
} */
    /* .col-sm-2 {
    width: 5.66666667%;
} */

    .rtl label {
        margin: 0 !important;
    }


    .sidebar-menu {
        margin: 11% 0px;
    }

    /*.ltr label {*/
    /*    margin: 0 !important;*/
    /*}*/
    /*
        .ltr .fields-group .form-group{

           display: flex !important;
        } */

    .fileinput-remove {
        display: none;

    }

    .rtl .pull-right {
        float: left !important;
    }

    .box-info .btn-group.pull-right {
        float: right !important;
    }

    .rtl .box-info .pull-right {
        float: right !important;
    }

    .rtl .box-info label {
        margin: 5px 10px 0 0 !important;
    }

    /* .box-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 16px 24px !important;
        margin: -24px -24px 24px -24px !important;
        border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        display: block !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 21px 145px !important;
    } */

    /* Responsive fixes for box-header on mobile */
    @media (max-width: 767px) {
        .box-header {
            padding: 12px 16px !important;
            margin: -16px -16px 16px -16px !important;
            display: block !important;
            text-align: left !important;
        }

        .box-header.with-border {
            padding: 12px 16px !important;
            margin: -16px -16px 16px -16px !important;
        }

        .box-header.with-border.filter-box {
            padding: 12px 16px !important;
            margin: -16px -16px 16px -16px !important;
        }

        .box-header .pull-right,
        .box-header .pull-left {
            float: none !important;
            text-align: center !important;
            margin: 8px 0 !important;
        }

        .box-header .box-title {
            font-size: 16px !important;
            margin-bottom: 8px !important;
        }

        .box-header form {
            padding-top: 0 !important;
        }
    }

    .box-header form {
        padding-top: 8px;
        border-radius: 36px;
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
        background: var(--gradient-primary) !important;
        box-shadow: none;
    }

    input:checked + .slider {
        background: var(--primary-button) !important;
    }

    .content-header > .breadcrumb > li > a {
        color: var(--primary-color) !important;
        text-decoration: none;
        font-size: var(--bs-breadcrumb-font-size);
        display: inline-block;
    }

    /* Dynamic CSS */
    @keyframes dynamic-sidebar-bg {
        0% {
            background: linear-gradient(180deg, var(--white) 0%, var(--gray-50) 100%) !important;
        }
        50% {
            background: linear-gradient(180deg, var(--gray-50) 0%, var(--white) 50%, var(--gray-200) 100%) !important;
        }
        100% {
            background: linear-gradient(180deg, var(--white) 0%, var(--gray-50) 100%) !important;
        }
    }

    .skin-black-light .main-sidebar,
    .skin-black-light .left-side {
        background: linear-gradient(180deg, var(--gray-50) 0%, var(--secondary-color) 100%) !important;
        box-shadow: var(--shadow-lg) !important;
        border-right: 1px solid var(--gray-200) !important;
        animation: dynamic-sidebar-bg 10s ease-in-out infinite !important;
    }

    .skin-black-light .main-sidebar {
        width: 18%;
        position: fixed !important;
        top: 0 !important;
        left: -var(--sidebar-width) !important;
        height: 100vh !important;
        z-index: 1000 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        transition: left 0.3s ease !important;
        border-radius: 0 20px 20px 0 !important;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.1) !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar {
        width: 24px !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-track {
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(255, 255, 255, 0.1) 100%) !important;
        border-radius: 30px !important;
        margin: 6px !important;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.3), 0 0 10px rgba(37, 99, 235, 0.1) !important;
        position: relative !important;
        border: 2px solid rgba(255, 255, 255, 0.1) !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-track::before {
        content: '' !important;
        position: absolute !important;
        top: -2px !important;
        left: -2px !important;
        right: -2px !important;
        bottom: -2px !important;
        background: conic-gradient(from 0deg, transparent 0deg, rgba(37, 99, 235, 0.2) 90deg, transparent 180deg, rgba(16, 185, 129, 0.2) 270deg, transparent 360deg) !important;
        border-radius: 30px !important;
        animation: track-rotate 8s linear infinite !important;
    }

    @keyframes track-rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--green-color) 20%, var(--primary-color) 40%, var(--green-color) 60%, var(--primary-color) 80%, var(--green-color) 100%) !important;
        border-radius: 30px !important;
        border: 8px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 0 25px rgba(37, 99, 235, 0.8), 0 0 45px rgba(16, 185, 129, 0.6), inset 0 0 15px rgba(255, 255, 255, 0.3), 0 0 0 2px rgba(37, 99, 235, 0.3) !important;
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        cursor: pointer !important;
        background-size: 200% 200% !important;
        animation: thumb-flow 3s ease-in-out infinite !important;
    }

    @keyframes thumb-flow {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb::before {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 10px !important;
        height: 10px !important;
        background: radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0.8) 40%, rgba(255, 255, 255, 0.4) 70%, transparent 100%) !important;
        border-radius: 50% !important;
        box-shadow: 0 0 12px rgba(255, 255, 255, 1), 0 0 20px rgba(37, 99, 235, 0.8) !important;
        animation: core-pulse 1.5s ease-in-out infinite alternate !important;
    }

    @keyframes core-pulse {
        0% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0.8;
        }
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb::after {
        content: '' !important;
        position: absolute !important;
        top: -4px !important;
        left: -4px !important;
        right: -4px !important;
        bottom: -4px !important;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%) !important;
        border-radius: 30px !important;
        animation: thumb-shimmer 2s ease-in-out infinite !important;
    }

    @keyframes thumb-shimmer {
        0%, 100% {
            opacity: 0;
            transform: translateY(-100%);
        }
        50% {
            opacity: 1;
            transform: translateY(100%);
        }
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, var(--green-color) 0%, var(--primary-color) 20%, var(--green-color) 40%, var(--primary-color) 60%, var(--green-color) 80%, var(--primary-color) 100%) !important;
        box-shadow: 0 0 35px rgba(37, 99, 235, 1.2), 0 0 60px rgba(16, 185, 129, 1), inset 0 0 20px rgba(255, 255, 255, 0.4), 0 0 0 3px rgba(37, 99, 235, 0.5) !important;
        transform: scale(1.05) !important;
        border: 10px solid rgba(255, 255, 255, 0.6) !important;
        animation-duration: 1.5s !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb:hover::before {
        width: 14px !important;
        height: 14px !important;
        box-shadow: 0 0 18px rgba(255, 255, 255, 1.2), 0 0 30px rgba(37, 99, 235, 1) !important;
        animation-duration: 1s !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-thumb:hover::after {
        animation-duration: 1s !important;
    }

    .skin-black-light .main-sidebar::-webkit-scrollbar-corner {
        background: rgba(255, 255, 255, 0.1) !important;
        border-radius: 0 0 30px 0 !important;
    }

    .sidebar-open .main-sidebar {
        left: 0 !important;
    }

    .skin-black-light .sidebar-menu > li.header {
        color: rgba(255, 255, 255, 0.6) !important;
        background: rgba(255, 255, 255, 0.05) !important;
        margin: 16px 16px 8px 16px !important;
        padding: 8px 16px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-radius: 6px !important;
    }

    /* Global Custom Scrollbar for Entire System - Cyberpunk Neon Design */
    *::-webkit-scrollbar {
        width: 14px !important;
        height: 14px !important;
    }

    *::-webkit-scrollbar-track {
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.6) 50%, rgba(0, 0, 0, 0.8) 100%) !important;
        border-radius: 0 !important;
        margin: 1px !important;
        position: relative !important;
        border: 1px solid rgba(37, 99, 235, 0.3) !important;
        box-shadow: inset 0 0 20px rgba(37, 99, 235, 0.1), 0 0 10px rgba(16, 185, 129, 0.1) !important;
    }

    *::-webkit-scrollbar-track::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: linear-gradient(90deg, transparent 0%, rgba(37, 99, 235, 0.1) 20%, rgba(16, 185, 129, 0.1) 80%, transparent 100%) !important;
        animation: neon-track 4s ease-in-out infinite !important;
    }

    @keyframes neon-track {
        0%, 100% {
            opacity: 0.2;
        }
        50% {
            opacity: 0.5;
        }
    }

    *::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #000000 0%, rgba(37, 99, 235, 0.8) 30%, rgba(16, 185, 129, 0.8) 70%, #000000 100%) !important;
        border-radius: 0 !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.8), 0 0 20px rgba(16, 185, 129, 0.6), inset 0 0 10px rgba(0, 0, 0, 0.8) !important;
        transition: all 0.2s ease !important;
        position: relative !important;
        cursor: pointer !important;
        background-size: 400% 400% !important;
        animation: neon-flow 6s linear infinite !important;
        min-height: 40px !important;
        min-width: 40px !important;
    }

    @keyframes neon-flow {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    *::-webkit-scrollbar-thumb::before {
        content: '' !important;
        position: absolute !important;
        top: 2px !important;
        left: 2px !important;
        right: 2px !important;
        bottom: 2px !important;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.9) 0%, rgba(37, 99, 235, 0.7) 50%, rgba(16, 185, 129, 0.7) 100%) !important;
        border-radius: 0 !important;
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.8), 0 0 15px rgba(37, 99, 235, 0.6) !important;
        animation: neon-core 3s ease-in-out infinite alternate !important;
    }

    @keyframes neon-core {
        0% {
            opacity: 0.7;
            transform: scale(0.9);
        }
        100% {
            opacity: 1;
            transform: scale(1.1);
        }
    }

    *::-webkit-scrollbar-thumb::after {
        content: '' !important;
        position: absolute !important;
        top: -1px !important;
        left: -1px !important;
        right: -1px !important;
        bottom: -1px !important;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%) !important;
        border-radius: 0 !important;
        animation: neon-glow 2s ease-in-out infinite !important;
    }

    @keyframes neon-glow {
        0%, 100% {
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
    }

    *::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, rgba(37, 99, 235, 1) 0%, rgba(16, 185, 129, 1) 50%, rgba(37, 99, 235, 1) 100%) !important;
        box-shadow: 0 0 20px rgba(37, 99, 235, 1.2), 0 0 35px rgba(16, 185, 129, 1), inset 0 0 15px rgba(255, 255, 255, 0.3) !important;
        transform: scale(1.05) !important;
        border: 2px solid rgba(255, 255, 255, 1) !important;
        animation-duration: 3s !important;
    }

    *::-webkit-scrollbar-thumb:hover::before {
        box-shadow: 0 0 15px rgba(255, 255, 255, 1), 0 0 25px rgba(37, 99, 235, 0.8) !important;
        animation-duration: 2s !important;
    }

    *::-webkit-scrollbar-thumb:active {
        background: linear-gradient(135deg, rgba(16, 185, 129, 1) 0%, rgba(37, 99, 235, 1) 100%) !important;
        box-shadow: 0 0 25px rgba(37, 99, 235, 1.5), 0 0 45px rgba(16, 185, 129, 1.2) !important;
        transform: scale(0.95) !important;
        animation-duration: 1.5s !important;
    }

    *::-webkit-scrollbar-corner {
        background: rgba(0, 0, 0, 0.8) !important;
        border: 1px solid rgba(37, 99, 235, 0.3) !important;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.2) !important;
    }

    body {
        color: var(--text-secondary-color) !important;
        background-color: #f8fafc !important;
        background-image: var(--brand_background-image) !important;
        background-repeat: no-repeat !important;
        background-size: cover !important;
        background-position: center !important;
        background-attachment: fixed !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        line-height: 1.6;
    }

    .skin-black-light .content-wrapper {
        margin-left: 0 !important;
        background: #f8fafc !important;
        min-height: calc(100vh - var(--header-height)) !important;
        padding: 24px !important;
        transition: var(--transition) !important;
    }

    /* Shift content when sidebar is open */
    .sidebar-open .content-wrapper {
        margin-left: var(--sidebar-width) !important;
        padding-left: 0 !important;
    }

    /* Shift content when sidebar is open (RTL) */
    .rtl.sidebar-open .content-wrapper {
        margin-right: var(--sidebar-width) !important;
        padding-right: 0 !important;
    }

    .skin-black-light .wrapper {
        background: #f8fafc !important;
    }

    .rtl .small-box .icon {
        width: 100%;
        text-align: left;
        right: -2px !important;
    }

    .skin-black-light .main-header > .navbar {
        background: var(--gradient-primary) !important;
        border-bottom: 1px solid rgba(229, 231, 235, 0.3) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
        height: var(--header-height) !important;
        display: flex !important;
        align-items: center !important;
        position: relative !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-radius: 0 0 var(--border-radius) var(--border-radius) !important;
        z-index: 1030 !important;
    }

    .sidebar-open .skin-black-light .main-header > .navbar {
        margin-left: var(--sidebar-width) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
    }

    .content-header {
        padding: 20px 24px !important;
        margin: 0 0 24px 0 !important;
        background: #ffffff !important;
        box-shadow: var(--shadow-sm) !important;
        border-radius: var(--border-radius) !important;
        border: 1px solid #e5e7eb !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    .content-header > h1 {
        margin: 0 !important;
        color: #111827 !important;
        font-size: 1.5rem !important;
        font-weight: 600 !important;
    }

    .content-header > .breadcrumb {
        background: transparent !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 0.875rem !important;
    }

    .content-header > .breadcrumb > li > a {
        color: #6b7280 !important;
        text-decoration: none !important;
        transition: var(--transition) !important;
    }

    .content-header > .breadcrumb > li > a:hover {
        color: var(--primary-color) !important;
    }

    .content-header > .breadcrumb > .active {
        color: #374151 !important;
        font-weight: 500 !important;
    }

    .skin-black-light .sidebar a {
        color: #374151 !important;
        text-decoration: none !important;
        display: flex !important;
        align-items: center !important;
        transition: var(--transition) !important;
    }

    .skin-black-light .sidebar a:hover {
        color: var(--primary-color) !important;
    }

    .skin-black-light .sidebar a i {
        color: var(--primary-color) !important;
        margin-right: 12px !important;
        width: 20px !important;
        text-align: center !important;
        font-size: 1.1rem !important;
        transition: var(--transition) !important;
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

    .iti {
        position: relative;
        z-index: 1050 !important;
        width: 100%;
    }

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
        background: #ffffff !important;
        color: #374151 !important;
        border: 1px solid #e5e7eb !important;
        border-radius: var(--border-radius) !important;
        box-shadow: var(--shadow-md) !important;
        overflow: hidden !important;
        transition: var(--transition) !important;
    }

    .box:hover {
        box-shadow: var(--shadow-lg) !important;
        transform: translateY(-2px) !important;
    }

    .table .table {
        background: var(--table-background-color) !important;
        color: var(--table-background-color) !important;
    }

    .skin-black-light .sidebar-menu > li > a {
        border-radius: var(--border-radius);
        margin: 4px 16px;
        padding: 12px 16px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none !important;
        position: relative;
        overflow: hidden;
    }

    .skin-black-light .sidebar-menu > li > a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: var(--primary-color);
        transform: scaleY(0);
        transition: var(--transition);
        border-radius: 0 4px 4px 0;
    }

    .skin-black-light .sidebar-menu > li:hover > a,
    .skin-black-light .sidebar-menu > li.active > a {
        color: #ffffff !important;
        background: rgba(37, 99, 235, 0.15) !important;
        box-shadow: var(--shadow-md);
        transform: translateX(4px);
    }

    .skin-black-light .sidebar-menu > li:hover > a::before,
    .skin-black-light .sidebar-menu > li.active > a::before {
        transform: scaleY(1);
    }

    .skin-black-light .sidebar-menu > li:hover > a i,
    .skin-black-light .sidebar-menu > li.active > a i {
        color: var(--primary-color) !important;
        transform: scale(1.1);
        transition: var(--transition);
    }

    /* Sidebar Icon Animations */

    /* Best Choice: Glow Pulse Animation - Active */
    .skin-black-light .sidebar-menu > li:hover > a i {
        animation: icon-glow 1s ease-in-out infinite;
        filter: drop-shadow(0 0 4px rgba(37, 99, 235, 0.6));
    }

    @keyframes icon-glow {
        0%, 100% {
            transform: scale(1);
            filter: drop-shadow(0 0 4px rgba(37, 99, 235, 0.6));
        }
        50% {
            transform: scale(1.1);
            filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.8));
        }
    }

    /* Alternative Options - Uncomment to use */

    /* Option 1: Gentle Pulse Animation */
    /*
    .skin-black-light .sidebar-menu > li:hover > a i {
        animation: icon-pulse 0.6s ease-in-out;
    }

    @keyframes icon-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.15); }
    }
    */

    /* Option 2: Rotate Animation */
    /*
    .skin-black-light .sidebar-menu > li:hover > a i {
        animation: icon-rotate 0.4s ease-in-out;
    }

    @keyframes icon-rotate {
        0% { transform: rotate(0deg) scale(1); }
        50% { transform: rotate(180deg) scale(1.1); }
        100% { transform: rotate(360deg) scale(1); }
    }
    */

    /* Option 3: Bounce Animation */
    /*
    .skin-black-light .sidebar-menu > li:hover > a i {
        animation: icon-bounce 0.8s ease-in-out;
    }

    @keyframes icon-bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) scale(1); }
        40% { transform: translateY(-8px) scale(1.1); }
        60% { transform: translateY(-4px) scale(1.05); }
    }
    */

    /* Option 4: Shake Animation */
    /*
    .skin-black-light .sidebar-menu > li:hover > a i {
        animation: icon-shake 0.5s ease-in-out;
    }

    @keyframes icon-shake {
        0%, 100% { transform: translateX(0) scale(1); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px) scale(1.05); }
        20%, 40%, 60%, 80% { transform: translateX(2px) scale(1.05); }
    }
    */

    .skin-black-light .treeview-menu > li.active > a,
    .skin-black-light .treeview-menu > li > a:hover {
        color: var(--text-secondary-color) !important;
    }

    /* Animation Option 1: Fast & Smooth (Current) */
    .sidebar-menu .treeview-menu {
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-10px);
        transition: max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, transform 0.25s ease;
    }

    .sidebar-menu li.active .treeview-menu,
    .sidebar-menu li.menu-open .treeview-menu {
        display: block;
        max-height: 500px; /* Adjust based on content */
        opacity: 1;
        transform: translateY(0);
    }

    .sidebar-collapse .sidebar-menu li:hover .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }

    /* Animation Option 2: Ultra Fast (0.15s) - Uncomment to use */
    /*
    .sidebar-menu .treeview-menu {
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-5px);
        transition: max-height 0.15s ease-out, opacity 0.15s ease-out, transform 0.15s ease-out;
    }

    .sidebar-menu li.active .treeview-menu,
    .sidebar-menu li.menu-open .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }

    .sidebar-collapse .sidebar-menu li:hover .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }
    */

    /* Animation Option 3: Gentle & Slow (0.4s) - Uncomment to use */
    /*
    .sidebar-menu .treeview-menu {
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-15px) scale(0.95);
        transition: max-height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.4s ease, transform 0.4s ease;
    }

    .sidebar-menu li.active .treeview-menu,
    .sidebar-menu li.menu-open .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .sidebar-collapse .sidebar-menu li:hover .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    */

    /* Animation Option 4: Bounce Effect - Uncomment to use */
    /*
    .sidebar-menu .treeview-menu {
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-20px);
        transition: max-height 0.3s ease, opacity 0.3s ease, transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .sidebar-menu li.active .treeview-menu,
    .sidebar-menu li.menu-open .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }

    .sidebar-collapse .sidebar-menu li:hover .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }
    */

    /* Animation Option 5: Fade Only (No Slide) - Uncomment to use */
    /*
    .sidebar-menu .treeview-menu {
        display: none;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }

    .sidebar-menu li.active .treeview-menu,
    .sidebar-menu li.menu-open .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
    }

    .sidebar-collapse .sidebar-menu li:hover .treeview-menu {
        display: block;
        max-height: 500px;
        opacity: 1;
    }
    */

    .sidebar-menu > li > .treeview-menu {
        background: var(--gradient-primary) !important;
    }

    .table.table-hover tbody tr:hover {
        color: var(--text-secondary-color) !important;
        background-color: var(--primary-hover-alpha) !important;
    }


    .input-group .input-group-addon {
        color: var(--text-secondary-color) !important;
        background-color: var(--second-alpha) !important;
    }

    .rtl .input-group .form-control {
        float: right;
    }

    .box-footer {
        border-top: 2px solid var(--second-alpha) !important;
    }

    .btn-info {
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-color) 100%) !important;
        color: var(--text-secondary-color) !important;
        border-color: var(--secondary-color);
    }

    .btn-info:hover {
        color: var(--secondary-color) !important;
    }

    .btn-primary {
        background: var(--secondary-color) !important;
        color: var(--text-secondary-color) !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
        transition: var(--transition) !important;
        box-shadow: var(--shadow-sm) !important;
    }

    .btn-primary:hover {
        transform: translateY(-1px) !important;
        box-shadow: var(--shadow-md) !important;
        color: #ffffff !important;
    }

    .btn-success {
        background: var(--success-button) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
        transition: var(--transition) !important;
        box-shadow: var(--shadow-sm) !important;
    }

    .btn-success:hover {
        transform: translateY(-1px) !important;
        box-shadow: var(--shadow-md) !important;
        color: #ffffff !important;
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
        color: var(--inverse-color) !important;
    }

    .btn-dropbox:hover,
    .btn-instagram:hover,
    .btn-twitter:hover,
    .btn-success:hover {
        background: var(--primary-color);
        filter: brightness(0.85);
        color: var(--secondary-color) !important;
    }

    .content-header > .breadcrumb > li > a {
        color: var(--text-secondary-color) !important;
    }

    .skin-black-light .main-header > .navbar > .sidebar-toggle {
        background: transparent !important;
        border: none !important;
        color: #374151 !important;
        padding: 8px 12px !important;
        border-radius: 6px !important;
        transition: var(--transition) !important;
        margin-right: 16px !important;
    }

    .navbar-nav {
        margin: 0 !important;
        margin-left: auto !important;
    }

    .skin-black-light .main-header > .navbar .nav > li > a,
    .skin-black-light .main-header > .navbar .nav > li > a:active,
    .skin-black-light .main-header > .navbar .nav > li > a:focus,
    .skin-black-light .main-header > .navbar .nav .open > a,
    .skin-black-light .main-header > .navbar .nav .open > a:hover,
    .skin-black-light .main-header > .navbar .nav .open > a:focus,
    .skin-black-light .main-header > .navbar .nav > .active > a {
        background-color: var(--second-color) !important;
        border-left: 1px solid var(--second-alpha) !important;
    }

    .skin-black-light .main-header > .logo {
        color: #374151 !important;
        border: none !important;
        padding: 0 24px !important;
        display: flex !important;
        align-items: center !important;
        font-weight: 600 !important;
        font-size: 1.25rem !important;
        box-shadow: var(--shadow-sm) !important;
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

    .table {
        background: #ffffff !important;
        border-radius: var(--border-radius) !important;
        box-shadow: var(--shadow-sm) !important;
    }

    .table > thead > tr > th {
        background: #f9fafb !important;
        border-bottom: 2px solid #e5e7eb !important;
        color: #374151 !important;
        font-weight: 600 !important;
        padding: 12px 16px !important;
        text-transform: uppercase !important;
        /* font-size: 0.875rem !important; */ /* يافنان اوعي تفعل دي بتأثر علي كل الداش بورد     ***** امضاء  شامي***** */

        letter-spacing: 0.05em !important;
    }

    .table > tbody > tr {
        transition: var(--transition) !important;
    }

    .table > tbody > tr:hover {
        background: #f9fafb !important;
    }

    .table > tbody > tr > td {
        border-top: 1px solid #f3f4f6 !important;
        padding: 12px 16px !important;
        color: #374151 !important;
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
        float: left !important;
    }

    .rtl [class*="col-md-12"] {
        float: none !important;
    }


    .rtl [class*="col-md-6"] {
        float: right;
    }

    .skin-black-light .main-header > .navbar .sidebar-toggle:hover {
        background: #f3f4f6 !important;
        color: var(--primary-color) !important;
        transform: scale(1.05) !important;
    }

    .select2-dropdown {
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        box-shadow: var(--shadow-lg) !important;
        margin-top: 4px !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: rgba(37, 99, 235, 0.1) !important;
        color: #374151 !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: rgba(37, 99, 235, 0.1) !important;
        color: #374151 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151 !important;
    }

    .select2-container--default .select2-selection--single {
        border: 1px solid #d1d5db !important;
        border-radius: 8px !important;
        background: #ffffff !important;
        height: 36px !important;
        line-height: 36px !important;
    }


    .input-group .input-group-addon {
        border-radius: 0;
        border-color: var(--primary-hover-alpha) !important;
        background-color: #fff;
    }

    .modal-backdrop {
        position: static !important;
    }

    .modal-content {
        position: relative;
        background-color: var(--box-background-color) !important;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;

        border-radius: 6px;
        outline: 0;
        -webkit-box-shadow: 0 3px 9px rgba(0, 0, 0, .5);
        box-shadow: 0 3px 9px rgba(0, 0, 0, .5);
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
        background: transparent !important;
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


    .skin-black-light .content-wrapper, .skin-black-light .main-footer {
        background-image: none !important;
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

    .rtl .pull-right > .dropdown-menu {
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
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
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
        background-color: var(--box-background-color) !important;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 6px;
        -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        line-break: auto;
    }

    .popover.top > .arrow:after {
        bottom: 1px;
        margin-left: -10px;
        content: " ";
        border-top-color: var(--box-background-color) !important;
        border-bottom-width: 0;
    }

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
        background: var(--gradient-primary) !important;
        filter: brightness(0.80);
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid var(--primary-hover-alpha) !important;
        border-radius: 4px;
        -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
        box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
        color: var(--inverse-box-color) !important;
    }

    .flag-image {
        height: 30px; /* adjust size */
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

    .rtl .column-reward .rtlSvga {
        direction: ltr !important;
    }

    select > option {
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

    .navbar-nav > .user-menu > .dropdown-menu > .user-footer {
        background-color: var(--box-background-color) !important;
        padding: 10px;
    }

    .skin-black-light .main-header li.user-header {
        background-color: var(--box-background-color) !important;
    }

    .navbar-nav > .user-menu > .dropdown-menu > li.user-header > img {
        z-index: 5;
        height: 90px;
        width: 90px;
        border: 3px solid var(--primary-hover-alpha) !important;

    }

    .navbar-nav > .user-menu > .dropdown-menu > li.user-header > p {
        z-index: 5;
        color: var(--inverse-box-color) !important;
        font-size: 17px;
        margin-top: 10px;
    }

    .inputs_cus_form {
        color: var(--text-secondary-color) !important;
        background-color: var(--box-background-color) !important;
    }

    .dd-handle {
        display: block;
        margin: 1px 0;
        padding: 8px 10px;
        color: var(--text-secondary-color) !important;
        text-decoration: none;
        border: 1px solid #ddd;
        background: var(--box-background-color) !important;
    }

    a {
        color: var(--text-secondary-color) !important;
    }

    .dropdown-menu > li > a {
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
        margin-right: 260px;
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
        width: 82.5%;
    }

    .ltr .navbar-static-top {
        margin-right: 0 !important;
        float: right;
        width: 82.5%;
    }

    .ltr .main-header>.navbar {
        margin-left: 0 !important;
    }

    .rtl .navbar-custom-menu > .navbar-nav > li > .dropdown-menu {
        position: absolute;
        right: 0;
        left: auto;
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

    .rtl .form-horizontal .row {
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
        margin-right: 42px !important;
    }

    .ltr .content-wrapper {
        margin-left: 260px !important;
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
    .rtl .sidebar-menu .treeview-menu > li.active > a > .fa-angle-left,
    .rtl .sidebar-menu .treeview-menu > li.active > a > .fa-angle-down {
        transform: rotate(-90deg) !important;
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
        background: var(--gradient-primary) !important;
        color: var(--text-secondary-color) !important;
    }

    .btn-warning {
        background-color: var(--primary-color);
        color: var(--text-secondary-color);
        border-color: var(--secondary-color);
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 20px !important;
        font-weight: 500 !important;
        transition: var(--transition) !important;
        box-shadow: var(--shadow-sm) !important;
    }

    .btn-warning:hover {
        background-color: var(--primary-color);
        color: var(--secondary-color) !important;
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
        position: absolute;
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

    .rtl label.control-label.pull-right small:last-of-type {
        margin-left: 50px;
    }

    .small-box h3 {
        font-size: x-large !important;
    }

    .small-box:hover .icon {
        font-size: 80px;
        transform: translateY(-37px);
        transition: all 0.3s ease;
    }

    .small-box .icon {
        font-size: 50px;
        top: 25px;
        right: 2px;
    }

    .preview-superadmin-btn,
    .exit-preview-btn {
        border: none;
        border-radius: 4px;
        padding: 4px 14px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    #go-superadmin i {
        font-size: 15px;
    }

    .payment-card {
        height: 580px;
    }

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

    /* form inputs */
    .form-horizontal .fields-group > .col-md-12 {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
        direction: rtl !important;
        width: 100% !important;
        padding: 15px !important;
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
        text-align: right !important;
        margin-bottom: 8px !important;
        padding: 0 5px !important;
        font-weight: 600;
        order: 1;
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
        display: block !important;
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

    .file-input .input-group.file-caption-main .file-caption {
        direction: rtl !important;
    }

    .file-input .input-group.file-caption-main .input-group-btn {
        position: absolute !important;
    }

    .file-input .input-group.file-caption-main .btn-file {
        padding: 5px 15px !important;
        border-radius: 6px !important;
        font-size: 12px !important;
        margin-top: 65% !important;
        margin-right: 100% !important;
    }

    .rtl .file-input .input-group.file-caption-main .btn-file {
        margin-left: 100% !important;
    }

    .form-control:focus {
        border-color: var(--secondary-color) !important;
    }

    @media (max-width: 768px) {
        .form-horizontal .fields-group > .col-md-12 {
            grid-template-columns: 1fr !important;
        }
    }
    /* end form inputs */

    .iti--separate-dial-code .iti__selected-flag {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    .stats-container .info-box {
        background: linear-gradient(135deg,
        #667eea 0%,
        #8B5CF6 25%,
        #A855F7 50%,
        #C084FC 75%,
        #E879F9 100%
        ) !important;
        background-attachment: fixed !important;
    }
</style>
