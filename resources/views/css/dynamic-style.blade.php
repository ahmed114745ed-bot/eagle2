/* Dynamic CSS */
.skin-black-light .wrapper, 
.skin-black-light .main-sidebar, 
.skin-black-light .left-side {
    background-color: {{ config('thems.sideMenuBackground') }} !important;
}

.skin-black-light .sidebar-menu>li.header {
    color: {{ config('thems.sideMenuWordMenuColor') }} !important;
    background: {{ config('thems.sideMenuWordMenuBackground') }} !important;
}

body {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-size: 14px;
    line-height: 1.42857143;
    color: {{ config('thems.bodyColor') }}!important;
    background-color: {{ config('thems.bodyBackGroundColor') }}!important;
}

.skin-black-light .content-wrapper, .skin-black-light .main-footer {
    border-left: 1px solid #263552;
    background-image: none !important;
    background-color: {{ config('thems.bodyBackGroundColor') }};
}

.content-header {
    background-color: {{ config('thems.bodyBackGroundColor') }} !important;
}

.content-header>.breadcrumb {
    background:{{ config('thems.bodyBackGroundColor') }} !important;
}

.skin-black-light .sidebar a {
    color: {{ config('thems.sideMenuListWordColor') }} !important;
}

.skin-black-light .sidebar a i{
    color: {{ config('thems.sideMenuListIconColor') }} !important;
}

.box {
    background: {{ config('thems.boxBackGround') }} !important;
    color: {{ config('thems.boxColor') }} !important;
}

.skin-black-light .sidebar-menu>li:hover>a, .skin-black-light .sidebar-menu>li.active>a {
    color: {{ config('thems.sideMenuListHoverColor') }} !important;
    background: {{ config('thems.sideMenuListHoverBackground') }} !important;
}
.skin-black-light .sidebar-menu>li:hover>a, .skin-black-light .sidebar-menu>li.active>a i{
    color: {{ config('thems.sideMenuListHoverColor') }} !important;
    background: {{ config('thems.sideMenuListHoverBackground') }} !important;
}

.skin-black-light .treeview-menu>li.active>a, .skin-black-light .treeview-menu>li>a:hover {
    color: {{ config('thems.sideMenuListActiveHoverColor') }} !important;
}

.sidebar-menu>li>.treeview-menu {
    background: {{ config('thems.sideMenuListTreeView') }} !important;
}

.table.table-hover tbody tr:hover {
    color: #fdf8f8 !important;
    background-color: #FF9428 !important; 
}

.filter-box {
    color: #ffffff !important; /* اللون الأبيض للنص */
    {{-- background-color: rgba(255, 255, 255, 0.8) !important; /* اللون الأبيض بخلفية شفافة */ --}}
}

/* تعديل شكل ولون الحقول */
.filter-box input, 
.filter-box select {
    background-color: rgba(255, 255, 255, 0.1) !important; 
}
.input-group .input-group-addon {
    border-radius: 0;
    border-color: #d2d6de;
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.1) !important; 
}


.box-footer {
    background-color: rgba(255, 255, 255, 0.1) !important; 
}

.btn-primary {
    background-color: #2b353b;
    background-color: #FF9428 !important;
}


.form-control, /* يشمل input و textarea */
select,
.select2-container .select2-selection--single,
.select2-container .select2-selection--multiple {
    background-color: rgba(255, 255, 255, 0.1) !important; 
    color: #ffffff; 
}
/* تخصيص زر التحويل في switch */
.bootstrap-switch.bootstrap-switch-on .bootstrap-switch-handle-on {
    background-color: #FF9428 !important;
    color: #ffffff; 
}

.box-header>.fa, .box-header>.glyphicon, .box-header>.ion, .box-header .box-title {
    color: #c1b9b9 !important;
}

.grid-create-btn>.btn-success {
    background-color: #FF9428 !important;
    color: #ffffff; 
}

.btn-dropbox {
    color: #fdf8f8;
    background-color: #FF9428 !important;
    border-color: rgba(0, 0, 0, 0.2);
}

.btn-instagram {
    color: #fdf8f8;
    background-color: #FF9428 !important;
    border-color: rgba(0, 0, 0, 0.2);
}

.btn-success {
    background-color: #FF9428 !important; /* لون الخلفية الجديد */
    color: #ffffff !important; /* لون النص */
 
    transition: background-color 0.3s ease; /* تأثير انتقال للون */
}

.content-header>.breadcrumb>li>a {
    color: #ffffff !important;
}

.skin-black-light .main-header>.navbar {
    color: #FF9428 !important;
    background-color: rgba(255, 255, 255, 0.1) !important; 
}

.skin-black-light .main-header>.navbar>.sidebar-toggle {
    color: #FF9428 !important;
}

.skin-black-light .main-header>.navbar .nav>li>a, .skin-black-light .main-header>.navbar .nav>li>a:active, .skin-black-light .main-header>.navbar .nav>li>a:focus, .skin-black-light .main-header>.navbar .nav .open>a, .skin-black-light .main-header>.navbar .nav .open>a:hover, .skin-black-light .main-header>.navbar .nav .open>a:focus, .skin-black-light .main-header>.navbar .nav>.active>a {
    background-color: rgba(255, 255, 255, 0.1) !important; 
    color: #FF9428 !important;
}

.skin-black-light .main-header>.logo {
    background-color: rgba(255, 255, 255, 0.1) !important; 
    color: #FF9428 !important;
}

.cardHome {
    color: #fdf8f8 !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    width: 44%;
    margin: inherit;
    padding: 20px;
    background-color: #FF9428 !important;
    margin-top: 10px;
}