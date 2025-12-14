<style>
    .inner-settings-menu {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .inner-settings-menu button {
        padding: 10px 20px;
        background: var(--secondary-color);
        color: black;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    
.settings-section {
    display: none; /* Hide all sections by default */
}

.settings-section.active {
    display: block; /* Only show the active section */
}
.settings-menu button {
    display: inline-flex;   /* important: keep inline alignment */
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: none;
    background-color: #f0f0f0;
    color: #333;
    cursor: pointer;
    white-space: nowrap;    /* prevent text from wrapping */
}

.settings-menu button.active {
    background-color:var(--primary-color); /* active tab color */
    color: white;
}

    .inner-settings-menu button.active {
        background: var(--primary-color);
        color: black;
    }

    .settings-section {
        display: none;
    }

    .settings-section.active {
        display: block;
    }

    .colorpicker {
        min-width: 220px;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        font-family: Arial, sans-serif;
    }

    .colorpicker-saturation {
        border-radius: 5px !important;
    }

    .colorpicker-hue {
        border-radius: 5px !important;
    }

    .colorpicker-alpha {
        border-radius: 5px !important;
    }

    .colorpicker-color div {
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .colorpicker.colorpicker-right {
        left: auto !important;
        right: 0 !important;
    }

    .colorpicker.colorpicker-left {
        left: 0 !important;
        right: auto !important;
    }

    .radio-options-container {
        display: flex;
        gap: 20px;
        align-items: center;
        margin: 15px 0;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .radio-input {
        margin: 0;
    }

    .radio-label {
        margin: 0;
        cursor: pointer;
        user-select: none;
    }

    .radio-input {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border: 2px solid #ff9800;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
    }

    .radio-input:checked {
        background-color: #ff9800;
    }

    .radio-input:checked::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--secondary-color);
        color: white;
        display: flex;
    }

    .settings-sidebar {
        width: 250px;
        background: #222;
        min-height: 400px;
        padding: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
    }

    .settings-sidebar h2 {
        text-align: center;
        color: #ff9800;
    }

    .settings-content {
        flex-grow: 1;
        padding: 20px;
    }

    .settings-section {
        display: none;
    }

    .active {
        display: block;
    }

    .settings-section.active {
        display: block;
    }

    form {
        background: var(--box-background-color);
        padding: 20px;
        border-radius: 5px;
        width: 100%;
        position: relative;
        margin: auto;
    }

    label {
        display: block;
        margin: 10px 0 5px;
    }

    input,
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        background: #333;
        border: 1px solid #444;
        color: white;
    }

    button {
        padding: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    .all-page {
        display: inline-flex;
    }

    .wrapper {
        width: 100%;
    }

    .settings-content {
        width: 869px;
    }

    button {
        width: 171px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: var(--secondary-color);;
    }

    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 700px;
    }

    .close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    img {
        width: 201px;
        display: block;
        height: 99px;
        margin-bottom: 20px;
    }

    .settings-sidebar {
        background-color: var(--table-background-color);
        display: inline;
        justify-content: center;
        align-items: center;
        padding: 10px 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: var(--text-secondary-color);
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: thin;
    }

    .settings-menu {
        display: block;
        gap: 4px;
        color: var(--text-secondary-color);
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: thin;
    }

    .settings-menu button {
        background-color: var(--secondary-color);
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        cursor: pointer;
        transition: color 0.3s ease-in-out;
        color: var(--text-secondary-color) !important;
    }

    .card {
        border-radius: 10px;
        border: 1px solid #ddd;
        background: "{{ $settings['primary_color'] ?? '#000000' }}";
        box-shadow: 2px 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        transition: transform 0.2s ease-in-out;
        margin-top: 40px;
        position: relative;
    }

    .btn-save {
        position: absolute;
        bottom: 15px;
        left: 15px;
    }

    .card:hover {
        transform: scale(1.02);
    }

    .card-header {
        background: {{ $settings['primary_color'] ?? '#000000' }};
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        border-radius: 8px 8px 0 0;
        text-align: center;
        font-weight: bold;
        font-size: 1.2rem;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h4 {
        margin: 0;
    }

    .d-flex.align-items-center {
        gap: 10px;
    }

    .custom-radio {
        display: none;
    }

    .custom-payment-radio {
        display: none;
    }

    .switch {
        display: inline-block;
        width: 50px;
        height: 25px;
        background-color: #ccc;
        border-radius: 25px;
        position: relative;
        cursor: pointer;
        transition: background 0.3s;
    }

    .switch::after {
        content: "";
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        left: 5px;
        transform: translateY(-50%);
        transition: left 0.3s;
    }

    .switch.active {
        background: #4caf50;
    }

    .switch.active::after {
        left: 25px;
    }

    .border-success {
        border: 5px solid #4caf50;
    }

    .position-relative {
        position: relative;
        overflow: visible;
    }

    .ribbon-banner {
        position: absolute;
        top: 6px;
        right: -10px;
        background-color: #ff0000;
        padding: 2px 7px;
        transform: rotate(90deg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .ribbon-banner-card {
        position: absolute;
        top: 6px;
        right: -9px;
        background-color: #ff0000;
        padding: 2px 7px;
        transform: rotate(90deg);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .ribbon-banner-card span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);

    }

    .rtl .ribbon-banner {
        right: auto !important;
        left: -11px !important;
        padding: 2px 13px !important;
    }

    .ribbon-banner span {
        color: white;
        font-size: 15px;
        font-weight: normal;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    }

    .text-center.my-3 img.img-fluid {
        max-height: 80px;
        display: unset !important;
        margin-top: 20px;
    }

    .card-top {
        margin-top: 33px;
    }

    .no-background-form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .exp-card {
        margin-bottom: 32px;
    }

    .exp-card-cont {
        height: 400px;
    }

    .copy-container {
        position: relative;
    }

    .copy-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none !important;
        cursor: pointer;
        padding: 0;
        font-size: 16px;
    }

    .ltr .copy-button {
        left: 95px;
    }

    .rtl .copy-button {
        right: 95px;
    }

    #landPageSettings {
        max-width: 1020px;
        margin: 18px auto;
        border-radius: 10px;
        padding: 22px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        color: #222;
        background: white;
    }

    .settings-container {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .tabs-sidebar {
        min-width: 230px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-left: 1px solid #eee;
        padding-left: 12px;
        border-right: none;
        direction: rtl;
    }

    .tab-btn {
        background-color: var(--secondary-color);
        border: none;
        padding: 10px 15px;
        border-radius: 10px;
        text-align: right;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        transform: translateY(-1px);
    }

    .tab-btn.active {
        background: var(--primary-color);
        color: #fff;
        border-color: rgba(13, 110, 253, 0.9);
    }

    .tab-content {
        flex: 1;
        min-width: 0;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.show {
        display: block;
    }

    #landPageSettings h5 {
        font-weight: 700;
        margin-bottom: 8px;
    }

    #landPageSettings hr {
        margin-top: 8px;
        margin-bottom: 14px;
        border: none;
        height: 1px;
    }

    .form-control {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #dcdcdc;
        border-radius: 6px;
        box-sizing: border-box;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .col-md-6 {
        flex: 0 0 calc(50% - 12px);
        min-width: 240px;
    }

    .col-md-4 {
        flex: 0 0 calc(33.333% - 12px);
        min-width: 160px;
    }

    @media (max-width: 576px) {
    }

    @media (max-width: 768px) {
        form {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            position: relative;
            margin: auto;
        }

        .settings-menu {
            display: flex;
            flex-wrap: nowrap;
        }

        .settings-menu button {
            display: inline-block;
            min-width: 150px;
            margin-right: 0.5rem;
            margin-bottom: 0;
            white-space: normal;
        }

        .settings-container {
            flex-direction: column;
        }

        .tabs-sidebar {
            width: 100%;
            order: 0;
            border-right: none;
            border-bottom: 1px solid #eee;
            padding-right: 0;
            padding-bottom: 10px;
            flex-direction: row; /* horizontal layout */
            justify-content: flex-start;
            gap: 10px;
            overflow-x: auto;
        }

        .tab-btn {
            white-space: nowrap;
            padding: 8px 10px;
            font-size: 14px;
        }

        .tab-content {
            margin-top: 12px;
        }

        .col-md-6, .col-md-4 {
            flex: 1 1 100%;
            min-width: 0;
        }

        .settings-content {
            width: 100% !important;
        }
    }

    @media (max-width: 992px) {
    }

    @media (max-width: 1200px) {
    }

    @media (max-width: 1400px) {
        .form-control {
            width: 170px !important;
        }

        .rtl .copy-button {
            right: 75px;
        }
    }
</style>
