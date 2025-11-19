@php use App\Helpers\Common;use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

    <style>

        .label-small-font {
    font-size: 12px;
}
    .nav-tabs{
        background: var(--box-background-color);
    }
    .nav-link.active {
        background-color: var(--primary-color);
        color: white;
    }
    .permissions-section {
        display: none;
    }
    .permissions-section.active {
        display: block;
    }
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    .permission-group {
        background-color: var(--box-background-color);
        border-radius: 8px;
        padding: 15px;
    }
    .permission-group-title {
        /* text-align: center; */
        font-size: 16px;
        font-weight: bold;
        color: #333;
        /* margin-bottom: 15px; */
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
        display: flex;
        /* align-items: center;
        justify-content: center; */
        gap: 10px;
    }
    .group-select-all {
        margin: 0;
    }
    .form-check {
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .form-check-input {
        margin: 0;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .form-check-label {
        font-size: 14px;
        color: #444;
        line-height: 1.4;
        word-wrap: break-word;
        flex: 1;
    }

    /* RTL Specific Styles */
    [dir="rtl"] .form-check {
        padding-right: 0;
        padding-left: 25px;
        flex-direction: row-reverse;
    }
    [dir="rtl"] .form-check-input {
        margin-right: 0;
        margin-left: 0;
    }
    [dir="rtl"] .permission-group-title {
        flex-direction: row-reverse;
    }
        :root {
            --primary-color: {{ config('themes.primaryColor') }};
            --secondary-color: {{ config('themes.secondaryColor') }};
            --green-color: {{ config('themes.greenColor') }};
            --text-primary-color: {{ config('themes.textPrimaryColor') }};
            --text-secondary-color: {{ config('themes.textSecondaryColor') }};
            --box-background-color: {{ config('themes.boxBackgroundColor') }};
            --table-background-color: {{ config('themes.tableBackGroundColor')}}
             --background-image:{{ config('themes.backgroundImage') }};
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

        .agency-header {
            display: flex;
            align-items: flex-start;
            gap: 25px;
            margin-bottom: 30px;
            position: relative;
            padding: 20px;
            background: var(--secondary-color);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            /* filter: brightness(0.5); */

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
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-btn:hover:not(.active) {
            color: #34495e;
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

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--secondary-color);
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

        .data-table tr:hover {
            background: #f8f9fa;
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


        .pagination-wrapper {
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            border-top: 1px solid #eee;
            background: var(--secondary-color);
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-top: 1px solid #dee2e6;
        }

        .card-header {
            border-bottom: 1px solid #dee2e6;
        }

        .card-title {
            color: #333;
            font-weight: 500;
        }
        .table tbody tr:nth-child(even) {
            background-color: var(--secondary-color) !important;
            filter: brightness(0.95);
        }
    </style>

</head>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="..." crossorigin="anonymous" /> -->

<body>
  @php
           $activeTab = request('tab', 'packs');

    @endphp
        <!-- Navigation Tabs -->
    <div class="agency-tabs">

          <a href="?tab=packs" class="tab-btn" data-target="packs-tab">{{ __('country manager') }}</a>

        <a href="?tab=vips" class="tab-btn {{ $activeTab == 'vips' ? 'active' : '' }}" data-target="vips-tab">{{ __('area manager') }}</a>

        

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

   <div class="tab-content active" id="packs-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="text-align: left;">{{ __('Permissions') }}</h4>
        </div>

        <div class="card-body">

            @php
                        use App\Models\RoleCategory;
                        use App\Enums\PermissionType;
                        // Group permissions by category first
                        $grouped = $permissions->groupBy('category');
                        $permissionType = $permissionType ??'role-country-manager';
                        $categories = RoleCategory::orderBy('sort')
                        ->select('slug', 'type')
                        ->where(function ($q) use($permissionType) {
                            $q->where('type',$permissionType);
                        })->get();
                        //dd($categories);
                        $selected = $selectedPermissions ?? [];

                        // Pre-process all permissions by category and group
                        $allGroupedPermissions = [];
                    foreach ($grouped as $categorySlug => $categoryPermissions) {
                        $allGroupedPermissions[$categorySlug] = $categoryPermissions->groupBy(function ($permission) {
                            $slug = $permission->slug;

                            if (str_contains($slug, '-switch-')) {
                                // Split by '-switch-'
                                $parts = explode('-switch-', $slug);
                                // $parts[1] is what comes after 'switch-'
                                return $parts[1];  // group by 'user' or 'agency' or whatever after switch-
                            } else {
                                // Normal grouping: remove first part and group by the rest
                                $parts = explode('-', $slug);
                                array_shift($parts);
                                return implode('-', $parts);
                            }
                        });
                    }
                    //dd($allGroupedPermissions);
                        $firstCategory = $categories->first()->slug ?? null;
                    @endphp
            <form action="{{ url('admin/update-super-roles') }}" method="POST">
                @csrf
                <input type="hidden" name="permissions_all" id="permissions_all">
                <input type="hidden" name="role_id" id="super_role_id"value="{{ $superAdminRole->id }}">

                {{-- Category Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
                    @foreach($categories as $category)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               data-category="{{ $category->slug }}"
                               href="#">
                                {{ __($category->slug) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Select All per Category --}}
                <div class="category-select-all-container mb-3" style="padding: 0 20px;">
                    @foreach($categories as $category)
                        <div class="category-select-wrapper {{ $category->slug === $firstCategory ? 'active' : '' }}"
                             data-category="{{ $category->slug }}"
                             style="display: {{ $category->slug === $firstCategory ? 'block' : 'none' }};">
                            <div class="form-check">
                                <input class="form-check-input category-select-all"
                                       type="checkbox"
                                       data-category="{{ $category->slug }}"
                                       id="category-{{ $category->slug }}">
                                <label class="form-check-label fw-bold" for="category-{{ $category->slug }}">
                                    {{ __('Select All') }} {{ __($category->slug) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Permissions Grid --}}
                <div id="permissions-container">
                    @foreach($allGroupedPermissions as $categorySlug => $groupedPermissions)
                        <div class="permissions-section {{ $categorySlug === $firstCategory ? 'active' : '' }}"
                             data-category="{{ $categorySlug }}">
                            <div class="permissions-grid">
                                @foreach($groupedPermissions as $group => $perms)
                                    <div class="permission-group">
                                        <h6 class="permission-group-title">
                                            <input class="form-check-input group-select-all"
                                                   type="checkbox"
                                                   data-group="{{ $group }}"
                                                   data-category="{{ $categorySlug }}"
                                                   id="group-{{ $categorySlug }}-{{ $group }}">
                                            <label for="group-{{ $categorySlug }}-{{ $group }}" class="label-small-font">
                                                {{ __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                                            </label>
                                        </h6>
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->id }}"
                                                       data-slug="{{ $perm->slug }}"
                                                       data-group="{{ $group }}"
                                                       data-category="{{ $categorySlug }}"
                                                       id="perm-{{ $perm->id }}"
                                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                                    {{ __($perm->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
               <br>
                {{-- Save Button --}}
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Save Permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>



 <div class="tab-content active" id="vips-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title" style="text-align: left;">{{ __('Permissions') }}</h4>
        </div>

        <div class="card-body">

            @php
                       
                        // Group permissions by category first
                        $grouped = $areaPermissions->groupBy('category');
                        $permissionType = $areaPermissionType ??'role-country-manager';
                        $categories = RoleCategory::orderBy('sort')
                        ->select('slug', 'type')
                        ->where(function ($q) use($permissionType) {
                            $q->where('type',$permissionType);
                        })->get();
                        //dd($categories);
                        $selected = $areaSelectedPermissions ?? [];

                        // Pre-process all permissions by category and group
                        $allGroupedPermissions = [];
                    foreach ($grouped as $categorySlug => $categoryPermissions) {
                        $allGroupedPermissions[$categorySlug] = $categoryPermissions->groupBy(function ($permission) {
                            $slug = $permission->slug;

                            if (str_contains($slug, '-switch-')) {
                                // Split by '-switch-'
                                $parts = explode('-switch-', $slug);
                                // $parts[1] is what comes after 'switch-'
                                return $parts[1];  // group by 'user' or 'agency' or whatever after switch-
                            } else {
                                // Normal grouping: remove first part and group by the rest
                                $parts = explode('-', $slug);
                                array_shift($parts);
                                return implode('-', $parts);
                            }
                        });
                    }
                    //dd($allGroupedPermissions);
                        $firstCategory = $categories->first()->slug ?? null;
                    @endphp
            <form action="{{ url('admin/update-super-roles') }}" method="POST">
                @csrf
                <input type="hidden" name="area_permissions_all" id="permissions_all">
                <input type="hidden" name="role_id" id="role_id"value="{{ $areaManagerRole->id }}">

                {{-- Category Tabs --}}
                <ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
                    @foreach($categories as $category)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                               data-category="{{ $category->slug }}"
                               href="#">
                                {{ __($category->slug) }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Select All per Category --}}
                <div class="category-select-all-container mb-3" style="padding: 0 20px;">
                    @foreach($categories as $category)
                        <div class="category-select-wrapper {{ $category->slug === $firstCategory ? 'active' : '' }}"
                             data-category="{{ $category->slug }}"
                             style="display: {{ $category->slug === $firstCategory ? 'block' : 'none' }};">
                            <div class="form-check">
                                <input class="form-check-input category-select-all"
                                       type="checkbox"
                                       data-category="{{ $category->slug }}"
                                       id="category-{{ $category->slug }}">
                                <label class="form-check-label fw-bold" for="category-{{ $category->slug }}">
                                    {{ __('Select All') }} {{ __($category->slug) }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Permissions Grid --}}
                <div id="permissions-container">
                    @foreach($allGroupedPermissions as $categorySlug => $groupedPermissions)
                        <div class="permissions-section {{ $categorySlug === $firstCategory ? 'active' : '' }}"
                             data-category="{{ $categorySlug }}">
                            <div class="permissions-grid">
                                @foreach($groupedPermissions as $group => $perms)
                                    <div class="permission-group">
                                        <h6 class="permission-group-title">
                                            <input class="form-check-input group-select-all"
                                                   type="checkbox"
                                                   data-group="{{ $group }}"
                                                   data-category="{{ $categorySlug }}"
                                                   id="group-{{ $categorySlug }}-{{ $group }}">
                                            <label for="group-{{ $categorySlug }}-{{ $group }}" class="label-small-font">
                                                {{ __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                                            </label>
                                        </h6>
                                        @foreach($perms as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->id }}"
                                                       data-slug="{{ $perm->slug }}"
                                                       data-group="{{ $group }}"
                                                       data-category="{{ $categorySlug }}"
                                                       id="perm-{{ $perm->id }}"
                                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                                    {{ __($perm->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>



                <br>
                {{-- Save Button --}}
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Save Permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>



  
   

    
     
     






  

     


<!-- jQuery أولاً -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<!-- SweetAlert2 -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script> -->


<script>



    

    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'packs';

        const allTabs = document.querySelectorAll('.tab-btn');
        const allTabContents = document.querySelectorAll('[id$="-tab"]');

        let targetElement = null;

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);

            if (target.startsWith(selectedTab)) {
                tab.classList.add('active');
                content.style.display = 'block';
                targetElement = content; // خزن العنصر لعمل scroll إليه لاحقًا
            } else {
                tab.classList.remove('active');
                content.style.display = 'none';
            }

            tab.addEventListener('click', function (e) {
                    e.preventDefault();

                    const currentUrl = new URL(window.location.href);
                    const href = tab.getAttribute('href');
                    const targetUrl = new URL(href, currentUrl.origin);

                    // تحقق أن التنقل داخل نفس الصفحة + تغيير التابة فقط
                    if (currentUrl.pathname === targetUrl.pathname && targetUrl.searchParams.get('tab')) {
                        document.getElementById('tab-loading').style.display = 'block';
                        allTabs.forEach(t => t.style.pointerEvents = 'none');

                        setTimeout(() => {
                            window.location.href = href;
                        }, 300);
                    } else {
                        // لا تعرض اللودر إذا الرابط خارج التابات
                        window.location.href = href;
                    }
                });
            });


        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 500); // تأخير بسيط للتأكد أن العنصر ظاهر
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


    
         $(function () {
        let selectedPermissions = new Set(@json($selected ?? []));

        function updateHiddenInput() {
            $('#permissions_all').val([...selectedPermissions].join(','));
        }

        function getBrowsePermissionId(currentCheckbox) {
            const groupContainer = currentCheckbox.closest('.permission-group');
            const browseCheckbox = groupContainer.find('.permission-checkbox').filter(function() {
                const permSlug = $(this).data('slug');
                return permSlug && permSlug.includes('browse-');
            });

            return browseCheckbox.length ? parseInt(browseCheckbox.val()) : null;
        }

        function updateGroupCheckboxState(group, category) {
            const groupCheckboxes = $(`.permission-checkbox[data-group="${group}"][data-category="${category}"]`);
            const groupSelectAll = $(`.group-select-all[data-group="${group}"][data-category="${category}"]`);

            const totalCheckboxes = groupCheckboxes.length;
            const checkedCheckboxes = groupCheckboxes.filter(':checked').length;

            // Only checked when ALL permissions are selected, otherwise unchecked
            if (checkedCheckboxes === totalCheckboxes) {
                groupSelectAll.prop('checked', true).prop('indeterminate', false);
            } else {
                groupSelectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        function updateCategoryCheckboxState(category) {
            const categoryCheckboxes = $(`.permission-checkbox[data-category="${category}"]`);
            const categorySelectAll = $(`.category-select-all[data-category="${category}"]`);

            const totalCheckboxes = categoryCheckboxes.length;
            const checkedCheckboxes = categoryCheckboxes.filter(':checked').length;

            // Only checked when ALL permissions are selected, otherwise unchecked
            if (checkedCheckboxes === totalCheckboxes) {
                categorySelectAll.prop('checked', true).prop('indeterminate', false);
            } else {
                categorySelectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        function bindPermissionCheckboxes() {
            $('.permission-checkbox').on('change', function () {
                const id = parseInt($(this).val());
                const permSlug = $(this).data('slug');
                const group = $(this).data('group');
                const category = $(this).data('category');

                if ($(this).is(':checked')) {
                    selectedPermissions.add(id);

                    if (permSlug && (
                        permSlug.includes('create-') ||
                        permSlug.includes('edit-') ||
                        permSlug.includes('delete-')
                    )) {
                        const browsePermissionId = getBrowsePermissionId($(this));
                        if (browsePermissionId) {
                            selectedPermissions.add(browsePermissionId);
                            $(`#perm-${browsePermissionId}`).prop('checked', true);
                        }
                    }
                } else {
                    selectedPermissions.delete(id);

                    if (permSlug && permSlug.includes('browse-')) {
                        const groupContainer = $(this).closest('.permission-group');
                        groupContainer.find('.permission-checkbox').each(function() {
                            const relatedSlug = $(this).data('slug');
                            if (relatedSlug && (
                                relatedSlug.includes('create-') ||
                                relatedSlug.includes('edit-') ||
                                relatedSlug.includes('delete-')
                            )) {
                                const relatedId = parseInt($(this).val());
                                selectedPermissions.delete(relatedId);
                                $(this).prop('checked', false);
                            }
                        });
                    }
                }

                updateGroupCheckboxState(group, category);
                updateCategoryCheckboxState(category);
                updateHiddenInput();
            });
        }

        function bindGroupSelectAll() {
            $('.group-select-all').on('change', function() {
                const group = $(this).data('group');
                const category = $(this).data('category');
                const isChecked = $(this).is(':checked');

                const groupCheckboxes = $(`.permission-checkbox[data-group="${group}"][data-category="${category}"]`);

                groupCheckboxes.each(function() {
                    const id = parseInt($(this).val());
                    const currentlyChecked = $(this).is(':checked');

                    if (isChecked && !currentlyChecked) {
                        selectedPermissions.add(id);
                        $(this).prop('checked', true);
                    } else if (!isChecked && currentlyChecked) {
                        selectedPermissions.delete(id);
                        $(this).prop('checked', false);
                    }
                });

                updateCategoryCheckboxState(category);
                updateHiddenInput();
            });
        }

        function bindCategorySelectAll() {
            $('.category-select-all').on('change', function() {
                const category = $(this).data('category');
                const isChecked = $(this).is(':checked');

                const categoryCheckboxes = $(`.permission-checkbox[data-category="${category}"]`);
                const categoryGroupCheckboxes = $(`.group-select-all[data-category="${category}"]`);

                categoryCheckboxes.each(function() {
                    const id = parseInt($(this).val());
                    const currentlyChecked = $(this).is(':checked');

                    if (isChecked && !currentlyChecked) {
                        selectedPermissions.add(id);
                        $(this).prop('checked', true);
                    } else if (!isChecked && currentlyChecked) {
                        selectedPermissions.delete(id);
                        $(this).prop('checked', false);
                    }
                });

                // Update all group checkboxes in this category
                categoryGroupCheckboxes.each(function() {
                    $(this).prop('checked', isChecked).prop('indeterminate', false);
                });

                updateHiddenInput();
            });
        }

        function initializeGroupCheckboxes() {
            // Initialize all group checkboxes based on current state
            $('.group-select-all').each(function() {
                const group = $(this).data('group');
                const category = $(this).data('category');
                updateGroupCheckboxState(group, category);
            });
        }

        function initializeCategoryCheckboxes() {
            // Initialize all category checkboxes based on current state
            $('.category-select-all').each(function() {
                const category = $(this).data('category');
                updateCategoryCheckboxState(category);
            });
        }

        bindPermissionCheckboxes();
        bindGroupSelectAll();
        bindCategorySelectAll();
        initializeGroupCheckboxes();
        initializeCategoryCheckboxes();
        updateHiddenInput();

        $('#permission-tabs .nav-link').on('click', function (e) {
            e.preventDefault();
            $('#permission-tabs .nav-link').removeClass('active tab-highlight');
            $(this).addClass('active tab-highlight');
            const category = $(this).data('category');
            $('.permissions-section').removeClass('active');
            $(`.permissions-section[data-category="${category}"]`).addClass('active');

            // Show/hide the appropriate category select-all checkbox
            $('.category-select-wrapper').hide().removeClass('active');
            $(`.category-select-wrapper[data-category="${category}"]`).show().addClass('active');
        });
    });


      

       


   

</script>





{{-- @php
    use App\Models\RoleCategory;
    use App\Enums\PermissionType;
    // Group permissions by category first
    $grouped = $permissions->groupBy('category');
    $permissionType = $permissionType ??'role-country-manager';
    $categories = RoleCategory::orderBy('sort')
    ->select('slug', 'type')
    ->where(function ($q) use($permissionType) {
        $q->where('type',$permissionType);
    })->get();
    //dd($categories);
    $selected = $selectedPermissions ?? [];

    // Pre-process all permissions by category and group
    $allGroupedPermissions = [];
 foreach ($grouped as $categorySlug => $categoryPermissions) {
    $allGroupedPermissions[$categorySlug] = $categoryPermissions->groupBy(function ($permission) {
        $slug = $permission->slug;

        if (str_contains($slug, '-switch-')) {
            // Split by '-switch-'
            $parts = explode('-switch-', $slug);
            // $parts[1] is what comes after 'switch-'
            return $parts[1];  // group by 'user' or 'agency' or whatever after switch-
        } else {
            // Normal grouping: remove first part and group by the rest
            $parts = explode('-', $slug);
            array_shift($parts);
            return implode('-', $parts);
        }
    });
}
//dd($allGroupedPermissions);
    $firstCategory = $categories->first()->slug ?? null;
@endphp

<style>
    
</style>

<input type="hidden" name="permissions_all" id="permissions_all">
<ul class="nav nav-tabs mb-3" role="tablist" id="permission-tabs">
    @foreach($categories as $category)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
               data-category="{{ $category->slug }}"
               href="#">
                {{ __($category->slug) }}
            </a>
        </li>
    @endforeach
</ul>

<div class="category-select-all-container mb-3" style="padding: 0 20px;">
    @foreach($categories as $category)
        <div class="category-select-wrapper {{ $category->slug === $firstCategory ? 'active' : '' }}"
             data-category="{{ $category->slug }}"
             style="display: {{ $category->slug === $firstCategory ? 'block' : 'none' }};">
            <div class="form-check">
                <input class="form-check-input category-select-all"
                       type="checkbox"
                       data-category="{{ $category->slug }}"
                       id="category-{{ $category->slug }}">
                <label class="form-check-label fw-bold" for="category-{{ $category->slug }}">
                    {{ __('Select All') }} {{ __($category->slug) }}
                </label>
            </div>
        </div>
    @endforeach
</div>

<div id="permissions-container">
    @foreach($allGroupedPermissions as $categorySlug => $groupedPermissions)
        <div class="permissions-section {{ $categorySlug === $firstCategory ? 'active' : '' }}"
             data-category="{{ $categorySlug }}">
            <div class="permissions-grid">
                @foreach($groupedPermissions as $group => $perms)
                    <div class="permission-group">
                        <h6 class="permission-group-title">
                            <input class="form-check-input group-select-all"
                                   type="checkbox"
                                   data-group="{{ $group }}"
                                   data-category="{{ $categorySlug }}"
                                   id="group-{{ $categorySlug }}-{{ $group }}">
                            <label for="group-{{ $categorySlug }}-{{ $group }}"class="label-small-font">
                                {{ __(ucwords(str_replace(['-', '_'], ' ', $group))) }}
                            </label>
                        </h6>
                        @foreach($perms as $perm)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox"
                                       type="checkbox"
                                       value="{{ $perm->id }}"
                                       data-slug="{{ $perm->slug }}"
                                       data-group="{{ $group }}"
                                       data-category="{{ $categorySlug }}"
                                       id="perm-{{ $perm->id }}"
                                    {{ in_array($perm->id, $selected) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $perm->id }}">
                                    {{ __($perm->name) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

 --}}
