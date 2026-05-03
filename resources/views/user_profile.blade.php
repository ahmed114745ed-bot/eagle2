@php use App\Helpers\Common;use Carbon\Carbon; @endphp
<style>
        :root {
            --primary-color: {{ config('themes.primaryColor') }};
            --secondary-color: {{ config('themes.secondaryColor') }};
            --text-primary-color: {{ config('themes.textPrimaryColor') }};
            --text-secondary-color: {{ config('themes.textSecondaryColor') }};
            --box-background-color: {{ config('themes.boxBackgroundColor') }};
            --table-background-color: {{ config('themes.tableBackGroundColor')}};
            --background-image: {{ config('themes.backgroundImage') }};
            --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
            --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
            --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
            --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;
            --inverse-color: {{getLighterColor(config('themes.primaryColor'))}};
            --inverse-box-color: {{adjustTextColor(config('themes.boxBackgroundColor'))}};
            --success-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
            --primary-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { box-sizing: border-box; }

        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 24px;
        }

        .filter-content {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            margin-left: auto;
        }

        .form-floating {
            position: relative;
        }

        .form-select {
            height: 48px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            padding: 12px 16px;
            font-size: 14px;
            background-color: #f9f9f9;
        }

        .wallet-filter-select {
            padding: 0 !important;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
            background-color: #ffffff;
        }

        .form-label {
            color: #000000;
            transition: all 0.3s ease;
        }

        .btn-filter {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            height: 48px;
        }

        .btn-filter:hover {
            background-color: var(--primary-hover-color);
            transform: translateY(-1px);
        }

        .btn-reset {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            height: 48px;
        }

        .btn-reset:hover {
            background-color: #f1f3f5;
            color: var(--danger-color);
            border-color: var(--danger-light-color);
        }

        @media (max-width: 768px) {
            .filter-content {
                flex-direction: column;
                gap: 16px;
            }

            .filter-group {
                width: 100%;
            }

            .filter-actions {
                width: 100%;
                justify-content: flex-end;
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .filter-actions {
                flex-direction: column;
                gap: 12px;
            }

            .btn-filter, .btn-reset {
                width: 100%;
                justify-content: center;
            }
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 57px;
            color: white;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .stat-icon.bg-blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .stat-icon.bg-green {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
        }

        .stat-label {
            font-size: 13px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .section-box {
            background: var(--secondary-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }


        .section-header h4 {
            margin: 0;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header .text-yellow {
            color: #f39c12;
        }

        .section-header .text-red {
            color: #e74c3c;
        }


        .number-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #ecf0f1;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .number-badge.warning {
            background: #fef9e7;
            color: #f39c12;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 0;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        .empty-table {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            background: white;
            border-radius: 8px;
            margin: 15px 0;
        }

        .empty-table i {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .empty-table p {
            margin: 0;
            font-size: 14px;
        }

        .agency-profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            /* background: var(--secondary-color); */
            /* filter: brightness(0.85); */

        }

        .agency-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .agency-avatar .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .agency-info {
            flex: 1;
        }

        .agency-name {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 28px;
            font-weight: 700;
        }

        .agency-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .meta-label {
            font-weight: 600;
            color: #7f8c8d;
        }

        .meta-value {
            color: #34495e;
        }

        .meta-uuid {
            color: #95a5a6;
            font-size: 0.9em;
        }

        .agency-stats {
            display: flex;
            gap: 15px;
        }

        .stat-card {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            min-width: 200px;

        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #3498db;
        }

        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ltr .btn-back {
            position: absolute;
            top: 5px;
            right: 20px;
            background: #ecf0f1;
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

        .rtl .btn-back {
            position: absolute;
            top: 5px;
            left: 20px;
            background: #ecf0f1;
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

        .notice-section {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 25px;
        }

        .notice-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: #ff9800;
            font-weight: 600;
        }

        .notice-content {
            color: #5d4037;
            line-height: 1.5;
        }

        .top-performers-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .top-performers-section {
                grid-template-columns: 1fr;
            }
        }

        .performers-card {
            background: var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            /* filter: brightness(0.5); */

        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .section-title {
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2c3e50;
        }

        .section-badge {
            background: #3498db;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 15px;
        }

        .avatar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s;
        }

        .avatar-item:hover {
            transform: translateY(-3px);
        }

        .avatar-img-container {
            position: relative;
            width: 60px;
            height: 60px;
            margin-bottom: 8px;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .avatar-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid white;
        }

        .avatar-badge.admin {
            background: #27ae60;
        }

        .avatar-name {
            font-size: 12px;
            text-align: center;
            max-width: 80px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-scroll-container {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .nav-pills {
            display: inline-flex;
            padding: 10px 0;
        }

        .nav-pills li {
            display: inline-block;
        }


        .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
            border-top-color: var(--primary-color);
        }

        .nav-pills > li.active > a, .nav-pills > li.active > a:focus, .nav-pills > li.active > a:hover {
            color: #fff;
            background-color: var(--primary-color);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 0;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        .agency-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;

            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .tab-btn.active {
            color: var(--text-secondary-color);
            background-color: var(--primary-color);
            border-bottom-color: transparent;
            border-radius: 4px;
        }

        .tab-btn:hover:not(.active) {
            border-bottom-color: var(--secondary-color) !important;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card-header h3 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }

        .count-badge {
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--secondary-color);
        }


        .table-section {
            width: 100%;
            border-collapse: collapse;
            background: var(--secondary-color);
        }

        .data-table th {
            /* text-align: left; */
            padding: 12px 15px;
            background: var(--secondary-color);

            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-info strong {
            font-size: 14px;
        }

        .user-info small {
            font-size: 11px;
            color: #95a5a6;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .role-badge.owner {
            background: #9b59b6;
        }

        .role-badge.admin {
            background: #27ae60;
        }

        .btn-action {
            padding: 5px 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-action:hover {
            background: #2980b9;
        }

        .empty-table {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
        }

        .empty-table i {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .empty-table p {
            margin: 0;
            font-size: 14px;
        }

        .user-avatar,
        .supporter-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .supporters-avatars {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .target-card-section-1 {
            /* display: inline-flex; */
            width: 100%;
            padding-top: 26px;
            margin-bottom: 35px;

        }

        .card-target-filter {
            display: inline;
            width: 34%;
            left: 33px;
            position: absolute;
        }

        .card-target-filter .form-group {
            margin-bottom: 16px;
            right: 20px;
            position: relative;
            top: 10px;
        }

        .card-target-filter button {
            position: relative;
            left: -49px;
            bottom: -29px;
        }

        .card-target-filter-phone {
            width: 51%;
            margin-bottom: 27px;
            position: relative;
        }

        .card-target-filter-phone .form-group {
            margin-bottom: 16px;
            right: 20px;
            position: relative;
            top: 10px;
        }

        .card-target-filter-phone button {
            position: relative;
            left: -49px;
            bottom: -29px;
        }

        .target-card-stat {
            width: 50%;
        }

        .filter-form {
            border-radius: 13px;
            height: 165px;

        }

        .card-target-filter-phone {
            /* display: none; */
        }

        .card-target-filter {
            display: none;
        }

        @media (max-width: 768px) {
            .stats-row {
                flex-direction: column;
                width: 108%;

            }

            .avatar-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            }

            .target-card-section-1 {
                display: grid;
                width: 100%;
                padding-top: 26px;
                margin-bottom: 35px;
            }


            .stat-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 118px;
                color: white;
                font-size: 20px;
                margin-bottom: 6px;

            }


            .target-card-stat {
                width: 92%;

            }

            .card-target-filter {
                display: none;
            }

            .card-target-filter-phone {
                display: block;
                width: 100%;
                left: 0px;
                position: relative;
                margin-bottom: 31px;

            }

            .card-target-filter-phone .col-md-7 {
                float: none;
            }

            .card-target-filter-phone .form-control {
                /* display: block; */
                width: 89%;
                padding: 6px 12px;
                font-size: 14px;
                line-height: 1.42857143;
                color: var(--text-secondary-color) !important;
                background-color: #fff;
                background-image: none;
                border: 1px solid var(--primary-hover-alpha) !important;
                border-radius: 4px;
            }

            .card-target-filter-phone .filter-form {
                border-radius: 13px;
                height: 238px;
            }

            .card-target-filter-phone .align-items-end {
                display: grid;
            }

            .card-target-filter-phone button {
                left: -224px;

            }
        }

        .date-flex-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-flex-row span {
            font-weight: 600;
            white-space: nowrap;
        }

        .date-flex-row input[type="date"] {
            flex-basis: 0;
        }

        .diamond-summary-container {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 20px;
        }

        .diamond-summary-box {
            max-width: 600px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-color) 100%);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin: 0 auto; /* This also helps with centering */
        }

        .diamond-summary-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .diamond-title {
            font-size: 22px;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .diamond-count {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .diamond-count span {
            margin-right: 10px;
        }

        .diamond-icon-container {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            padding: 8px;
            display: inline-flex;
        }

        .diamond-icon {
            width: 32px;
            height: 32px;
            filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.5));
        }

        .gift-log-form {
            background-color: transparent !important;
            filter: none !important;
        }

        .level-form {
            background-color: transparent !important;
            filter: none !important;
            padding: 10px;
        }

        .level-label {
            padding: 10px;
        }

        /* .rtl .gift-log-form {
            padding-right: 13%;
        } */
        .ltr .gift-log-form {
            padding-left: 13%;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-top: 1px solid #dee2e6;
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        .card-title {
            color: #333;
            font-weight: 500;
        }

        /* ═══════════════════════════════════════════
           MODERN UI POLISH — Enhanced Styling
           ═══════════════════════════════════════════ */

        /* ── Cover Slideshow ── */
        .profile-cover-wrapper {
            position: relative;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            overflow: visible;
            margin-bottom: 0;
        }

        .profile-cover-slideshow {
            position: relative;
            height: 220px;
            overflow: hidden;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .cover-slide {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            z-index: 1;
        }

        .cover-slide.active {
            opacity: 1;
            z-index: 2;
        }

        .cover-slide::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(transparent, rgba(0,0,0,0.35));
            z-index: 3;
        }

        /* Navigation arrows */
        .cover-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(4px);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s, background 0.3s;
            font-size: 14px;
        }

        .profile-cover-wrapper:hover .cover-nav {
            opacity: 1;
        }

        .cover-nav:hover {
            background: rgba(255,255,255,0.45);
        }

        .cover-prev { left: 14px; }
        .cover-next { right: 14px; }

        /* Dots indicator */
        .cover-dots {
            position: absolute;
            bottom: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 8px;
        }

        .cover-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.45);
            cursor: pointer;
            transition: all 0.3s;
        }

        .cover-dot.active {
            background: #fff;
            transform: scale(1.3);
            box-shadow: 0 0 6px rgba(255,255,255,0.6);
        }

        .profile-avatar-wrapper {
            position: absolute;
            bottom: -50px;
            left: 40px;
            z-index: 10;
        }

        .profile-avatar-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            object-fit: cover;
            background: #fff;
        }

        .profile-info-card {
            background: #fff;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            padding: 20px 40px 24px;
            padding-top: 60px;
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.06);
            border-top: none;
        }

        .profile-info-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }

        .profile-name-section {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .profile-user-name {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .profile-badges-inline {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .profile-actions-top {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .profile-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .profile-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 6px 16px;
            font-size: 13px;
            color: #475569;
        }

        .profile-meta-chip i {
            color: #94a3b8;
            font-size: 12px;
        }

        .meta-sep {
            color: #cbd5e1;
            margin: 0 2px;
        }

        .profile-stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }

        .profile-stat-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius-md);
            padding: 16px 24px;
            min-width: 120px;
            flex: 1;
            text-align: center;
            transition: all 0.2s;
        }

        .profile-stat-box:hover {
            border-color: var(--primary-color);
            background: #f0f7ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .profile-stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
        }

        .profile-stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .profile-badges-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }

        .flag-image {
            height: 18px;
            border-radius: 3px;
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .profile-cover { height: 140px; }
            .profile-avatar-wrapper { left: 50%; transform: translateX(-50%); bottom: -40px; }
            .profile-avatar-img { width: 90px; height: 90px; }
            .profile-info-card { padding: 56px 20px 20px; text-align: center; }
            .profile-info-header { justify-content: center; }
            .profile-name-section { justify-content: center; }
            .profile-actions-top { justify-content: center; }
            .profile-meta-row { justify-content: center; }
            .profile-stats-row { justify-content: center; }
            .profile-stat-box { min-width: 100px; padding: 12px 16px; }
            .profile-badges-row { justify-content: center; }
        }

        /* ── Tabs Navigation ── */
        .agency-tabs {
            background: var(--secondary-color);
            border-radius: var(--radius-md);
            padding: 8px;
            border: none;
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
            gap: 4px;
            scrollbar-width: none;
            color: var(--text-secondary-color);
        }

        .agency-tabs::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            padding: 11px 20px;
            font-size: 13px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: none;
            border-bottom: none;
            color: var(--text-secondary-color);
            opacity: 0.6;
            text-decoration: none;
            transition: all 0.25s;
        }

        .tab-btn:hover:not(.active) {
            opacity: 0.9;
            background: rgba(255,255,255,0.08);
            border-bottom-color: transparent !important;
            text-decoration: none;
            color: var(--text-secondary-color);
        }

        .tab-btn.active {
            opacity: 1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            color: var(--text-primary-color);
            background: var(--primary-color);
        }

        /* ── Cards ── */
        .card {
            border: none;
            border-top: none;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 18px 24px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .card-body {
            padding: 20px 24px;
        }

        /* ══════════════════════════════════════════════════════
           PREMIUM TABLE STYLES — Polished & Professional
           ══════════════════════════════════════════════════════ */
        .data-table,
        .table {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            width: 100%;
            background: #fff !important;
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        /* ── Table Header ── */
        .data-table th,
        .table > thead > tr > th {
            background: linear-gradient(180deg, #f8fafc 0%, #edf1f7 100%) !important;
            color: #3b4a63 !important;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 16px 22px !important;
            border-bottom: 2px solid #dce3ed !important;
            border-top: none !important;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .data-table th:first-child,
        .table > thead > tr > th:first-child {
            border-radius: var(--radius-md) 0 0 0;
        }

        .data-table th:last-child,
        .table > thead > tr > th:last-child {
            border-radius: 0 var(--radius-md) 0 0;
        }

        /* ── Table Cells ── */
        .data-table td,
        .table > tbody > tr > td {
            padding: 16px 22px !important;
            vertical-align: middle;
            border-bottom: 1px solid #edf0f7 !important;
            border-top: none !important;
            color: #334155;
            font-size: 13.5px;
            line-height: 1.7;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        /* ── Row Styles ── */
        .data-table tbody tr,
        .table > tbody > tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            position: relative;
        }

        .data-table tbody tr:nth-child(even),
        .table > tbody > tr:nth-child(even) {
            background: #f8f9fd;
        }

        .data-table tbody tr:hover,
        .table > tbody > tr:hover {
            background: linear-gradient(90deg, #eef1ff 0%, #f3f5ff 40%, #f0f2ff 100%) !important;
            box-shadow: inset 4px 0 0 var(--primary-color, #667eea), 0 2px 8px rgba(99,102,241,0.06);
            z-index: 1;
        }

        .data-table tbody tr:hover td,
        .table > tbody > tr:hover td {
            color: #1e293b;
        }

        .data-table tbody tr:last-child td:first-child {
            border-radius: 0 0 0 var(--radius-md);
        }

        .data-table tbody tr:last-child td:last-child {
            border-radius: 0 0 var(--radius-md) 0;
        }

        /* Override orange text in tbody */
        .data-table tbody,
        .table tbody,
        .data-table tbody tr,
        .table tbody tr,
        .data-table tbody tr td,
        .table tbody tr td,
        .data-table tbody[style] tr td,
        .table tbody[style] tr td {
            color: #334155 !important;
        }

        /* ── First Column (ID/#) ── */
        .data-table td:first-child,
        .table > tbody > tr > td:first-child {
            font-weight: 800;
            font-size: 12px;
        }

        .data-table td:first-child,
        .table > tbody > tr > td:first-child {
            color: #fff;
        }

        .data-table td:first-child::before,
        .table > tbody > tr > td:first-child::before {
            content: '';
            display: inline-block;
        }

        .data-table td:first-child,
        .table > tbody > tr > td:first-child {
            position: relative;
        }

        .data-table td:first-child span,
        .data-table td:first-child {
            color: #6366f1;
        }

        /* ID number badge style */
        .data-table tbody tr td:first-child,
        .table > tbody > tr > td:first-child {
            color: #4f46e5;
            font-variant-numeric: tabular-nums;
            min-width: 50px;
            text-align: center;
        }

        /* ── Table Wrapper ── */
        .table-responsive {
            border-radius: var(--radius-md);
            border: 1px solid #e4e9f2;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 0 1px rgba(0,0,0,0.06);
        }

        /* Table inside cards - remove double borders */
        .card .table-responsive {
            margin: 0;
            border-left: none;
            border-right: none;
            border-radius: 0;
            box-shadow: none;
            border-top: none;
        }

        .card .data-table,
        .card .table {
            margin-bottom: 0;
        }

        /* ── Table Links ── */
        .data-table a,
        .table a {
            color: #4f46e5;
            text-decoration: none;
            transition: all 0.25s;
            font-weight: 600;
            position: relative;
        }

        .data-table a:hover,
        .table a:hover {
            color: #3730a3;
        }

        .data-table a::after,
        .table td a::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: #4f46e5;
            transition: width 0.3s ease;
        }

        .data-table a:hover::after,
        .table td a:hover::after {
            width: 100%;
        }

        /* ── Table Images ── */
        .data-table img,
        .table img {
            border-radius: 10px;
            border: 2px solid #edf0f7;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .data-table img:hover,
        .table img:hover {
            transform: scale(1.12);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            border-color: #c7d2fe;
        }

        /* ── Status Badges ── */
        .table .text-success {
            color: #059669 !important;
            font-weight: 700;
            background: #ecfdf5;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .table .text-danger {
            color: #dc2626 !important;
            font-weight: 700;
            background: #fef2f2;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .table .text-muted {
            color: #94a3b8 !important;
        }

        /* ── Action Buttons in Tables ── */
        .data-table .btn,
        .table .btn {
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            letter-spacing: 0.3px;
            border: none;
        }

        .data-table .btn:hover,
        .table .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .data-table .btn:active,
        .table .btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .data-table .btn-info,
        .table .btn-info {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
        }

        .data-table .btn-info:hover,
        .table .btn-info:hover {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
            box-shadow: 0 6px 20px rgba(99,102,241,0.3);
        }

        .data-table .btn-danger,
        .table .btn-danger {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            color: #fff;
        }

        .data-table .btn-danger:hover,
        .table .btn-danger:hover {
            background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
            box-shadow: 0 6px 20px rgba(244,63,94,0.3);
        }

        .data-table .btn-default,
        .table .btn-default {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .data-table .btn-default:hover,
        .table .btn-default:hover {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            color: #1e293b;
        }

        .data-table .d-flex,
        .table .d-flex {
            gap: 10px;
        }

        /* ── User Cell in Tables ── */
        .data-table .d-flex.align-items-center img,
        .table .d-flex.align-items-center img {
            border-radius: 50% !important;
            border: 2px solid #e0e7ff !important;
        }

        /* ── Pagination Enhancement ── */
        .pagination-wrapper,
        .pagination-container {
            padding: 20px 24px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            gap: 4px;
        }

        .pagination > li > a,
        .pagination > li > span {
            border-radius: 10px !important;
            margin: 0 2px !important;
            border: 1px solid #e4e9f2 !important;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 16px !important;
            transition: all 0.25s;
            color: #475569;
        }

        .pagination > .active > a,
        .pagination > .active > span {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
            color: #fff !important;
        }

        .pagination > li > a:hover {
            background: #eef2ff !important;
            border-color: #c7d2fe !important;
            color: #4f46e5 !important;
            transform: translateY(-1px);
        }

        /* ── Empty Table State ── */
        .data-table tbody tr td[colspan],
        .table tbody tr td[colspan] {
            padding: 48px 24px !important;
            color: #94a3b8;
            font-size: 14px;
            font-style: italic;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        }

        /* ── Buttons ── */
        .btn {
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: all 0.25s;
            font-size: 13px;
        }

        .btn-info {
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .btn-danger {
            box-shadow: 0 2px 6px rgba(220,53,69,0.2);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: 12px;
        }

        /* ── Filter Container ── */
        .filter-container {
            background: rgba(0,0,0,0.015);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: none;
        }

        .form-control {
            border-radius: var(--radius-sm);
            border: 1.5px solid rgba(0,0,0,0.1);
            padding: 8px 14px;
            font-size: 13px;
            transition: all 0.25s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.04);
        }

        .form-select {
            border-radius: var(--radius-sm);
        }

        /* ── Nav Pills (sub-tabs) ── */
        .nav-pills {
            gap: 4px;
            padding: 8px 0;
        }

        .nav-pills > li > a {
            border-radius: var(--radius-sm);
            padding: 8px 18px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }

        .nav-pills > li.active > a {
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        /* ── Pagination ── */
        .pagination-wrapper,
        .pagination-container {
            padding: 16px 24px;
        }

        .pagination > li > a,
        .pagination > li > span {
            border-radius: var(--radius-sm) !important;
            margin: 0 3px !important;
            border: 1px solid rgba(0,0,0,0.08) !important;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 14px !important;
            transition: all 0.2s;
        }

        .pagination > .active > a,
        .pagination > .active > span {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .pagination > li > a:hover {
            background: rgba(0,0,0,0.04) !important;
            transform: translateY(-1px);
        }

        /* ── Diamond Summary ── */
        .diamond-summary-box {
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-md);
        }

        .diamond-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .diamond-count {
            font-size: 28px;
            font-weight: 800;
        }

        /* ── Loading Indicator ── */
        #tab-loading {
            border-radius: var(--radius-md) !important;
            box-shadow: var(--shadow-lg) !important;
            font-size: 16px !important;
            padding: 20px 32px !important;
            transform: translate(-50%, -50%) !important;
        }

        /* ── Modals ── */
        .modal-content {
            border: none !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25) !important;
            overflow: hidden;
        }

        .modal-header {
            padding: 20px 24px !important;
        }

        .modal-body {
            padding: 24px !important;
        }

        .modal-footer {
            padding: 16px 24px !important;
            border-top: 1px solid rgba(0,0,0,0.06) !important;
            gap: 8px;
        }

        /* ── Date Filter Row ── */
        .date-flex-row {
            gap: 10px;
            background: rgba(0,0,0,0.015);
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid rgba(0,0,0,0.06);
        }

        .date-flex-row span {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── Empty States ── */
        .empty-state,
        .empty-table {
            padding: 48px;
            border-radius: var(--radius-md);
        }

        .empty-state i,
        .empty-table i {
            font-size: 48px;
            opacity: 0.4;
            margin-bottom: 16px;
        }

        .empty-state p,
        .empty-table p {
            font-size: 14px;
            opacity: 0.6;
        }

        /* ── Scrollbar ── */
        .table-responsive::-webkit-scrollbar,
        .nav-scroll-container::-webkit-scrollbar {
            height: 4px;
        }

        .table-responsive::-webkit-scrollbar-track,
        .nav-scroll-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .nav-scroll-container::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.12);
            border-radius: 4px;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .agency-header {
                padding: 20px;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .agency-meta {
                justify-content: center;
            }

            .agency-stats {
                flex-direction: column;
                width: 100%;
            }

            .agency-tabs {
                border-radius: var(--radius-sm);
                padding: 4px;
            }

            .tab-btn {
                padding: 8px 14px;
                font-size: 12px;
            }

            .card-header {
                padding: 14px 18px;
            }

            .card-body {
                padding: 16px 18px;
            }

            .data-table th,
            .data-table td,
            .table > thead > tr > th,
            .table > tbody > tr > td {
                padding: 10px 12px !important;
                font-size: 12px;
            }
        }

        /* ── Animations ── */
        .tab-content {
            animation: fadeInTab 0.3s ease;
        }

        @keyframes fadeInTab {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Box Body Spacing ── */
        .box-body {
            padding: 16px;
        }

        .box-body.p-3 {
            padding: 20px !important;
        }
    </style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="..." crossorigin="anonymous"/>
<script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
</head>

<body>
<div class="agency-profile-container" id="pjax-container">
    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="alert alert-danger mx-3 mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->has('msg'))
        <div class="alert alert-danger mx-3 mt-3">
            {{ $errors->first('msg') }}
        </div>
    @endif
    <!-- Header Section - Cover + Profile Card -->
    <div class="profile-cover-wrapper">
        @php
            $coverImages = $covers->count() ? $covers : collect();
        @endphp
        <div class="profile-cover-slideshow">
            @if($coverImages->count() > 0)
                @foreach($coverImages as $i => $cover)
                    <div class="cover-slide {{ $i === 0 ? 'active' : '' }}" style="background-image: url('{{ getImagePath($cover->img) }}');"></div>
                @endforeach
                @if($coverImages->count() > 1)
                    <div class="cover-dots">
                        @foreach($coverImages as $i => $cover)
                            <span class="cover-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></span>
                        @endforeach
                    </div>
                    <button class="cover-nav cover-prev" onclick="coverSlide(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="cover-nav cover-next" onclick="coverSlide(1)"><i class="fas fa-chevron-right"></i></button>
                @endif
            @else
                <div class="cover-slide active" style="background-image: var(--brand_background-image); background-color: var(--secondary-color);"></div>
            @endif
        </div>
        <div class="profile-avatar-wrapper">
            <img src="{{ $user->display_image }}" alt="Avatar" class="profile-avatar-img">
        </div>
    </div>

    <div class="profile-info-card">
        <div class="profile-info-header">
            <div class="profile-name-section">
                <h1 class="profile-user-name">{{ @$user?->name ?? '' }}</h1>
                <div class="profile-badges-inline">{!! @$user->userBadgeTop() !!}</div>
            </div>
            <div class="profile-actions-top">
                <a href="{{ url('admin/users/') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> {{ __('Go Back') }}
                </a>
                @if (\Encore\Admin\Facades\Admin::user()->can('edit-' . 'users') || \Encore\Admin\Facades\Admin::user()->can('*'))
                    <button type="submit" class="btn btn-primary btn-sm edit_user_item_model_btn">
                        <i class="fas fa-edit"></i> {{ __('edit') }}
                    </button>
                @endif
                @if (($user->is_bd == 1) && (\Encore\Admin\Facades\Admin::user()->can('edit-users') || \Encore\Admin\Facades\Admin::user()->can('*')))
                    <button type="button" class="btn btn-danger btn-sm remove-bd-btn"
                            data-id="{{ $user->id }}" data-url="{{ route('users.remove', $user->id) }}">
                        {{ __('Remove BD') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="profile-meta-row">
            <div class="profile-meta-chip">
                <i class="fas fa-id-badge"></i>
                @if (@$user->uuid == @$user->original_uuid)
                    <span>{{ @$user->original_uuid }}</span>
                @else
                    <span>{{ @$user->original_uuid }}</span>
                    <span class="meta-sep">|</span>
                    <span style="opacity:0.7">{{ @$user->uuid_v3 }}</span>
                @endif
            </div>
            <div class="profile-meta-chip">
                <i class="fas fa-phone"></i>
                <span>{{ @$user->phone ?? 'N/A' }}</span>
            </div>
            <div class="profile-meta-chip">
                <img src="{{ getImagePath(@$user->country->flag) }}" class="flag-image" alt="flag"
                     title="{{ app()->getLocale() === 'ar' ? @$user->country->name : @$user->country->e_name }}">
                <span>{{ app()->getLocale() === 'ar' ? @$user->country->name : @$user->country->e_name }}</span>
            </div>
        </div>

        @php
            $user_diamonds = (in_array($user->type_user, [0,3])) ? $user->exchange_diamonds : $user->monthly_diamond_received;
        @endphp

        <div class="profile-stats-row">
            <div class="profile-stat-box">
                <span class="profile-stat-value">{{ @$user->salary }}</span>
                <span class="profile-stat-label">{{ __('Balance') }}</span>
            </div>
            <div class="profile-stat-box">
                <img src="{{ getImagePath(@$user->senderLevel->img) }}" style="height: 28px;">
                <span class="profile-stat-label">{{ __('level') }}</span>
            </div>
            <div class="profile-stat-box">
                <img src="{{ getImagePath(@$user->receiverLevel->img) }}" style="height: 28px;">
                <span class="profile-stat-label">{{ __('Receiver Level') }}</span>
            </div>
            <div class="profile-stat-box">
                <span class="profile-stat-value">{{ @$user_diamonds }}</span>
                <span class="profile-stat-label">{{ __('diamonds') }}</span>
            </div>
            <div class="profile-stat-box">
                <span class="profile-stat-value">{{ @$user->di }}</span>
                <span class="profile-stat-label">{{ __('coins') }}</span>
            </div>
        </div>

        <div class="profile-badges-row">
            {!! @$user->userBadge() !!}
        </div>
    </div>


    @php
        $activeTab = request('tab', 'packs');

    @endphp
        <!-- Navigation Tabs -->
    <div class="agency-tabs">

        <a href="?tab=packs"
           data-pjax
           class="tab-btn {{ $activeTab === 'packs' ? 'active' : '' }}"
           data-target="packs-tab">
            {{ __('packs') }}
        </a>

        <a href="?tab=vips" data-pjax class="tab-btn {{ $activeTab == 'vips' ? 'active' : '' }}"
           data-target="vips-tab">{{ __('vips') }}</a>

        @if (\Encore\Admin\Facades\Admin::user()->can('level-switch-' . 'users') || \Encore\Admin\Facades\Admin::user()->can('*'))
            <a href="?tab=level" data-pjax class="tab-btn" data-target="level-tab">{{ __('level') }}</a>
        @endif
        @if (\Encore\Admin\Facades\Admin::user()->can('salary-switch-' . 'users') || \Encore\Admin\Facades\Admin::user()->can('*'))
            <a href="?tab=salary" data-pjax class="tab-btn {{ $activeTab == 'salary' ? 'active' : '' }}"
               data-target="salary-tab">{{ __('prof_reports') }}</a>
        @endif
        <a href="?tab=charge" data-pjax class="tab-btn {{ $activeTab == 'charge' ? 'active' : '' }}"
           data-target="charge-tab">{{ __('Charge Reports') }}</a>

        <a href="?tab=gift-log" data-pjax class="tab-btn {{ $activeTab == 'gift-log' ? 'active' : '' }}"
           data-target="gift-log-tab">{{ __('gifts') }}</a>
        <a href="?tab=user-agency" data-pjax class="tab-btn {{ request('tab') == 'user-agency' ? 'active' : '' }}"
           data-target="user-agency-tab">{{ __('Agency join logs') }}</a>
        <a href="?tab=user-coins" data-pjax class="tab-btn {{ request('tab') == 'user-coins' ? 'active' : '' }}"
           data-target="user-coins-tab">{{ __('User Coins') }}</a>
        <a href="?tab=badges" data-pjax class="tab-btn {{ $activeTab == 'badges' ? 'active' : '' }}"
           data-target="badges-tab">{{ __('badges') }}</a>

        <a href="?tab=wallet_logs" data-pjax class="tab-btn {{ $activeTab == 'wallet_logs' ? 'active' : '' }}"
           data-target="wallet-logs-tab">  {{ __('wallet-transactions') }} </a>
    </div>
    <div id="tab-loading" style="
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            /* transform: translate(-50%, -50%); */
            background: var(--primary-color);
            color: var(--text-primary-color);
            z-index: 9999;
            padding: 30px 40px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        ">
        {{ __('Loading...') }}
    </div>

    <!-- packs Section -->

    <div class="tab-content {{ $activeTab === 'packs' ? '' : 'd-none' }}" id="packs-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('pack') }}</h4>
            </div>
            <div class="box-body">
                <div class="nav-scroll-container">

                    <ul class="nav nav-pills">
                        @foreach($types as $id => $name)
                            <li class="{{ $type == $id ? 'active' : '' }}">
                                <a href="{{ request()->fullUrlWithQuery(['type' => $id, 'pack_page' => 1]) }}"
                                   class="charge_action">
                                    {{ __($name) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="table-responsive">
                <div class="box-body ">
                    <table class="table table-bordered table-hover align-middle data-table" id="pack">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('created by') }}</th>
                            <th>{{ __('get type') }}</th>
                            <th>{{ __('type') }}</th>
                            <th>{{ __('img') }}</th>
                            <th>{{ __('expire') }}</th>
                            <th>{{ __('receive_type') }}</th>
                            <th>{{ __('action') }}</th>

                        </tr>
                        </thead>
                        @if($packs && $packs->count())

                            <tbody>
                            @foreach($packs as $index => $pack)
                                @php
                                    $path = @$pack->ware?->show_img ?? '';

                                    $admin = null;

                                    if ($pack->vip_user_id && optional($pack->userVip)->admin) {
                                        $admin = $pack->userVip->admin;
                                    } elseif ($pack->dash_user_id && optional($pack)->admin) {
                                        $admin = $pack->admin;
                                    }

                                    $image = optional($admin)->avatar ?? '';
                                    $defaultImage = asset("images/businessman-icon.jpg");
                                    $imagePath = getImagePath($image);
                                    $image = isImageExists($imagePath) ? $imagePath : $defaultImage;

                                    $nameRaw = optional($admin)->name;
                                    $name = is_array($nameRaw) ? reset($nameRaw) : (string) $nameRaw;

                                    $uid = optional($admin)->id ?? 0;
                                    $url = $admin ? url("admin/auth/users/" . $uid) : '#';
                                @endphp
                                <tr>
                                    <td>{{ $packs->firstItem() + $index }}</td>
                                    <td>
                                        @if ($admin && @$pack->receive_type == 'wares-dash-dedicate')
                                            <a href="{{ $url ?? '#' }}" target="_blank"
                                               style="display: inline-flex; align-items: center; text-decoration: none;">
                                                <img src="{{ $image }}" width="30" height="30"
                                                     style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                                <span>{{ $name }} ({{ $uid }})</span>
                                            </a>
                                        @else

                                        @endif

                                    </td>
                                    <td>{{ $pack->getTypeGet() }}</td>

                                    <td>{{ $pack->getType() }}</td>
                                    <td>
                                        <img src="{{ getImagePath(@$path) }}" width="30" height="30"
                                             style="object-fit: cover; border-radius: 50%; margin-right: 10px;">

                                    </td>
                                    <td>{{ (!empty($pack->expire) && $pack->expire !== '0') ? \Carbon\Carbon::parse($pack->expire)->format('Y-m-d H:i:s') :$pack->days  }}</td>
                                    {{-- <td>{{ $pack->receive_type }}</td> --}}
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            {{-- Always show type --}}
                                            <span style="font-weight: 600; color: #444;">
                                                    {{ @$pack->receive_type ?? '' }}
                                                </span>

                                            {{-- If receive_type contains "send", show sender below --}}
                                            @if(@$pack->sender && Str::contains(@$pack->receive_type, 'send'))
                                                @php
                                                    $name = @$pack->sender->name ?? 'Unknown User';
                                                    $showUrl = url("admin/users/" . @$pack->sender->id);
                                                @endphp

                                                <a href="{{ $showUrl }}"
                                                   style="text-decoration: none; color: #007bff; display: inline-block;">
                                                        <span style="font-weight: 600; color: #555; font-size: 0.9rem;">
                                                            sender:
                                                        </span>
                                                    <span
                                                        style="text-decoration: underline; cursor: pointer; font-size: 1.1rem; font-weight: bold;">
                                                            {{ $name }}
                                                        </span>
                                                </a>
                                            @elseif($pack->receive_type == 'wares-dash-dedicate' && $pack->admin)
                                                @php
                                                    $name = @$pack->admin->name ?? 'Unknown User';
                                                    $showUrl = url("admin/auth/users/" . @$pack->admin->id);
                                                @endphp

                                                <a href="{{ $showUrl }}"
                                                   style="text-decoration: none; color: #28a745; display: inline-block;">
                                                    <span style="font-weight: 600; color: #555; font-size: 0.9rem;">
                                                        admin:
                                                    </span>
                                                    <span
                                                        style="text-decoration: underline; cursor: pointer; font-size: 1.1rem; font-weight: bold;">
                                                        {{ $name }}
                                                    </span>
                                                </a>

                                            @endif
                                        </div>
                                    </td>


                                    <td>
                                        <div style="display:inline-flex; gap:6px; align-items:center;">
                                            <button class="btn btn-sm edit_item_model_btn"
                                                    data-id="{{ @$pack->id }}"
                                                    style="background:#eef2ff; color:#4f46e5; border:1px solid #c7d2fe; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s;">
                                                <i class="fa fa-clock-o"></i> {{ __('dashboard.free') }}
                                            </button>
                                            <button class="btn btn-sm delete-btn" data-id="{{ @$pack->id }}"
                                                    style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s;">
                                                <i class="fa fa-trash"></i> {{ __('dashboard.delete') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @endif

                    </table>
                </div>
            </div>

            <div class="pagination-wrapper">
                {{ $packs?->appends([
                     'type'        => $type,
                    'vip_page' => $userVips?->currentPage(),
                    'salary_page' => $salaries?->currentPage(),
                    'gift_page' => $giftSLogs?->currentPage(),
                ])->links('vendor.pagination.default') }}
            </div>


        </div>

    </div>

    <!-- vips Section -->

    <div class="tab-content {{ $activeTab == 'vips' ? '' : 'd-none' }}" id="vips-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('vips') }}</h4>
            </div>

            <div class="table-responsive">
                <div class="box-body ">
                    <table class="table table-bordered table-hover align-middle data-table" id="vip">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('level') }}</th>
                            <th>{{ __('expire') }}</th>
                            <th>{{ __('qty') }}</th>
                            <th>{{ __('total Price') }}</th>
                            <th>{{ __('receive_type') }}</th>
                            <th>{{ __('action') }}</th>

                        </tr>
                        </thead>
                        @if($userVips && $userVips->count())
                            <tbody>
                            @foreach($userVips as $index => $userVip)
                                <tr>
                                    <td>{{ $index + 1 + (($userVips->currentPage() - 1) * $userVips->perPage()) }}</td>
                                    <td>{{ $userVip->level }}</td>
                                    <td>
                                        {{
                                            (!empty($userVip->expire) && $userVip->expire != '0')
                                                ? \Carbon\Carbon::parse($userVip->expire)->format('Y-m-d H:i:s')
                                                : $userVip->days
                                        }}
                                    </td>
                                    <td>{{ @$userVip->qty ?? 0 }}</td>
                                    <td>{{ @$userVip->total ?? 0 }}</td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            {{-- Always show type --}}
                                            <span style="font-weight: 600; color: #444;">
                                                    {{ @$userVip->receive_type ?? '' }}
                                                </span>

                                            {{-- If send-vip, show sender below --}}
                                            @if(@$userVip->receive_type === 'send-vip' && @$userVip->sender)
                                                @php
                                                    $name = @$userVip->sender->name ?? 'Unknown User';
                                                    $showUrl = url("admin/users/" . @$userVip->sender->id);
                                                @endphp

                                                <a href="{{ $showUrl }}"
                                                   style="text-decoration: none; color: #007bff; display: inline-block;">
                                                        <span style="font-weight: 600; color: #555; font-size: 0.9rem;">
                                                            sender:
                                                        </span>
                                                    <span
                                                        style="text-decoration: underline; cursor: pointer; font-size: 1.1rem; font-weight: bold;">
                                                            {{ $name }}
                                                        </span>
                                                </a>
                                            @endif
                                            @if(@$userVip->receive_type === 'admin-dedicate' && @$userVip->admin)
                                                @php
                                                    $name = @$userVip->admin->name ?? 'Unknown Admin';
                                                    $showUrl = url("admin/auth/users/" . @$userVip->admin->id);
                                                @endphp

                                                <a href="{{ $showUrl }}"
                                                   style="text-decoration: none; color: #007bff; display: inline-block;">
                                                        <span style="font-weight: 600; color: #555; font-size: 0.9rem;">
                                                            sender:
                                                        </span>
                                                    <span
                                                        style="text-decoration: underline; cursor: pointer; font-size: 1.1rem; font-weight: bold;">
                                                            {{ $name }}
                                                        </span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <button class="btn btn-sm delete-vip-btn" data-id="{{ @$userVip->id }}"
                                                style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s;">
                                            <i class="fa fa-trash"></i> {{ __('dashboard.delete') }}
                                        </button>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        @endif
                    </table>

                    @if($userVips)
                        <div class="pagination-container">
                            {{ $userVips->appends([
                                'tab' => 'vips',
                                'vip_page' => $userVips->currentPage(),
                            ])->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>

    <div class="tab-content" id="salary-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('user wallet') }}</h4>
            </div>

            <div class="box-body p-3">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ url('admin/users/' . $user->id) }}"
                                  class="form-horizontal" pjax-container="">
                                <input type="hidden" name="tab" value="salary">
                                <div class="filter-container">
                                    <div class="filter-content">
                                        <div class="filter-group">
                                            <label class="form-label">{{ __('year') }}</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <select class="form-control form-select year" name="year">
                                                    <option value="">{{ __('Select Year') }}</option>
                                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="filter-group">
                                            <label class="form-label">{{ __('month') }}</label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                <select class="form-control form-select month" name="month">
                                                    <option value="">{{ __('Select Month') }}</option>
                                                    @for($m = 1; $m <= 12; $m++)
                                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }} - {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-info submit btn-sm">
                                                <i class="fa fa-search"></i>&nbsp;&nbsp;{{__('Search')}}
                                            </button>
                                            <a href="{{ url('admin/users/' . $user->id. '?'.'tab=salary') }}"
                                               class="btn btn-default btn-sm">
                                                <i class="fa fa-undo"></i>&nbsp;&nbsp;{{__('Reset')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                <div class="table-responsive">
                    <div class="box-body ">
                        <table class="table table-bordered table-hover align-middle data-table" id="vip">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('agency') }}</th>
                                <th>{{ __('salary') }}</th>
                                <th>{{ __('Withdraw') }}</th>
                                <th>{{ __('net salary') }}</th>
                                <th>{{ __('days') }}</th>
                                <th>{{ __('hours') }}</th>
                                <th>{{ __('Moments') }}</th>
                                <th>{{ __('Reels') }}</th>
                                <th>{{ __('diamonds') }}</th>
                                <th>{{ __('date') }}</th>

                            </tr>
                            </thead>
                            @if($salaries && $salaries->count())
                                <tbody>
                                @foreach($salaries as $index => $salary)

                                    @php
                                        $agency = $salary->agency;
                                        $name = $agency->name ?? '';

                                        $path = @$agency->img;
                                        $defaultImage = asset("images/icon-agency.jpg");
                                        $url = getImagePath($path) ?? $defaultImage;

                                        if (!isImageExists($url)) {
                                            $url = $defaultImage;
                                        }

                                        $image = handleShowImageWithTypes($user->id, $url, 40, 40);
                                        $profileUrl = route('admin.agency.profile', ['id' => @$agency->id ?? 0]);

                                        $extras = json_decode($salary->extras, true);
                                        $moment = $extras['moment'] ?? [];
                                        $reel = $extras['reel'] ?? [];

                                        $momentUpload = $moment['upload'] ?? '0/0';
                                        $momentLikes = $moment['likes'] ?? '0/0';
                                        $momentComments = $moment['comments'] ?? '0/0';

                                        $reelUpload = $reel['upload'] ?? '0/0';
                                        $reelLikes = $reel['likes'] ?? '0/0';
                                        $reelComments = $reel['comments'] ?? '0/0';


                                    @endphp

                                    <tr>
                                        <td>{{ $index + 1 + (($salaries->currentPage() - 1) * $salaries->perPage()) }}</td>
                                        <td>
                                            <a href="{{ $profileUrl }}" style="text-decoration: none; color: inherit;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    {!! $image !!}
                                                    <div style="display: flex; flex-direction: column;">
                                                    <span
                                                        style="text-decoration: underline; cursor: pointer;">{{ $name }}</span>
                                                        <span
                                                            style="font-size: smaller;">ID: {{ @$agency->id ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>


                                        <td>{{truncateAndTrim($salary->sallary)}}</td>
                                        <td>{{ $salary->cut_amount}}</td>
                                        <td>{{ truncateAndTrim($salary->sallary - $salary->cut_amount) }}</td>
                                        <td>{{ $salary->days }}</td>
                                        <td>{{ $salary->hours }}</td>
                                        <td>
                                            <div style="line-height: 1.6;">
                                                <ul style="margin-left: 8px;">
                                                    <li><b>{{ __('Uploads:') }}</b> {{ $momentUpload }}</li>
                                                    <li><b>{{ __('Likes:') }}</b> {{ $momentLikes }}</li>
                                                    <li><b>{{ __('Comments:') }}</b> {{ $momentComments }}</li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="line-height: 1.6;">
                                                <ul style="margin-left: 8px;">
                                                    <li><b>{{ __('Uploads:') }}</b> {{ $reelUpload }}</li>
                                                    <li><b>{{ __('Likes:') }}</b> {{ $reelLikes }}</li>
                                                    <li><b>{{ __('Comments:') }}</b> {{ $reelComments }}</li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ $salary->achieved_diamond }}</td>
                                        <td>{{ $salary->month .'/'. $salary->year }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            @endif
                        </table>

                        @if($salaries)
                            <div class="pagination-container">
                                {{ $salaries->appends([
                                    'tab' => 'salary',
                                    'year' => request('year'),  // preserve filters
                                    'month' => request('month'),
                                ])->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tab-content" id="level-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-left">{{ __('level') }}</h4>
            </div>
            <div class="box-body p-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ url('/admin/edit-level') }}" id="user_level_update_form" method="POST"
                              enctype="multipart/form-data" class="level-form">
                            @csrf
                            <div class="row" style="justify-content:space-evenly">
                                <input type="hidden" name="id" class="item_id" value="{{ $user->id }}">
                                <div class=" col-lg-6 form-Roles mb-3">
                                    <label class="form-label level-label"> {{ __('Sender Level') }}</label>
                                    <input type="number" min="0" value="{{ $user->total_sender_level }}"
                                           class="form-control "
                                           id="total_sender_level" name="total_sender_level" required>
                                </div>

                                <div class=" col-lg-6 form-Roles mb-3">
                                    <label class="form-label level-label"> {{ __('Received Level') }}</label>
                                    <input type="number" min="0" value="{{ $user->total_received_level }}"
                                           class="form-control "
                                           id="total_received_level" name="total_received_level" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" type="button"
                                        data-bs-dismiss="modal">{{ __('cancel') }} </button>
                                <button class="btn btn-primary " type="submit">{{ __('save') }} </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content {{ $activeTab == 'wallet_logs' ? 'active show' : 'd-none' }}" id="wallet-logs-tab">
        <div class="box-body">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">
                    {{ __('wallet-transactions') }}
                </h4>
            </div>


            <div class="card">

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ url('admin/users/' . $user->id) }}"
                              class="form-horizontal gift-log-form" pjax-container="">
                            <input type="hidden" name="tab" value="wallet_logs">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="box-body">
                                        <div class="fields-group">

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">{{__("year")}}</label>
                                                <div class="col-sm-8">
                                                    <div class="input-group input-group-sm">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                        <select class="form-control year wallet-filter-select" name="year">
                                                            <option value="">{{ __('Select Year') }}</option>
                                                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box-body">
                                        <div class="fields-group">

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">{{__('month')}}</label>
                                                <div class="col-sm-8">
                                                    <div class="input-group input-group-sm">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                        <select class="form-control month wallet-filter-select" name="month">
                                                            <option value="">{{ __('Select Month') }}</option>
                                                            @for($m = 1; $m <= 12; $m++)
                                                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }} - {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- /.box-body -->
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-8">
                                            <div class="btn-group pull-left">
                                                <button class="btn btn-info submit btn-sm">
                                                    <i class="fa fa-search"></i>&nbsp;&nbsp;{{__('Search')}}
                                                </button>
                                            </div>
                                            <div class="btn-group pull-left" style="margin-left: 10px;">
                                                <a href="{{ url('admin/users/' . $user->id. '?'.'tab=wallet_logs') }}"
                                                   class="btn btn-default btn-sm">
                                                    <i class="fa fa-undo"></i>&nbsp;&nbsp;{{__('Reset')}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box-body">


                    <table class="table table-bordered table-hover align-middle data-table" id="walletLogs">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Type') }}</th>
                            <!-- <th>{{ __('Type') }}</th> -->
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('amount before') }}</th>
                            <th>{{ __('amount after') }}</th>
                            <th>{{ __('Created at') }}</th>
                        </tr>
                        </thead>

                        @if($walletLogs && $walletLogs->count())
                            <tbody>
                            @foreach($walletLogs as $index => $log)
                                <tr>
                                    <td>{{ $walletLogs->firstItem() + $index }}</td>
                                    <td>{{ __("wallet." . $log->operation) }}</td>

                                    <!-- <td>{{ $log->type }}</td> -->
                                    <td>{{ number_format($log->amount, 2) }}</td>
                                    <td>{{ number_format($log->before_amount, 2) }}</td>
                                    <td>{{ number_format($log->after_amount, 2) }}</td>
                                    <td>{{ $log->created_at }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        @endif

                    </table>
                </div>
            </div>

            <div class="pagination-wrapper">
                {{ $walletLogs?->appends([
                    'tab'         => 'wallet_logs',
                    'wallet_logs_page' => $walletLogs?->currentPage(),
                ])->links('vendor.pagination.default') }}
            </div>

        </div>

    </div>

    <div class="tab-content {{ $activeTab == 'badges' ? 'active show' : 'd-none' }}" id="badges-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('badges') }}</h4>
            </div>


            <div class="table-responsive">
                <div class="box-body ">
                    <table class="table table-bordered table-hover align-middle data-table" id="badge">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('created by') }}</th>
                            <th>{{ __('expire') }}</th>
                            <th>{{ __('receive_type') }}</th>
                            <th>{{ __('created_at') }}</th>
                            <th>{{ __('action') }}</th>

                        </tr>
                        </thead>
                        @if($badges && $badges->count())

                            <tbody>
                            @foreach($badges as $index => $badge)
                                @php
                                    $admin = $badge->admin;

                                $image = $admin->avatar ?? '';
                                $defaultImage = asset("images/businessman-icon.jpg");
                                $imagePath = getImagePath($image);
                                $image = isImageExists($imagePath) ? $imagePath : $defaultImage;

                                $nameRaw = optional($admin)->name ?? @$admin->username;
                                $name = is_array($nameRaw) ? reset($nameRaw) : (string) $nameRaw;

                                $uid = optional($admin)->id ?? 0;
                                $url = $admin ? url("admin/auth/users/" . $uid) : '#';
                                @endphp
                                <tr>
                                    <td>{{ $badges->firstItem() + $index }}</td>
                                    <td>
                                        @if ($admin )
                                            <a href="{{ $url ?? '#' }}" target="_blank"
                                               style="display: inline-flex; align-items: center; text-decoration: none;">
                                                <img src="{{ $image }}" width="30" height="30"
                                                     style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                                <span>{{ $name }} ({{ $uid }})</span>
                                            </a>
                                        @else

                                        @endif

                                    </td>
                                    <td>{{ (!empty($badge->expire) && $badge->expire !== '0') ? \Carbon\Carbon::parse($badge->expire)->format('Y-m-d H:i:s') :$badge->days  }}</td>


                                    <td>{{ $badge->receive_type }}</td>
                                    <td>{{ $badge->created_at }}</td>
                                    <td>
                                        @if (($badge->expire == 0) || ($badge->expire >= now()->timestamp) )
                                            <button class="btn btn-sm delete-badge-btn"
                                                    data-id="{{ @$badge->id }}"
                                                    style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px; padding:5px 12px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.2s;">
                                                <i class="fa fa-trash"></i> {{ __('dashboard.delete') }}
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @endif

                    </table>
                </div>
            </div>

            <div class="pagination-wrapper">
                {{ $badges?->appends([
                    'tab'         => 'badges',
                     'type'        => $type,
                    'badges_page' => $badges?->currentPage(),
                ])->links('vendor.pagination.default') }}
            </div>


        </div>

    </div>

    <div class="tab-content" id="user-agency-tab"
         style="{{ request('tab') == 'user-agency' ? 'display: block;' : 'display: none;' }}">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('Agency join logs') }}</h4>
            </div>
            <div class="box-body p-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ url('admin/users/' . $user->id) }}" class="form-horizontal user-agency-form"
                              method="GET" pjax-container>
                            <input type="hidden" name="tab" value="user-agency">

                            <input type="hidden" name="user_agency_page"
                                   value="{{ request()->get('user_agency_page', 1) }}">

                            <div class="row mb-3" style="align-items: flex-end;">
                                <!-- From Date -->
                                <div class="col-md-4">
                                    <div class="date-flex-row">
                                        <i class="fa fa-calendar"></i>
                                        <span>{{ __('Join date') }}</span>
                                        <input type="date" class="form-control" id="from_date" name="join_date"
                                               value="{{ request('join_date') }}">
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-4 d-flex align-items-end justify-content-end" style="gap: 8px;">
                                    <button type="submit" class="btn btn-info btn-sm me-2">
                                        <i class="fa fa-search"></i> {{__('Search')}}
                                    </button>
                                    <a href="{{ url('admin/users/' . $user->id. '?tab=user-agency') }}"
                                       class="btn btn-default btn-sm">
                                        <i class="fa fa-undo"></i> {{__('Reset')}}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <div class="box-body ">
                        <table class="table table-bordered table-hover align-middle data-table" id="user-agency">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('agency') }}</th>
                                <th>{{ __('status') }}</th>
                                <th>{{ __('kicked By') }}</th>
                                <th>{{ __('kicked By status') }}</th>
                                <th>{{ __('Join date') }}</th>
                                <th>{{ __('Leave date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($userJoinAgencies && $userJoinAgencies->count())
                                @foreach($userJoinAgencies as $index => $userJoinAgency)
                                    @php
                                        $kickedBy = null;
                                        $kickedByName = '';
                                        $kickedByUuid = '';
                                        $kickedByImage = '';
                                        $kickedByUrl = '';
                                        $status = '';
                                    @endphp

                                    @php
                                        $agency = $userJoinAgency->agency;
                                        $name = $agency->name ?? '';
                                        $path = @$agency->img;
                                        $defaultImage = asset("images/icon-agency.jpg");
                                        $url = getImagePath($path) ?? $defaultImage;
                                        if (!isImageExists($url)) {
                                            $url = $defaultImage;
                                        }
                                        $image = "<img src='{$url}' width='40' height='40' style='object-fit: cover; border-radius: 6px;'>";
                                        $profileUrl = route('admin.agency.profile', ['id' => @$agency->id ?? 0]);
                                    @endphp

                                    @php
                                        if ($userJoinAgency->status == 'kick off'){
                                            if ($userJoinAgency->kicked_by_app){
                                             $status = 'app';
                                             $kickedBy = $userJoinAgency['kickedByApp'];
                                             $kickedByName = $kickedBy->name ?? '';
                                             $kickedByUuid = $kickedBy->uuid ?? '';
                                             $kickedByPath = @$kickedBy->profile?->avatar;
                                             $defaultImage = asset("images/businessman-icon.jpg");
                                             $url = getImagePath($kickedByPath) ?? $defaultImage;
                                             if (!isImageExists($url)) {
                                                 $url = $defaultImage;
                                             }
                                             $kickedByImage = "<img src='{$url}' width='40' height='40' style='object-fit: cover; border-radius: 6px;'>";
                                             $kickedByUrl = url("admin/users/" . ($kickedBy->id) ?? 0);
                                         }

                                         if ($userJoinAgency->kicked_by_admin){
                                             $status = 'admin';
                                             $kickedBy = $userJoinAgency['kickedByAdmin'];
                                             $kickedByName = $kickedBy->name ?? '';
                                             $kickedByUuid = $kickedBy->id ?? '';
                                             $kickedByPath = @$kickedBy?->avatar;
                                             $defaultImage = asset("images/businessman-icon.jpg");
                                             $url = getImagePath($kickedByPath) ?? $defaultImage;
                                             if (!isImageExists($url)) {
                                                 $url = $defaultImage;
                                             }
                                             $kickedByImage = "<img src='{$url}' width='40' height='40' style='object-fit: cover; border-radius: 6px;'>";
                                             $kickedByUrl = url("admin/auth/users/".($kickedBy->id ?? 0));
                                         }
                                     }
                                    @endphp

                                    <tr>
                                        <td>{{ $index + 1 + (($userJoinAgencies->currentPage() - 1) * $userJoinAgencies->perPage()) }}</td>
                                        <td>
                                            <a href="{{ $profileUrl }}" style="text-decoration: none; color: inherit;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    {!! $image !!}
                                                    <div style="display: flex; flex-direction: column;">
                                                        <span
                                                            style="text-decoration: underline; cursor: pointer;">{{ $name }}</span>
                                                        <span
                                                            style="font-size: smaller;">ID: {{ @$agency->id ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>{{ $userJoinAgency->status }}</td>
                                        <td>
                                            @if(!empty($kickedBy) && !empty($kickedBy->id))
                                                <a href="{{ $kickedByUrl ?? '#' }}" target="_blank"
                                                   style="display: inline-flex; align-items: center; text-decoration: none;">
                                                    {!! $kickedByImage !!}
                                                    <span>{{ $kickedByName }} ({{ $kickedByUuid }})</span>
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ @$status }}</td>
                                        <td>{{ $userJoinAgency->join_date }}</td>
                                        <td>{{ $userJoinAgency->leave_date }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('No agency join logs found') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>

                        @if($userJoinAgencies && $userJoinAgencies->count())
                            <div class="pagination-container">
                                {{ $userJoinAgencies->appends([
                                    'tab' => 'user-agency',
                                    'user_agency_page' => $userJoinAgencies?->currentPage(),
                                ])->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($activeTab == 'user-coins')

    <div class="tab-content" id="user-coins-tab"
         style="{{ request('tab') == 'user-coins' ? 'display: block;' : 'display: none;' }}">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="text-align: left;">{{ __('Users Coins Logs') }}</h4>
            </div>
            <div class="box-body p-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ url('admin/users/' . $user->id) }}" class="form-horizontal user-agency-form"
                              method="GET" pjax-container>
                            <input type="hidden" name="tab" value="user-coins">
                            <input type="hidden" name="coins_page" value="{{ request()->get('coins_page', 1) }}">

                            <div class="row mb-3" style="align-items: flex-end;">
                                <!-- From Date -->
                                <div class="col-md-3">
                                    <label>{{ __('From Date') }}</label>
                                    <input type="date" class="form-control" name="from_date"
                                           value="{{ request('from_date') }}">
                                </div>

                                <!-- To Date -->
                                <div class="col-md-3">
                                    <label>{{ __('To Date') }}</label>
                                    <input type="date" class="form-control" name="to_date"
                                           value="{{ request('to_date') }}">
                                </div>

                                <!-- Sub Type -->
                                <div class="col-md-3">
                                    <label>{{ __('Sub Type') }}</label>
                                    <select name="sub_type" class="form-control">
                                        <option value="">{{ __('All') }}</option>
                                        @foreach(\App\Helpers\Common::getCoinSubTypes() as $type)
                                            <option
                                                value="{{ $type }}" {{ request('sub_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Search/Reset Buttons -->
                                <div class="col-md-3 d-flex align-items-end justify-content-end"
                                     style="gap: 8px;top: 23px;">
                                    <button type="submit" class="btn btn-info btn-sm me-2">
                                        <i class="fa fa-search"></i> {{ __('Search') }}
                                    </button>
                                    <a href="{{ url('admin/users/' . $user->id. '?tab=user-coins') }}"
                                       class="btn btn-default btn-sm">
                                        <i class="fa fa-undo"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <div class="box-body ">
                        <table class="table table-bordered table-hover align-middle data-table" id="vip">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('type') }}</th>
                                <th>{{ __('sub type') }}</th>
                                <th>{{ __('Item Name') }}</th>
                                <th>{{ __('balance before') }}</th>
                                <th>{{ __('amount') }}</th>
                                <th>{{ __('balance yet') }}</th>
                                <th>{{ __('from date') }}</th>
                                <th>{{ __('to date') }}</th>
                                <!-- <th>{{ __('action') }}</th> -->
                            </tr>
                            </thead>
                            @if($usersCoins && $usersCoins->count())
                                <tbody>
                                @foreach($usersCoins as $index => $coin)
                                    <tr>
                                        <td>{{ ($usersCoins->currentPage() - 1) * $usersCoins->perPage() + $index + 1 }}</td>
                                        <td>{{ $coin->type }}</td>
                                        <td>{{ @$coin->sub_type ?? 0 }}</td>
                                        <td>{{ @$coin->item_name ?? '' }}</td>
                                        <td>{{ @$coin->amount_before ?? 0 }}</td>
                                        <td class="{{ ($coin->amount ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $coin->amount ?? 0 }}
                                            @if($coin->sub_type == 'coin_game_users')
                                                <br>
                                                <small class="d-block text-muted text-success">
                                                    {{  $coin->helper_amount }} {{  __('profit') }}
                                                </small>
                                                <br>
                                                <small class="d-block text-muted text-danger">
                                                    {{ $coin->amount  - $coin->helper_amount }}  {{  __('loss')}}
                                                </small>
                                            @endif
                                        </td>
                                        <td>

                                            {{ ($coin->amount_before ?? 0) + ($coin->amount ?? 0)}}


                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($coin->from_date)->format('Y-m-d H:i:s') ?? '0' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($coin->to_date)->format('Y-m-d H:i:s') ?? '0' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <!-- <button class="btn btn-danger delete-coins-log-btn" data-id="{{ @$coin->id }}">
                                                {{ __('dashboard.delete') }}
                                                </button> -->
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            @endif
                        </table>

                        @if($usersCoins)
                            <div class="pagination-container">
                                {{ $usersCoins->appends([
                                    'tab' => 'user-coins',
                                    'pack_page' => $packs?->currentPage(),
                                    'salary_page' => $salaries?->currentPage(),
                                    'gift_page' => $giftSLogs?->currentPage(),
                                    'coins_page' => $usersCoins?->currentPage(),
                                ])->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($activeTab == 'charge')
    <div class="tab-content active" id="charge-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Charge Reports') }}</h4>
            </div>

            <div class="box-body">
                <div class="nav-scroll-container">
                    <ul class="nav nav-pills">

                        <li class="{{ $chargeTabType == 'receiver' ? 'active' : '' }}">
                            <a class="nav-link @if($chargeTabType == 'receiver') active @endif"
                               href="?tab=charge&type=receiver"
                               role="tab">
                                {{ __('Receiver') }}
                            </a>
                        </li>
                        <li class="{{ $chargeTabType == 'charger' ? 'active' : '' }}">
                            <a class="nav-link @if($chargeTabType == 'charger') active @endif"
                               href="?tab=charge&type=charger"
                               role="tab">
                                {{ __('Charger') }}
                            </a>
                        </li>

                    </ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>
                                @if($chargeTabType == 'receiver')
                                    {{ __('Charger') }}
                                @else
                                    {{ __('Receiver') }}
                                @endif
                            </th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('usd') }}</th>
                            <th>{{ __('Created at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($charges as $index => $charge)
                            @php
                                if($chargeTabType == 'receiver') {
                                    $userCharges = Common::getChargerInfo($charge);
                                  } else {
                                    $userCharges = Common::getReceiverInfo($charge);
                                  }
                                  $name = $userCharges['name'] ?? '-';
                                  $uid = $userCharges['uuid'] ?? '-';
                                  $type = $userCharges['type'] ?? '-';
                                  $image = $userCharges['image'] ?? asset('images/businessman-icon.jpg');
                            @endphp
                            <tr>
                                <td>{{ @$charge->id ?? 0 }}</td>
                                <td>
                                    <a href="{{ $userCharges['url'] ?? '#' }}" target="_blank"
                                       style="display: inline-flex; align-items: center; text-decoration: none;">
                                        <img src="{{ getImagePath( $image) }}" width="30" height="30"
                                             style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                        <span>{{ $name }} ({{ $uid }})</span>
                                    </a>
                                </td>
                                <td>{{ $type }} </td>
                                <td>{{ $charge->amount }} </td>
                                <td>{{ $formattedUsd = number_format((float)$charge->usd, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($charge->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Pagination --}}
            @if($charges instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="pagination-container mt-3">
                    {{ $charges->appends([
                        'tab' => 'charge',
                        'type' => $chargeTabType,
                        'receiver_page' => request('receiver_page'),
                        'charger_page' => request('charger_page'),
                        // other tabs' pages if needed
                    ])->links('vendor.pagination.bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
@endif

@if($activeTab == 'gift-log')
    <div class="tab-content active" id="gift-log-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('gift Reports') }}</h4>
            </div>

            <div class="box-body p-3">
                <div class="nav-scroll-container mb-3">
                    <ul class="nav nav-pills">
                        <li class="{{ $giftType == 'receiver' ? 'active' : '' }}">
                            <a class="nav-link @if($giftType == 'receiver') active @endif"
                               href="?tab=gift-log&gift_type=receiver"
                               role="tab">
                                {{ __('received gift') }}
                            </a>
                        </li>
                        <li class="{{ $giftType == 'sender' ? 'active' : '' }}">
                            <a class="nav-link @if($giftType == 'sender') active @endif"
                               href="?tab=gift-log&gift_type=sender"
                               role="tab">
                                {{ __('sent gift') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form action="{{ url('admin/users/' . $user->id) }}" class="form-horizontal gift-log-form"
                              method="GET" pjax-container>
                            <input type="hidden" name="tab" value="gift-log">
                            <input type="hidden" name="gift_type" value="{{ $giftType }}">
                            <input type="hidden" name="gift_page" value="{{ request()->get('gift_page', 1) }}">

                            <div class="row mb-2" style="align-items: flex-end;">
                                <!-- From Date -->
                                <div class="col-md-4">
                                    <div class="date-flex-row">
                                        <i class="fa fa-calendar"></i>
                                        <span>{{ __('From Date') }}</span>
                                        <input type="date" class="form-control" id="from_date" name="start_at"
                                               value="{{ request('start_at') }}">
                                    </div>
                                </div>
                                <!-- To Date -->
                                <div class="col-md-4">
                                    <div class="date-flex-row">
                                        <i class="fa fa-calendar"></i>
                                        <span>{{ __('To Date') }}</span>
                                        <input type="date" class="form-control" id="to_date" name="end_at"
                                               value="{{ request('end_at') }}">
                                    </div>
                                </div>
                                @if ($giftType == 'receiver')
                                    <div class="col-md-4">
                                        <div class="date-flex-row">
                                            <label for="agency_id">{{ __('Agency') }}</label>
                                            <select class="form-control" id="agency_id" name="agency_id">
                                                @if(request('agency_id'))
                                                    <option value="{{ request('agency_id') }}" selected>
                                                        {{ \App\Models\Agency::find(request('agency_id'))?->name . ' - ' . request('agency_id') }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <!-- Buttons -->
                                <div class="col-md-4 d-flex align-items-end justify-content-end" style="gap: 8px;">
                                    <button type="submit" class="btn btn-info btn-sm me-2">
                                        <i class="fa fa-search"></i> {{__('Search')}}
                                    </button>
                                    <a href="{{ url('admin/users/' . $user->id. '?'.'tab=gift-log&gift_type=' . $giftType) }}"
                                       class="btn btn-default btn-sm">
                                        <i class="fa fa-undo"></i> {{__('Reset')}}
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <!-- Summary Box -->
                <div class="diamond-summary-container">
                    <div class="diamond-summary-box">
                        <div class="diamond-title">
                            {{ $giftType == 'receiver' ? __('total diamonds received') : __('total diamonds sent') }}
                        </div>
                        <div class="diamond-count">
                            <span>{{ number_format(@$diamonds) }}</span>
                            <div class="diamond-icon-container">
                                <img src="{{ asset('images/diamond.jpg') }}" alt="Diamond" class="diamond-icon">
                            </div>
                        </div>
                    </div>
                </div>

                @if($giftType == 'sender')
                <!-- Sender Gift Summary Cards - 2 per row -->
                <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; padding: 20px;">

                    <!-- Total Diamond Send from Users Table Card -->
                    {{-- <div style="flex: 1 1 calc(50% - 20px); min-width: 280px; max-width: 500px;">
                        <div class="diamond-summary-box" style="background: linear-gradient(90deg, #e74c3c 0%, #c0392b 100%); margin: 0;">
                            <div class="diamond-title">
                                {{ __('Total Diamond Send') }}
                            </div>
                            <div class="diamond-count">
                                <span>{{ number_format(@$user->total_diamond_send ?? 0) }}</span>
                                <div class="diamond-icon-container">
                                    <img src="{{ asset('images/diamond.jpg') }}" alt="Diamond" class="diamond-icon">
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div style="flex: 1 1 calc(50% - 20px); min-width: 280px; max-width: 500px;">
                        <div class="diamond-summary-box" style="background: linear-gradient(90deg, #27ae60 0%, #229954 100%); margin: 0;">
                            <div class="diamond-title">
                                {{ __('total_diamonds_sent') }}
                            </div>
                            <div class="diamond-count">
                                <span>{{ number_format(@$totalGiftCoins ?? 0) }}</span>
                                <div class="diamond-icon-container">
                                    <img src="{{ asset('images/diamond.jpg') }}" alt="Diamond" class="diamond-icon">
                                </div>
                            </div>
                        </div>
                    </div>

                  

                </div>
                @endif
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ $giftType == 'receiver' ? __('Sender') : __('Receiver') }}</th>
                            <th>{{ __('room') }}</th>
                            <th>{{ __('moment') }}</th>
                            <th>{{ __('gift') }}</th>
                            @if($giftType == 'receiver')
                                <th>{{ __('agency') }}</th>
                            @endif
                            <th>{{ __('quantity') }}</th>
                            <th>{{ __('price') }}</th>
                            <th>{{ __('Created at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($giftSLogs as $index => $giftSLog)
                            @php
                                $userImageDefault = asset('images/businessman-icon.jpg');
                                $defaultImage = asset("images/background_room.jpg");

                                $userCharges = $giftType === 'receiver' ? $giftSLog->sender : $giftSLog->receiver;
                                $name = @$userCharges->name ?? '';
                                $uid = @$userCharges->uuid ?? '';
                                $id = @$userCharges->id ?? 0;

                                $avatar = @$userCharges->profile->avatar;
                                $image = getImagePath($avatar) ?? $userImageDefault;
                                if (!isImageExists($image)) {
                                    $image = $userImageDefault;
                                }

                                $roomName = @$giftSLog->room->room_name ?? '-';
                                $path = @$giftSLog->room->room_cover;
                                $ownerRoom = @$giftSLog->room->uid ?? 0;
                                $url = getImagePath($path) ?? $defaultImage;
                                if (!isImageExists($url)) {
                                    $url = $defaultImage;
                                }

                                $giftName = app()->getLocale() == 'ar'
                                    ? (@$giftSLog->gift->name ?? '')
                                    : (@$giftSLog->gift->e_name ?? '');

                                    $agency =$giftSLog->agency;
                                    $agencyName = $agency->name ?? '';
                                    $agencyId = $agency->id ?? 0;
                                    $agencyDefaultImage = asset("images/icon-agency.jpg");
                                    $agencyImage =getImagePath(@$agency->img) ?? $agencyDefaultImage;
                                    if (!isImageExists($agencyImage)) {
                                    $agencyImage = $agencyDefaultImage;
                                }
                            @endphp
                            @php
                                $moment = $giftSLog->moment;
                                $galleries = @$moment->images;
                            @endphp
                            <tr>
                                <td>{{ @$giftSLog->id ?? 0 }}</td>
                                <td>
                                    @if ($giftType === 'receiver' &&@$giftSLog->giftId ==0)

                                        <h5>{{__('remaining diamond')}}</h5>
                                    @else
                                        <a href="{{ url('admin/users/' . $id) }}" target="_blank"
                                           class="d-flex align-items-center text-decoration-none">
                                            <img src="{{ $image }}" width="40" height="40"
                                                 style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                            <div>
                                                <strong style="font-size: 14px;">{{ $name }}</strong><br>
                                                <small class="text-muted">UUID: {{ $uid }}</small>
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($giftSLog->room))
                                        <a href="{{ url('admin/rooms/' .  $giftSLog->room->id) }}" target="_blank"
                                           class="d-flex align-items-center text-decoration-none">
                                            <img src="{{ $url }}"
                                                 width="30" height="30"
                                                 style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                            <div>
                                                <span>{{ $roomName }}</span><br>
                                                <small
                                                    class="text-muted">Type: {{ $giftSLog->room->type ?? '-' }}</small>
                                            </div>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if($galleries && $galleries->count() > 0)
                                        <div id="image-gallery-{{ $moment->id }}" style="display: none;">
                                            @foreach($galleries as $image)
                                                @php
                                                    $imgUrl = getDriverUrl() . '/' . $image->image;
                                                @endphp
                                                <img src="{{ $imgUrl }}"
                                                     style="width: 100%; height: 200px; object-fit: cover;"
                                                     data-original="{{ $imgUrl }}"
                                                     loading="lazy"
                                                     class="gallery-image">
                                            @endforeach
                                        </div>

                                        {{-- Show first image as thumbnail --}}
                                        @php
                                            $firstImageUrl = getDriverUrl() . '/' . $galleries->first()->image;
                                        @endphp
                                        <img src="{{ $firstImageUrl }}"
                                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                                             onclick="document.querySelector('#image-gallery-{{ $moment->id }} img').click()">

                                        @push('scripts')
                                            <script>
                                                new Viewer(document.getElementById('image-gallery-{{ $moment->id }}'));
                                            </script>
                                        @endpush
                                    @else
                                        No Image
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ url('admin/gifts/' .  @$giftSLog->gift->id) }}" target="_blank"
                                       class="d-flex align-items-center text-decoration-none">
                                        {!! handleShowImageWithTypes($giftSLog->gift->id ?? 0, getImagePath($giftSLog->gift->img ?? ''), 30, 30) !!}
                                        <div>
                                            <span>{{ $giftName }}</span><br>
                                            <span>id: {{ $giftSLog->gift->id ?? 0 }}</span><br>
                                            <small class="text-muted">
                                                {{ __('Type') }}
                                                : {{ __(ucfirst(TYPE_GIFT[@$giftSLog->gift->type ?? 1])) }}<br>
                                                @if(@$giftSLog->gift->pk)
                                                    <br>{{ __('PK') }}
                                                @endif
                                            </small>
                                        </div>
                                    </a>
                                </td>
                                @if($giftType == 'receiver')
                                    <td>
                                        @if ($giftSLog->agency_id)
                                            <a href="{{ url('admin/agencies/' . $agencyId) }}" target="_blank"
                                               class="d-flex align-items-center text-decoration-none">
                                                <img src="{{ $agencyImage }}"
                                                     width="50" height="30"
                                                     style="object-fit: cover; border-radius: 4px; border: 1px solid #ccc; padding: 2px; margin-right: 10px;">
                                                <div>
                                                    <span>{{ $agencyName }}</span><br>
                                                    <small class="text-muted">id: {{ $agencyId ?? 0 }}</small>
                                                </div>
                                            </a>
                                        @else
                                            <span class="text-danger">{{__('not join to agency')}}</span>
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $giftSLog->giftNum }}</td>
                                <td>{{  $giftSLog->giftPrice}}</td>
                                <td>{{ \Carbon\Carbon::parse($giftSLog->created_at)->timezone(getTimezone())->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if($giftSLogs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-center mt-3">
                        {{ $giftSLogs->appends([
                            'tab' => 'gift-log',
                            'gift_type' => $giftType,
                            'start_at' => request('start_at'),
                            'end_at' => request('end_at'),
                            'gift_page' => $giftSLogs->currentPage()
                        ])->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="Add_model" tabindex="-1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
            <div class="modal-content position-relative">
                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.pack.free') }}" method="POST" id="add_form">
                    @csrf
                    <div class="modal-body p-0">
                        <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                            <h4 class="mb-1" id="modalExampleDemoLabel"> {{ __('dashboard.free') }}</h4>
                        </div>
                        <div class="p-4">

                            <div class="row" style="justify-content:space-evenly">

                                <input type="hidden" name=id class="item_id">
                                <div class="mb-3 col-md-12">
                                    <label for="type" class="form-label">{{ __('type') }}</label>
                                    <select name="type" id="type" class="form-select">
                                        <option value="0">{{ __('dashboard.raise') }}</option>
                                        <option value="1">{{ __('dashboard.lower') }}</option>
                                    </select>
                                </div>

                                <!-- Days Input -->
                                <div class="mb-3 col-md-6">
                                    <label for="days" class="form-label">{{ __('days') }}</label>
                                    <input type="number" class="form-control" id="days" name="days"
                                           placeholder="{{ __('days') }}">
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer mt-3">
                        <button class="btn btn-secondary close-modal-btn" type="button"
                                data-bs-dismiss="modal">{{ __('Cancel') }} </button>
                        <button class="btn btn-primary add_country" type="submit">{{ __('save') }} </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="item_modal_update" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg mt-6" role="document">
        <div class="modal-content border-0">
            <div class="modal-content position-relative">
                <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                    <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ url('/admin/update-user') }}" id="country_update_form" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-0">

                        <div class="p-4">
                            <div class="row flex-evenly">
                                <input type="hidden" name="id" value="{{ old('id', $user->id) }}">

                                <div class="col-lg-6 mb-3 form-group">
                                    <label class="form-label">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $user->name ?? '') }}">
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label class="form-label">{{ __('uuid') }}</label>
                                    <input type="text" name="uuid" class="form-control"
                                           value="{{ old('uuid', $user->uuid ?? '') }}">
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label class="form-label">{{ __('email') }}</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $user->email ?? '') }}">
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label class="form-label">{{ __('phone') }}</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="{{ old('phone', $user->phone ?? '') }}">
                                </div>

                                <div class="mb-3 col-lg-12 form-group">
                                    <label class="form-label">{{ __('Gender') }}</label>
                                    <select class="form-select col-lg-6" name="gender">
                                        <option value="">{{ __('Choose gender') }}</option>
                                        <option
                                            value="0" {{ old('gender', $user->profile->gender ?? '') == '0' ? 'selected' : '' }}>{{ __('female') }}</option>
                                        <option
                                            value="1" {{ old('gender', $user->profile->gender ?? '') == '1' ? 'selected' : '' }}>{{ __('male') }}</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 form-group mb-3">
                                    <label class="form-label">{{ __('image') }}</label>
                                    <input class="form-control" name="image" accept="image/*" type="file"/>
                                    <div class="mt-2">
                                        <img
                                            src="{{ getImagePath($user->profile->avatar ?? '') ?? asset('images/default-avatar.png') }}"
                                            class="rounded"
                                            style="width: 100px; height: 100px"
                                            id="img_edit"
                                            alt="{{ $user->name ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3 col-lg-12 form-group">
                                    <label class="form-label">{{ __('Country') }}</label>
                                    <select class="form-select col-lg-6" name="country_id" id="country_id">
                                        @foreach($countries as $id => $name)
                                            <option
                                                value="{{ $id }}" {{ old('country_id', $user->country_id ?? null) == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3 col-lg-12 form-group">
                                    <label class="form-label">{{ __('bio') }}</label>
                                    <textarea class="form-control" cols="10" name="bio"
                                              rows="2">{{ old('bio', $user->bio ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary cancel_user_item_model_btn" type="button"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button class="btn btn-primary" type="submit">{{ __('edit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery أولاً -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script> -->

<script>


    // ── Cover Slideshow Auto-Rotation ──
    var coverCurrentIndex = 0;
    var coverSlides = document.querySelectorAll('.cover-slide');
    var coverDots = document.querySelectorAll('.cover-dot');
    var coverTimer = null;

    function coverGoTo(index) {
        if (coverSlides.length <= 1) return;
        coverSlides[coverCurrentIndex].classList.remove('active');
        if (coverDots.length) coverDots[coverCurrentIndex].classList.remove('active');
        coverCurrentIndex = (index + coverSlides.length) % coverSlides.length;
        coverSlides[coverCurrentIndex].classList.add('active');
        if (coverDots.length) coverDots[coverCurrentIndex].classList.add('active');
    }

    function coverSlide(direction) {
        coverGoTo(coverCurrentIndex + direction);
        coverResetTimer();
    }

    function coverResetTimer() {
        if (coverTimer) clearInterval(coverTimer);
        if (coverSlides.length > 1) {
            coverTimer = setInterval(function() { coverGoTo(coverCurrentIndex + 1); }, 4000);
        }
    }

    // Click on dots
    coverDots.forEach(function(dot) {
        dot.addEventListener('click', function() {
            coverGoTo(parseInt(this.getAttribute('data-index')));
            coverResetTimer();
        });
    });

    // Start auto-rotation
    coverResetTimer();

    $(document).ready(function () {

        $(document).on('click', '.edit_user_item_model_btn', function () {
            $('#item_modal_update').modal('show');
        });

        $(document).on('click', '.cancel_user_item_model_btn', function () {
            $('#item_modal_update').modal('hide');
        });
        $('#add_form').on('submit', function (e) {
            e.preventDefault(); // prevent default form submit

            let form = $(this);
            let formData = form.serialize();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function (response) {
                    // ✅ close modal
                    $('#Add_model').modal('hide');

                    $('#Add_model').modal('hide');

                    // ✅ Reload the page
                    location.reload();

                },
                error: function (xhr) {
                    // show error message
                    let errors = xhr.responseJSON.errors;
                    let msg = '';
                    for (let key in errors) {
                        msg += errors[key][0] + '\n';
                    }
                    alert(msg || 'Something went wrong!');
                }
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'packs';

        const allTabs = document.querySelectorAll('.tab-btn');
        let targetElement = null;

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);

            if (!content) return; // ✅ prevent null error

            if (target === selectedTab + '-tab') {
                tab.classList.add('active');
                content.classList.remove('d-none');
                content.style.display = 'block';
                targetElement = content;
            } else {
                tab.classList.remove('active');
                content.classList.add('d-none');
                content.style.display = 'none';
            }

            // Remove the click handler that prevents default and reloads
        });

        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 300);
        }
    });

    // Function to handle tab switching
    function handleTabSwitching() {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'packs';

        const allTabs = document.querySelectorAll('.tab-btn');
        let targetElement = null;

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);

            if (!content) return; // ✅ prevent null error

            if (target === selectedTab + '-tab') {
                tab.classList.add('active');
                content.classList.remove('d-none');
                content.style.display = 'block';
                targetElement = content;
            } else {
                tab.classList.remove('active');
                content.classList.add('d-none');
                content.style.display = 'none';
            }
        });

        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 300);
        }
    }

    // Initialize PJAX
    $(document).pjax('a[data-pjax]', '#pjax-container');

    // PJAX event listeners for loading indicator
    $(document).on('pjax:start', function() {
        $('#tab-loading').show();
    });

    $(document).on('pjax:end', function() {
        $('#tab-loading').hide();
        handleTabSwitching(); // Update tabs after PJAX load
    });

    // Handle tab switching on initial load
    document.addEventListener("DOMContentLoaded", function () {
        handleTabSwitching();
    });
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'packs';

        const allTabs = document.querySelectorAll('.tab-btn');
        let targetElement = null;

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);

            if (!content) return; // ✅ prevent null error

            if (target === selectedTab + '-tab') {
                tab.classList.add('active');
                content.classList.remove('d-none');
                content.style.display = 'block';
                targetElement = content;
            } else {
                tab.classList.remove('active');
                content.classList.add('d-none');
                content.style.display = 'none';
            }

            // Remove the click handler that prevents default and reloads
        });

        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 300);
        }
    });


    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            btn.classList.add('active');
            const target = btn.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });


    $(document).ready(function () {

        $(document).on('click', '.remove-bd-btn', function (e) {
            e.preventDefault();
            let btn = $(this);
            let url = btn.data('url');

            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تستطيع التراجع بعد الحذف!",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.value) {
                    let form = $('<form>', {
                        'method': 'POST',
                        'action': url
                    }).append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': LA.token
                    })).append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'POST'
                    }));
                    form.appendTo('body').submit();
                }
            });
        });

        $('#agency_id').select2({
            placeholder: 'Select agency',
            allowClear: true,
            ajax: {
                url: '/api/search/host-agency', // ✅ make sure this matches your route
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.data.map(item => ({
                            id: item.id,
                            text: item.name
                        })),
                        pagination: {
                            more: data.next_page_url !== null
                        }
                    };
                },
                cache: true
            }
        });

        console.log("Document ready");

        function showLoader() {
            console.log("Showing loader");
            Swal.fire({
                title: 'Loading...',  // تغيير النص ليوضح الرسالة
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function showSuccess(message, callback = null) {
            console.log("Showing success:", message);
            Swal.fire({
                icon: 'success',  // استبدال type بـ icon
                title: message,
                confirmButtonText: 'OK'
            }).then(() => {
                console.log("Success confirmed");
                if (callback) {
                    console.log("Running success callback");
                    callback();
                }
            });
        }

        function showError(message) {
            console.log("Showing error:", message);
            Swal.fire({
                icon: 'error',  // استبدال type بـ icon
                title: message,
                confirmButtonText: 'OK'
            });
        }

        function confirmAction(message, onConfirm) {
            console.log("Confirm action:", message);
            Swal.fire({
                title: message,
                icon: 'question',  // استبدال type بـ icon
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then(result => {
                console.log("Confirmation result:", result);
                console.log("isConfirmed:", result.isConfirmed);

                if (result.value) {
                    console.log("User confirmed action");
                    onConfirm();
                } else {
                    console.log("User cancelled action");
                }
            });
        }


        // قبول الطلب
        $('.accept-btn').click(function () {
            const id = $(this).data('id');
            console.log("Accept clicked, ID:", id);
            confirmAction('{{ __("are_you_sure_accept") }}', () => {
                showLoader();
                $.post(`/admin/agencies/accept_join/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
                    console.log("Accept response:", response);
                    if (response.status) {
                        showSuccess(response.message, () => {
                            const url = new URL(window.location.href);
                            url.searchParams.set('tab', 'requests');
                            window.location.href = url.toString();
                        });
                    } else {
                        showError(response.message);
                    }
                }).fail(function (xhr) {
                    Swal.close();
                    console.error("Accept failed", xhr);
                    const res = xhr.responseJSON;
                    showError(res?.message ?? '{{ __("failed_accept_request") }}');
                });
            });
        });

        $(document).on('click', '.edit_item_model_btn', function () {
            let itemId = $(this).data('id');

            // Clear the form
            $('#add_form')[0].reset();

            // Set the hidden ID field
            $('.item_id').val(itemId);

            // Open the modal
            $('#Add_model').modal('show');
        });
        $(document).on('click', '.close-modal-btn', function () {
            $('#Add_model').modal('hide');
        });

        $(document).on('click', '.delete-btn', function () {
            let itemId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '/admin/delete-pack/' + itemId,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'An error occurred.'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.delete-badge-btn', function () {
            let itemId = $(this).data('id');

            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('This action cannot be undone!') }}",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, delete it!') }}",
                cancelButtonText: "{{ __('Cancel') }}",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '/admin/delete-badge/' + itemId,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'An error occurred.'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.delete-vip-btn', function () {
            let itemId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: '/admin/delete-user-vip/' + itemId,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'An error occurred.'
                            });
                        }
                    });
                }
            });
        });


        // رفض الطلب
        $('.reject-btn').click(function () {
            const id = $(this).data('id');
            console.log("Reject clicked, ID:", id);
            confirmAction('{{ __("are_you_sure_reject") }}', () => {
                showLoader();
                $.post(`/admin/agencies/reject_join/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
                    console.log("Reject response:", response);
                    if (response.status) {
                        showSuccess(response.message, () => {
                            const url = new URL(window.location.href);
                            url.searchParams.set('tab', 'requests');
                            window.location.href = url.toString();
                        });
                    } else {
                        showError(response.message);
                    }
                }).fail(function (xhr) {
                    Swal.close();
                    console.error("Reject failed", xhr);
                    const res = xhr.responseJSON;
                    showError(res?.message ?? '{{ __("failed_reject_request") }}');
                });
            });
        });

        // ترقية إلى Admin
        $('.make-admin-btn').click(function () {
            const id = $(this).data('id');
            console.log("Make admin clicked, ID:", id);
            confirmAction('{{ __("are_you_sure_make_admin") }}', () => {
                showLoader();
                $.post(`/admin/agencies/admin/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
                    console.log("Make admin response:", response);
                    if (response.status) {
                        showSuccess(response.message, () => {
                            location.reload();
                        });
                    } else {
                        showError(response.message);
                    }
                }).fail(function (xhr) {
                    Swal.close();
                    console.error("Make admin failed", xhr);
                    const res = xhr.responseJSON;
                    showError(res?.message ?? '{{ __("failed_make_admin") }}');
                });
            });
        });


    });

</script>

