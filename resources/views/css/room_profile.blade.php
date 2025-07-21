<style>
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
            display: block;
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

    .pk-title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .team-info {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .members-count {
        color: var(--primary-color);
        font-size: 14px;
        font-weight: bold;
    }

    .team-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: #2c3e50;
    }

    .team-boss {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
        margin-bottom: 5px;
    }

    .boss-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 10px;
        object-fit: cover;
    }

    .score-info {
        font-size: 0.9em;
    }

    .team-score {
        margin-bottom: 5px;
    }

    .score-label {
        font-weight: bold;
        margin-right: 5px;
    }

    .score-value {
        color: #e74c3c;
        font-weight: bold;
    }

    .vote-count {
        color: #7f8c8d;
        margin-left: 5px;
    }

    .status-container {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .winner-badge {
        background: #27ae60;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.8em;
        margin-top: 5px;
    }

    .time-info {
        font-size: 0.85em;
        color: #666;
    }

    /* Modal Styles */
    .team-members-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .member-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }

    .member-item:hover {
        background-color: #f8f9fa;
    }

    .member-item:last-child {
        border-bottom: none;
    }

    .member-info {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 15px;
        object-fit: cover;
    }

    .member-details {
        flex-grow: 1;
    }

    .member-name {
        font-weight: bold;
    }

    .member-id {
        font-size: 0.8em;
        color: #666;
    }
    .member-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s;
    }

    .member-info {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
        gap: 20px;
    }

    .member-avatar {
        width: 60px;  /* Bigger avatar */
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .member-details {
        flex-grow: 1;
    }

    .member-name {
        font-size: 18px;  /* Bigger name */
        font-weight: bold;
        margin-bottom: 5px;
    }

    .member-id {
        font-size: 14px;  /* Bigger ID text */
        color: #666;
        line-height: 1.5;
    }

    .team-members-list {
        max-height: 600px;  /* Taller list */
        overflow-y: auto;
        padding: 10px;
    }

    .large-modal {
        font-size: 16px;  /* Bigger base font size */
    }
</style>
