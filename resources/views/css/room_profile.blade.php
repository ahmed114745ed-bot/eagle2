<style>
    /* ═══════════════════════════════════════════════════════════
       ROOM PROFILE — CLEAN PROFESSIONAL UI
       ═══════════════════════════════════════════════════════════ */

    :root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
        --text-primary-color: {{ config('themes.textPrimaryColor') }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') }};
        --box-background-color: {{ config('themes.boxBackgroundColor') }};
        --table-background-color: {{ config('themes.tableBackGroundColor') }};
        --background-image: {{ config('themes.backgroundImage') }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
        --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
        --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
        --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;
        --inverse-color: {{ getLighterColor(config('themes.primaryColor')) }};
        --inverse-box-color: {{ adjustTextColor(config('themes.boxBackgroundColor')) }};
        --success-button: linear-gradient(90deg, {{ adjustColor(config('themes.primaryColor')) }} 0%, {{ config('themes.primaryColor') }} 100%);
        --primary-button: linear-gradient(90deg, {{ adjustColor(config('themes.primaryColor')) }} 0%, {{ config('themes.primaryColor') }} 100%);
    }

    /* ── Reset ────────────────────────────────────────────── */
    .room-profile-page * { box-sizing: border-box; }

    .room-profile-page {
        max-width: 1300px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: #1e293b;
    }

    /* ══════════════════════════════════════════════════════════
       HEADER CARD
       ══════════════════════════════════════════════════════════ */
    .room-header-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);
        padding: 28px 32px;
        margin-bottom: 24px;
        border: 1px solid #e8ecf1;
    }

    /* Top row: Avatar + Info + Actions */
    .room-header-top {
        display: flex;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 20px;
    }

    .room-avatar {
        flex-shrink: 0;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e8ecf1;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    .room-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .room-header-info {
        flex: 1;
        min-width: 0;
    }

    .room-name {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }

    /* Meta row (ID, UID, etc.) */
    .room-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .room-meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12.5px;
        color: #64748b;
        white-space: nowrap;
    }

    .room-meta-tag i {
        font-size: 10px;
        color: #94a3b8;
    }

    .room-meta-tag strong {
        color: #334155;
        font-weight: 700;
    }

    /* Owner row */
    .room-owner-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 13.5px;
    }

    .room-owner-row .owner-label {
        color: #64748b;
        font-weight: 500;
    }

    .room-owner-row a {
        color: var(--primary-color);
        font-weight: 700;
        text-decoration: none;
    }

    .room-owner-row a:hover {
        text-decoration: underline;
    }

    /* Status + Features row */
    .room-status-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill.active {
        background: #dcfce7;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .status-pill.active::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
        animation: blink 1.5s infinite;
    }

    .status-pill.inactive {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: .3; }
    }

    .feature-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .feature-tag i { font-size: 9px; }

    .feature-tag.popular     { background: #f59e0b; }
    .feature-tag.top         { background: #3b82f6; }
    .feature-tag.recommended { background: #8b5cf6; }
    .feature-tag.secret      { background: #eab308; color: #1e293b; }
    .feature-tag.live        { background: #ef4444; }

    /* Header actions */
    .room-header-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-edit-room {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        background: #1e293b;
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .btn-edit-room:hover {
        background: #334155;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }

    /* ══════════════════════════════════════════════════════════
       STATS ROW
       ══════════════════════════════════════════════════════════ */
    .room-stats-row {
        display: flex;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .room-stat-item {
        flex: 1;
        text-align: center;
        padding: 14px 8px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
        transition: all .2s ease;
    }

    .room-stat-item:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .room-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: #fff;
        margin-bottom: 8px;
    }

    .room-stat-icon.c-blue   { background: #3b82f6; }
    .room-stat-icon.c-green  { background: #10b981; }
    .room-stat-icon.c-amber  { background: #f59e0b; }
    .room-stat-icon.c-purple { background: #8b5cf6; }

    .room-stat-value {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }

    .room-stat-value img {
        height: 22px;
        vertical-align: middle;
    }

    .room-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 2px;
    }

    /* ══════════════════════════════════════════════════════════
       ERROR ALERT
       ══════════════════════════════════════════════════════════ */
    .room-alert {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 16px;
        color: #dc2626;
        font-size: 13px;
    }

    .room-alert ul {
        margin: 0;
        padding-left: 16px;
    }

    /* ══════════════════════════════════════════════════════════
       TABS
       ══════════════════════════════════════════════════════════ */
    .agency-tabs {
        display: flex;
        gap: 4px;
        padding: 5px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        margin-bottom: 24px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e8ecf1;
    }

    .tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        background: transparent;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        white-space: nowrap;
        transition: all .2s ease;
        text-decoration: none;
    }

    .tab-btn i {
        font-size: 13px;
        opacity: .7;
    }

    .tab-btn:hover:not(.active) {
        background: #f1f5f9;
        color: #334155;
    }

    .tab-btn.active {
        background: var(--primary-color);
        color: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.12);
    }

    .tab-btn.active i {
        opacity: 1;
    }

    /* ══════════════════════════════════════════════════════════
       TAB CONTENT
       ══════════════════════════════════════════════════════════ */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* ══════════════════════════════════════════════════════════
       CARDS
       ══════════════════════════════════════════════════════════ */
    .card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        margin-bottom: 24px;
        border: 1px solid #e8ecf1;
        overflow: hidden;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfc;
    }

    .card-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i {
        color: var(--primary-color);
        font-size: 14px;
    }

    .card .p-3 {
        padding: 20px 24px;
    }

    /* ══════════════════════════════════════════════════════════
       TABLES
       ══════════════════════════════════════════════════════════ */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead th {
        padding: 11px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 2px solid #e8ecf1;
        white-space: nowrap;
        text-align: start;
    }

    .table tbody td {
        padding: 12px 16px;
        font-size: 13px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background: #fafbfc;
    }

    /* User cell */
    .user-cell {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: inherit;
    }

    .user-cell img {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e8ecf1;
        flex-shrink: 0;
    }

    .user-cell:hover img {
        border-color: var(--primary-color);
    }

    .user-cell-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .user-cell-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 13px;
        line-height: 1.3;
    }

    .user-cell-meta {
        font-size: 11.5px;
        color: #94a3b8;
        line-height: 1.3;
    }

    /* ══════════════════════════════════════════════════════════
       BADGES
       ══════════════════════════════════════════════════════════ */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        border: none;
    }

    .badge-primary   { background: #dbeafe; color: #2563eb; }
    .badge-success   { background: #dcfce7; color: #16a34a; }
    .badge-danger    { background: #fee2e2; color: #dc2626; }
    .badge-warning   { background: #fef3c7; color: #b45309; }
    .badge-info      { background: #e0e7ff; color: #4f46e5; }
    .badge-secondary { background: #f1f5f9; color: #64748b; }

    /* ══════════════════════════════════════════════════════════
       BUTTONS
       ══════════════════════════════════════════════════════════ */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 7px;
        font-weight: 600;
        font-size: 12.5px;
        padding: 7px 14px;
        border: none;
        cursor: pointer;
        transition: all .2s ease;
        text-decoration: none;
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
    }

    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .btn-danger:hover {
        background: #dc2626;
        color: #fff;
    }

    .btn-warning {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .btn-warning:hover {
        background: #f59e0b;
        color: #fff;
    }

    .btn-success {
        background: #dcfce7;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .btn-success:hover {
        background: #16a34a;
        color: #fff;
    }

    .btn-info {
        background: var(--primary-color);
        color: #fff;
        border: none;
    }
    .btn-info:hover {
        opacity: .9;
    }

    .btn-light, .btn-default, .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .btn-light:hover, .btn-default:hover, .btn-secondary:hover {
        background: #e2e8f0;
    }

    /* ══════════════════════════════════════════════════════════
       FORM CONTROLS
       ══════════════════════════════════════════════════════════ */
    .gift-log-form {
        background-color: transparent !important;
        filter: none !important;
    }

    .form-control, .form-select {
        height: 40px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 8px 12px;
        font-size: 13px;
        color: #334155;
        background: #f8fafc;
        transition: all .2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(99,102,241,.08);
        background: #fff;
        outline: none;
    }

    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    /* ══════════════════════════════════════════════════════════
       DIAMOND SUMMARY
       ══════════════════════════════════════════════════════════ */
    .diamond-summary-container {
        display: flex;
        justify-content: center;
        padding: 16px 0;
    }

    .diamond-summary-box {
        max-width: 400px;
        width: 100%;
        background: linear-gradient(135deg, var(--primary-color), #6366f1);
        border-radius: 12px;
        padding: 20px 28px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }

    .diamond-title {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: rgba(255,255,255,.7);
        margin-bottom: 8px;
    }

    .diamond-count {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 26px;
        font-weight: 800;
        color: #fff;
    }

    .diamond-icon-container {
        background: rgba(255,255,255,.15);
        border-radius: 50%;
        padding: 6px;
        display: inline-flex;
    }

    .diamond-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
    }

    /* ══════════════════════════════════════════════════════════
       PK
       ══════════════════════════════════════════════════════════ */
    .team-info {
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #f1f5f9;
    }

    .team-title {
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .team-boss {
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: inherit;
        margin-bottom: 4px;
    }

    .boss-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    .members-count {
        color: var(--primary-color);
        font-size: 12px;
        font-weight: 600;
    }

    .members-count:hover { text-decoration: underline; }

    .score-info { display: flex; flex-direction: column; gap: 3px; }

    .team-score {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
    }

    .score-label { font-weight: 600; color: #64748b; }
    .score-value { font-weight: 800; color: #ef4444; }

    .status-container {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }

    .winner-badge {
        background: #16a34a;
        color: #fff;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
    }

    .time-info {
        font-size: 12px;
        color: #64748b;
        line-height: 1.7;
    }

    /* ══════════════════════════════════════════════════════════
       BOXES / PROGRESS
       ══════════════════════════════════════════════════════════ */
    .progress {
        background: #e2e8f0;
        border-radius: 50px;
        overflow: hidden;
        height: 6px !important;
    }

    .progress-bar {
        border-radius: 50px;
        transition: width .4s ease;
    }

    .progress-info { width: 100%; }

    .show-users-text {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 12px;
    }

    .show-users-text:hover { text-decoration: underline; }

    /* ══════════════════════════════════════════════════════════
       MODAL MEMBERS
       ══════════════════════════════════════════════════════════ */
    .team-members-list {
        max-height: 450px;
        overflow-y: auto;
        padding: 4px;
    }

    .member-item {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s ease;
    }

    .member-item:hover { background: #f8fafc; }
    .member-item:last-child { border-bottom: none; }

    .member-info {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
    }

    .member-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e8ecf1;
    }

    .member-details { flex: 1; }

    .member-name {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .member-id {
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.4;
    }

    /* ══════════════════════════════════════════════════════════
       MODAL
       ══════════════════════════════════════════════════════════ */
    .modal-content {
        border-radius: 14px;
        border: none;
        box-shadow: 0 8px 30px rgba(0,0,0,.12);
    }

    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 16px 24px;
    }

    .modal-title {
        font-weight: 700;
        font-size: 16px;
    }

    .modal-body { padding: 24px; }

    .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 14px 24px;
    }

    /* ══════════════════════════════════════════════════════════
       LOADING
       ══════════════════════════════════════════════════════════ */
    #tab-loading {
        background: rgba(30,41,59,.92) !important;
        color: #fff !important;
        border-radius: 12px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        padding: 16px 28px !important;
        transform: translate(-50%, -50%);
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }

    /* ══════════════════════════════════════════════════════════
       PAGINATION
       ══════════════════════════════════════════════════════════ */
    .pagination {
        gap: 3px;
    }

    .pagination .page-item .page-link {
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 12.5px;
        padding: 6px 12px;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }

    /* ══════════════════════════════════════════════════════════
       EMPTY STATE
       ══════════════════════════════════════════════════════════ */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .empty-state i { font-size: 32px; opacity: .4; }
    .empty-state p { margin: 0; font-size: 13px; font-weight: 500; }

    /* ══════════════════════════════════════════════════════════
       RESPONSIVE
       ══════════════════════════════════════════════════════════ */
    @media (max-width: 768px) {
        .room-header-top {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .room-meta-row {
            justify-content: center;
        }

        .room-owner-row {
            justify-content: center;
        }

        .room-status-row {
            justify-content: center;
        }

        .room-header-actions {
            justify-content: center;
            width: 100%;
        }

        .room-stats-row {
            flex-wrap: wrap;
        }

        .room-stat-item {
            flex: 1 1 calc(50% - 6px);
            min-width: 0;
        }

        .room-avatar {
            width: 80px;
            height: 80px;
        }

        .room-name {
            font-size: 20px;
        }
    }

    @media (max-width: 480px) {
        .room-profile-page {
            padding: 12px;
        }

        .room-header-card {
            padding: 20px 16px;
        }

        .room-stats-row {
            gap: 8px;
        }

        .room-stat-item {
            flex: 1 1 calc(50% - 4px);
            padding: 10px 6px;
        }

        .tab-btn {
            padding: 8px 14px;
            font-size: 12px;
        }

        .table thead th, .table tbody td {
            padding: 10px 10px;
            font-size: 12px;
        }
    }

    /* ── Utility ───────────────────────────────────────────── */
    .text-center .p-3 { color: #94a3b8; font-size: 13px; }
    .text-center .p-3 i { margin-right: 5px; }
    .me-2 { margin-right: .5rem; }
    .float-right { float: right; }
    .edit-btn { background: var(--primary-color) !important; }
    .level-form { background-color: transparent !important; filter: none !important; padding: 10px; }
    .level-label { padding: 10px; }
    .ltr .gift-log-form { padding-left: 0; }

    /* Select2 */
    .select2-container--default .select2-selection--single {
        height: 40px;
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 4px 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
        color: #334155;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 6px;
    }
</style>
