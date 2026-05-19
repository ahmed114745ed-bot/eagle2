/* Moment Viewer - Facebook-like Design */
/* استخدام ألوان من config/themes.php */
<style>
    :root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
        --text-primary-color: {{ config('themes.textPrimaryColor') }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') }};
        --box-background-color: {{ config('themes.boxBackgroundColor') }};
        --table-background-color: {{ config('themes.tableBackGroundColor') }};
        --background-image: {{ config('themes.backgroundImage') }};
        --brand-background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
        --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
        --primary-hover-alpha: {{ config('themes.primaryColor')}}33;
        --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
        --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;
        --inverse-color: {{getLighterColor(config('themes.primaryColor'))}};
        --inverse-box-color: {{adjustTextColor(config('themes.boxBackgroundColor'))}};
        --success-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
        --primary-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);

        /* تعيين المتغيرات المستخدمة في التصميم */
        --primary: {{ config('themes.primaryColor') }};
        --primary-hover: {{ adjustColor(config('themes.primaryColor'), -10, -10, -10) }};
        --bg-primary: {{ config('themes.backgroundImage') }};
        --bg-secondary: {{ config('themes.boxBackgroundColor') }};
        --text-primary: {{ config('themes.textPrimaryColor') }};
        --text-secondary: {{ config('themes.textSecondaryColor') }};
        --border-color: {{ adjustColor(config('themes.boxBackgroundColor'), 20, 20, 20) }};
        --shadow-1: 0 1px 2px rgba(0, 0, 0, 0.1);
        --shadow-2: 0 2px 4px rgba(0, 0, 0, 0.1);
        --shadow-3: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        background: var(--bg-primary) !important;
        line-height: 1.34;
        direction: ltr;
    }

    body[dir="rtl"],
    html[dir="rtl"] body {
        direction: rtl;
    }

    .content-header {
        display: none !important;
    }

    .content-wrapper {
        background: var(--bg-primary) !important;
        min-height: 100vh !important;
        padding-bottom: 40px !important;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y pinch-zoom;
    }

    .content {
        height: auto !important;
        min-height: 100vh !important;
        overflow: visible !important;
        touch-action: pan-y pinch-zoom;
    }

    .viewer-container {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        min-height: 100vh !important;
        height: auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        display: block !important;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y pinch-zoom;
    }

    /* إعادة تعيين أحجام الصور */
    .viewer-container img {
        display: block;
        height: auto;
        max-height: none !important;
        max-width: none !important;
        min-height: 0 !important;
        min-width: 0 !important;
    }

    /* Header */
    .viewer-header {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        margin-bottom: 16px;
        max-width: 680px;
        margin-left: 7%;
        margin-right: 0;
    }

    .rtl .viewer-header {
        margin-left: auto;
        margin-right: 7%;
    }

    .viewer-title {
        font-size: 20px;
        font-weight: 700;
        color: black;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .viewer-title i {
        color: var(--primary);
    }

    .viewer-controls {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-input,
    .search-input {
        flex: 1;
        min-width: 180px;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 15px;
        transition: all 0.2s;
    }

    .filter-input:focus,
    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .sort-select {
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 15px;
        background: var(--bg-primary);
        color: black;
        cursor: pointer;
        transition: all 0.2s;
    }

    .sort-select:hover {
        background: var(--bg-primary);
    }

    .refresh-btn {
        padding: 8px 16px;
        color: var(--inverse-color);
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .refresh-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Feed Container */
    .moments-feed {
        width: 100%;
        max-width: 680px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
        z-index: auto;
        margin-left: 7%;
        margin-right: 0;
    }

    .rtl .moments-feed {
        margin-left: auto;
        margin-right: 7%;
    }

    /* Post Card - Facebook Style */
    .moment-post {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        overflow: visible;
        will-change: transform;
        transform: translateZ(0);
        backface-visibility: hidden;
        position: relative;
        z-index: 1;
        touch-action: pan-y;
    }

    .moment-post:hover {
        box-shadow: var(--shadow-2);
    }

    .moment-post:has(.dropdown-menu.show),
    .moment-post:has(.post-dropdown:not([style*="display: none"])) {
        z-index: 100;
    }

    /* Post Header */
    .post-header {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 1;
    }

    .user-avatar {
        width: 40px !important;
        height: 40px !important;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        border: 1px solid var(--border-color);
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 15px;
        font-weight: 600;
        margin: 0;
        cursor: pointer;
        line-height: 1.3;
        display: block;
        text-align: left !important;

    }

    .user-name:hover {
        text-decoration: underline;
    }

    .user-meta {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        margin-top: 4px;
        line-height: 1.2;
        flex-wrap: wrap;
    }

    .user-uuid {
        font-size: 12px;
    }

    .post-time {
        font-size: 12px;
    }

    /* Menu Button - Facebook Style */
    .menu-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: black;
        font-size: 20px;
        transition: all 0.2s;
        position: relative;
        z-index: 10;
    }

    .post-menu {
        position: relative;
        z-index: 10;
    }

    /* Dropdown Menu */
    .dropdown-menu,
    .post-dropdown {
        display: none;
        position: absolute;
        top: 38px;
        left: 0;
        right: auto;
        background: var(--box-background-color);
        border-radius: 8px;
        box-shadow: 0 12px 28px 0 rgba(0, 0, 0, 0.2), 0 2px 4px 0 rgba(0, 0, 0, 0.1);
        min-width: 200px;
        z-index: 9999;
        padding: 8px;
        border: 1px solid var(--border-color);
        white-space: nowrap;
        float: left;
        text-align: left;
    }

    /* RTL Support for Dropdown */
    html.rtl .dropdown-menu.different,
    html[dir="rtl"] .dropdown-menu.different,
    [dir="rtl"] .dropdown-menu.different,
    html.rtl .post-dropdown,
    html[dir="rtl"] .post-dropdown,
    [dir="rtl"] .post-dropdown {
        text-align: right;
        right: 0% !important;
        left: auto;
        float: right;
        top: 38px;
    }

    .dropdown-menu.show {
        display: block;
    }

    /* إذا لم يكن هناك مساحة على اليمين، اعرضها على اليسار */
    @media (max-width: 768px) {
        .dropdown-menu,
        .post-dropdown {
            right: 0;
            left: auto;
        }
    }

    .dropdown-item,
    .post-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-primary);
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 15px;
        font-weight: 500;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        text-decoration: none;
    }

    /* RTL Support for Dropdown Item */
    html.rtl .dropdown-item,
    html[dir="rtl"] .dropdown-item,
    [dir="rtl"] .dropdown-item,
    html.rtl .post-dropdown-item,
    html[dir="rtl"] .post-dropdown-item,
    [dir="rtl"] .post-dropdown-item {
        text-align: right;
        flex-direction: row-reverse;
    }

    .dropdown-item:hover,
    .post-dropdown-item:hover {
        background: var(--primary-color);
    }

    .dropdown-item.delete-item,
    .post-dropdown-item.delete-item {
        color: #e4405f;
    }

    .dropdown-item i,
    .post-dropdown-item i {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }

    /* Post Content */
    .post-content {
        padding: 0 16px 12px;
    }

    .post-description {
        font-size: 15px;
        line-height: 1.5;
        white-space: pre-wrap;
        word-wrap: break-word;
        margin-bottom: 8px;
        unicode-bidi: plaintext;
        transition: max-height 0.3s ease;
    }

    .post-description[dir="rtl"] {
        text-align: right !important;
        direction: rtl;
    }

    .post-description[dir="ltr"] {
        text-align: left !important;
        direction: ltr;
    }

    .post-description.collapsible {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .see-more-btn {
        color: var(--primary-color);
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        padding: 4px 0;
        font-size: 14px;
        margin-top: 4px;
        transition: opacity 0.2s;
    }

    .see-more-btn:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    }

    .see-more-btn:hover {
        text-decoration: underline;
    }

    /* Post Media - Facebook Style Grid */
    .post-media {
        width: 100%;
        background: #000;
        position: relative;
        overflow: hidden;
        contain: layout style paint;
        display: grid;
        gap: 2px;
        cursor: pointer;
        max-height: 370px !important;
    }

    /* Grid Layouts */
    .media-grid.grid-1 {
        grid-template-columns: 1fr;
        max-height: 500px;
    }

    .media-grid.grid-2 {
        grid-template-columns: 1fr 1fr;
        max-height: 400px;
    }

    .media-grid.grid-3 {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        max-height: 400px;
    }

    .media-grid.grid-3 .media-item:first-child {
        grid-row: 1 / 3;
    }

    .media-grid.grid-4 {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        max-height: 400px;
    }

    .media-grid.grid-5-plus {
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: 1fr 1fr;
        max-height: 400px;
    }

    .media-grid.grid-5-plus .media-item:first-child {
        grid-column: 1 / 3;
        grid-row: 1 / 3;
    }

    .media-item {
        position: relative;
        overflow: hidden;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
    }

    .post-media img,
    .post-media video {
        width: 62% !important;
        height: 100% !important;
    <!-- object-fit: cover !important;
    --> display: block !important;
        transition: transform 0.3s ease;
    }

    .media-item:hover img,
    .media-item:hover video {
        transform: scale(1.05);
    }

    .media-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 48px;
        font-weight: 700;
        pointer-events: none;
    }

    /* Media Lightbox */
    .media-lightbox {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .media-lightbox.active {
        display: flex;
    }

    .lightbox-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.95);
        cursor: pointer;
    }

    .lightbox-content {
        position: relative;
        z-index: 10001;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-media {
        max-width: 100%;
        max-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-media img,
    .lightbox-media video {
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        display: block;
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        z-index: 10002;
    }

    .lightbox-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        z-index: 10002;
    }

    .lightbox-nav:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%) scale(1.1);
    }

    .lightbox-prev {
        left: 20px;
    }

    .lightbox-next {
        right: 20px;
    }

    .lightbox-counter {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px 20px;
        border-radius: 20px;
        font-size: 16px;
        font-weight: 600;
        z-index: 10002;
    }

    /* Post Stats */
    .post-stats {
        padding: 12px 16px 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 15px;
    }

    .stats-left,
    .stats-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .stat-item:hover {
        background: var(--hover-bg);
    }

    .stat-item i {
        font-size: 16px;
    }

    .stat-item:first-child i {
        color: #e4405f;
    }

    .stat-item:nth-child(2) i {
        color: #9b59b6;
    }

    .stats-right .stat-item i {
        color: black;
    }

    .stat-item span {
        font-weight: 500;
        min-width: 16px;
        text-align: left;
    }

    /* Legacy styles for backward compatibility */
    .like-icon {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .like-icon i {
        color: #e4405f;
    }

    .stats-right {
        display: flex;
        gap: 12px;
    }

    .stats-right span {
        cursor: pointer;
    }

    .stats-right span:hover {
        text-decoration: underline;
    }

    /* Post Actions */
    .post-actions {
        padding: 4px 16px;
        display: flex;
        border-top: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
    }

    .action-btn {
        flex: 1;
        padding: 8px;
        background: none;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: var(--bg-primary);
    }

    .action-btn i {
        font-size: 18px;
    }

    /* Comments Section */
    .comments-section {
        padding: 12px 16px;
        max-height: 400px;
        overflow-y: auto;
        display: none;
    }

    .comments-section.show {
        display: block;
    }

    .comment-item {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
    }

    .comment-avatar {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .comment-content {
        flex: 1;
        min-width: 0;
    }

    .comment-bubble {
        background: var(--bg-primary);
        padding: 8px 12px;
        border-radius: 18px;
        display: inline-block;
        max-width: 100%;
    }

    .comment-author {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }

    .comment-text {
        font-size: 15px;
        color: var(--text-primary);
        word-wrap: break-word;
    }

    .comment-actions {
        padding: 0 12px;
        margin-top: 4px;
        display: flex;
        gap: 16px;
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .comment-actions span {
        cursor: pointer;
    }

    .comment-actions span:hover {
        text-decoration: underline;
    }

    .comment-delete {
        color: #e4405f;
    }

    /* Likes Section */
    .likes-section {
        padding: 12px 16px;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        border-top: 1px solid var(--border-color);
    }

    .likes-section.show {
        display: block;
    }

    .like-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .like-item:hover {
        background: var(--bg-primary);
    }

    .like-avatar {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .like-user-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .like-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .like-uuid {
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Loading States */
    .loading-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px;
        min-height: 200px;
    }

    .modal-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        width: 100%;
    }

    .modal-loading .spinner {
        width: 30px;
        height: 30px;
        border-width: 3px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--box-background-color);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-1);
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 15px;
        margin-bottom: 20px;
    }

    /* Load More */
    .load-more-container {
        text-align: center;
        padding: 20px;
    }

    .load-more-btn {
        padding: 10px 24px;
        background: var(--box-background-color);
        color: var(--text-primary-color);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .load-more-btn:hover {
        background: var(--bg-primary);
    }

    /* Comments Section - Hidden (Using Side Modal Instead) */
    .comments-section,
    .likes-section {
        display: none !important;
    }

    /* Scroll to Top Button */
    .scroll-top {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
        background: var(--primary-color);
        color: var(--inverse-color);
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s;
        z-index: 1000;
    }

    .scroll-top:hover {
        background: var(--primary-hover);
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .scroll-top.visible {
        display: flex;
    }

    /* Scrollbar Styling */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-primary);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--text-secondary);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        /* تحسين التمرير على الموبايل */
        body {
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y pinch-zoom;
            overflow-x: hidden;
        }

        html {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
            touch-action: manipulation;
        }

        /* تعطيل الانميشن على الموبايل للسرعة */
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }

        .viewer-container {
            padding: 12px 0;
            touch-action: pan-y;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .viewer-header {
            margin: 0 8px 12px !important;
            padding: 12px 16px !important;
        }

        .moments-feed {
            margin: 0 8px 12px !important;
        }

        .viewer-title {
            font-size: 18px;
        }

        .viewer-controls {
            flex-direction: column;
            width: 100%;
        }

        .filter-input,
        .search-input,
        .sort-select {
            width: 100%;
        }

        .moments-feed {
            max-width: 100%;
            gap: 12px;
            padding: 0 8px;
        }

        .moment-post {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }

        .post-media img,
        .post-media video {
            max-height: 400px;
            image-rendering: -webkit-optimize-contrast;
        }

        .lightbox-nav {
            width: 44px;
            height: 44px;
            font-size: 20px;
        }

        .lightbox-prev {
            left: 10px;
        }

        .lightbox-next {
            right: 10px;
        }

        .lightbox-close {
            width: 40px;
            height: 40px;
            top: 10px;
            right: 10px;
            font-size: 20px;
        }

        .lightbox-counter {
            bottom: 10px;
            font-size: 14px;
            padding: 6px 16px;
        }

        .media-grid.grid-2,
        .media-grid.grid-3,
        .media-grid.grid-4,
        .media-grid.grid-5-plus {
            max-height: 300px;
        }

        /* تحسين الأداء */
        .moment-post {
            will-change: auto;
            contain: layout style;
        }

        .user-avatar,
        .modal-user-avatar {
            will-change: auto;
        }

        .scroll-top {
            bottom: 16px;
            right: 16px;
            width: 48px;
            height: 48px;
        }

        .dropdown-menu,
        .post-dropdown {
            min-width: 180px;
            font-size: 14px;
        }

        .post-header {
            padding: 10px 12px;
        }

        .user-name {
            font-size: 14px;
        }

        .user-meta {
            font-size: 11px;
        }

        .post-description {
            font-size: 14px;
        }

        .post-stats {
            font-size: 13px;
            padding: 10px 12px 6px;
        }

        .action-btn {
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        /* تحسين الأداء للشاشات الصغيرة */
        html {
            -webkit-tap-highlight-color: transparent;
        }

        .viewer-container {
            padding: 8px 0;
        }

        .viewer-header {
            margin: 0 4px 8px !important;
            padding: 10px 12px !important;
        }

        .moments-feed {
            margin: 0 4px 8px !important;
        }

        .viewer-title {
            font-size: 16px;
            gap: 6px;
        }

        .filter-input,
        .search-input,
        .sort-select,
        .refresh-btn {
            font-size: 14px;
            padding: 6px 10px;
        }

        .moments-feed {
            gap: 8px;
            padding: 0 4px;
        }

        .post-header {
            padding: 8px 10px;
            gap: 10px;
        }

        .user-avatar {
            width: 36px !important;
            height: 36px !important;
        }

        .user-name {
            font-size: 13px;
        }

        .user-meta {
            font-size: 10px;
        }

        .menu-btn {
            width: 32px;
            height: 32px;
            font-size: 18px;
        }

        .dropdown-menu,
        .post-dropdown {
            min-width: 160px;
            font-size: 13px;
        }

        .dropdown-item,
        .post-dropdown-item {
            padding: 6px 10px;
            font-size: 13px;
        }

        .post-content {
            padding: 0 12px 10px;
        }

        .post-description {
            font-size: 13px;
        }

        .post-media img,
        .post-media video {
            max-height: 300px;
        }

        .nav-btn {
            width: 32px;
            height: 32px;
            font-size: 16px;
        }

        .media-counter {
            top: 12px;
            right: 12px;
            font-size: 12px;
            padding: 4px 10px;
        }

        .post-stats {
            font-size: 12px;
            padding: 8px 12px 6px;
        }

        .post-actions {
            padding: 2px 12px;
        }

        .action-btn {
            font-size: 13px;
            padding: 6px;
        }

        .action-btn i {
            font-size: 16px;
        }

        .scroll-top {
            bottom: 12px;
            right: 12px;
            width: 44px;
            height: 44px;
            font-size: 18px;
        }

        .empty-state {
            padding: 40px 16px;
        }

        .empty-state i {
            font-size: 48px;
        }

        .empty-state h3 {
            font-size: 18px;
        }

        .empty-state p {
            font-size: 14px;
        }
    }

    @media (max-width: 360px) {
        .viewer-title {
            font-size: 15px;
        }

        .user-avatar {
            width: 32px !important;
            height: 32px !important;
        }

        .user-name {
            font-size: 12px;
        }

        .user-meta {
            font-size: 9px;
        }

        .post-description {
            font-size: 12px;
        }

        .menu-btn {
            width: 28px;
            height: 28px;
            font-size: 16px;
        }

        .nav-btn {
            width: 28px;
            height: 28px;
            font-size: 14px;
        }
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        :root {
            --primary-color: {{ config('themes.primaryColor') }};
            --secondary-color: {{ config('themes.secondaryColor') }};
            --text-primary-color: {{ config('themes.textPrimaryColor') }};
            --text-secondary-color: {{ config('themes.textSecondaryColor') }};
            --box-background-color: {{ config('themes.boxBackgroundColor') }};
            --primary: {{ config('themes.primaryColor') }};
            --primary-hover: {{ adjustColor(config('themes.primaryColor'), -10, -10, -10) }};
            --bg-primary: {{ config('themes.backgroundImage') }};
            --bg-secondary: {{ config('themes.boxBackgroundColor') }};
            --text-primary: {{ config('themes.textPrimaryColor') }};
            --text-secondary: {{ config('themes.textSecondaryColor') }};
            --border-color: {{ adjustColor(config('themes.boxBackgroundColor'), 20, 20, 20) }};
        }
    }

    /* Utilities */
    .text-muted {
        color: var(--text-secondary) !important;
    }

    .d-none {
        display: none !important;
    }

    .d-block {
        display: block !important;
    }

    .d-flex {
        display: flex !important;
    }

    /* Side Modal */
    .side-modal {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        height: 100%;
        z-index: 9999;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }

    /* RTL Support for Side Modal */
    [dir="rtl"] .side-modal {
        right: auto;
        left: -100%;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .side-modal.active {
        right: 0;
        pointer-events: all;
    }

    [dir="rtl"] .side-modal.active {
        right: auto;
        left: 0;
    }

    .side-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .side-modal.active .side-modal-overlay {
        opacity: 1;
    }

    .side-modal-content {
        position: absolute;
        top: 0;
        right: -100%;
        width: 100%;
        max-width: 480px;
        height: 100%;
        background: white;
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    /* RTL Support for Side Modal Content */
    [dir="rtl"] .side-modal-content {
        right: auto;
        left: -100%;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .side-modal.active .side-modal-content {
        right: 0;
    }

    [dir="rtl"] .side-modal.active .side-modal-content {
        right: auto;
        left: 0;
    }

    .side-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--primary-color);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .side-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .side-modal-close {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.2s;
    }

    .side-modal-close:hover {
        color: var(--text-primary);
    }

    .side-modal-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* Modal List */
    .modal-list {
        padding: 8px 0;
    }

    .modal-user-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        transition: background 0.15s ease;
        cursor: pointer;
        gap: 12px;
        min-height: 56px;
    }

    /* ارتفاع أكبر للتعليقات الطويلة */
    .modal-user-item:has(.modal-comment-text) {
        align-items: flex-start;
        flex-wrap: wrap;
        min-height: 70px;
    }

    .modal-user-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .modal-user-item:last-child {
        border-bottom: none;
    }

    .modal-user-header {
        display: flex;
        align-items: center;
        gap: 10px;
        /*flex: 1;*/
        min-width: 0;
        position: relative;
    }

    .modal-user-avatar {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border-color);
        flex-shrink: 0;
        min-width: 36px;
    }

    /* حجم أكبر للصور في موديل التعليقات */
    .modal-list .modal-user-item:has(.modal-comment-text) .modal-user-avatar {
        width: 44px !important;
        height: 44px !important;
        border-radius: 25px;
        min-width: 44px;
    }

    .modal-user-info {
        /*flex: 1;*/
        min-width: 0;
        max-width: 200px;
        overflow: hidden;
    }

    .modal-user-name {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .modal-user-meta {
        font-size: 11px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .modal-like-info {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        margin-left: auto;
        min-width: 60px;
    }

    .modal-like-icon {
        color: #e4405f;
        font-size: 16px;
        flex-shrink: 0;
    }

    .modal-like-time {
        font-size: 12px;
        white-space: nowrap;
    }

    /* Gift Item Styles */
    .modal-gift-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
        min-height: 60px;
    }

    .modal-gift-item:hover {
        background: var(--hover-bg);
    }

    .modal-gift-item:last-child {
        border-bottom: none;
    }

    .gift-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .gift-img {
        width: 40px;
        height: 40px;
        object-fit: contain;
        border-radius: 8px;
        flex-shrink: 0;
        min-width: 40px;
    }

    .gift-icon {
        font-size: 28px;
        color: #9b59b6;
        min-width: 40px;
    }

    .gift-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
    }

    .gift-name {
        font-weight: 500;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gift-value {
        font-size: 13px;
        color: #f39c12;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .gift-value i {
        font-size: 12px;
    }

    .gift-time {
        font-size: 12px;
        margin-top: 2px;
    }

    .modal-delete-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e4405f;
        font-size: 16px;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .modal-delete-btn:hover {
        background: rgba(228, 64, 95, 0.1);
    }

    .modal-comment-text {
        margin-top: 12px;
        /*margin-left: 70px;*/
        font-size: 15px;
        line-height: 1.4;
        word-wrap: break-word;
        white-space: pre-wrap;
        unicode-bidi: plaintext;
    }

    .modal-comment-text[dir="rtl"] {
        text-align: right !important;
        direction: rtl;
        margin-left: 0;
        /*margin-right: 70px;*/
    }

    .modal-comment-text[dir="ltr"] {
        text-align: left !important;
        direction: ltr;
        /*margin-left: 70px;*/
        /*margin-right: 0;*/
    }

    /* RTL Support for Comment Text */
    [dir="rtl"] .modal-comment-text:not([dir="ltr"]) {
        /*margin-left: 0;*/
        /*margin-right: 70px;*/
    }

    .modal-comment-time {
        margin-top: 6px;
        margin-left: 70px;
        font-size: 12px;
    }

    .modal-comment-time[dir="rtl"] {
        margin-left: 0;
        margin-right: 70px;
    }

    /* RTL Support for Comment Time */
    [dir="rtl"] .modal-comment-time:not([dir="ltr"]) {
        margin-left: 0;
        margin-right: 70px;
    }

    .modal-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        text-align: center;
    }

    .modal-empty i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .modal-empty p {
        font-size: 16px;
        opacity: 0.5;
        margin: 0;
    }

    /* تحسين سرعة التمرير */
    .side-modal-body,
    .moments-feed,
    .viewer-container {
        will-change: scroll-position;
        -webkit-overflow-scrolling: touch;
    }

    /* تسريع الرسومات باستخدام GPU */
    .moment-post,
    .user-avatar,
    .post-media img,
    .post-media video,
    .modal-user-avatar,
    .nav-btn,
    .menu-btn,
    .dropdown-menu,
    .post-dropdown,
    .side-modal-content {
        transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        -webkit-transform: translateZ(0);
        perspective: 1000px;
        -webkit-perspective: 1000px;
    }

    /* تحسين الانتقالات */
    .moment-post,
    .dropdown-menu,
    .post-dropdown,
    .side-modal,
    .side-modal-content,
    .side-modal-overlay {
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }

    .nav-btn,
    .menu-btn,
    .action-btn,
    .modal-delete-btn,
    .side-modal-close {
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .nav-btn:active,
    .menu-btn:active,
    .action-btn:active,
    .modal-delete-btn:active,
    .side-modal-close:active {
        transform: scale(0.95) translateZ(0);
    }

    /* تحسين تحميل الصور */
    .user-avatar,
    .modal-user-avatar,
    .post-media img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    /* Responsive للموديل */
    @media (max-width: 768px) {
        .side-modal-content {
            max-width: 100%;
        }

        .modal-user-avatar {
            width: 44px !important;
            height: 44px !important;
        }

        .modal-user-name {
            font-size: 15px;
        }

        .modal-comment-text {
            /*margin-left: 56px;*/
            font-size: 14px;
        }

        .modal-comment-text[dir="rtl"] {
            /*margin-left: 0;*/
            /*margin-right: 56px;*/
        }

        .modal-comment-time {
            margin-left: 56px;
        }

        .modal-comment-time[dir="rtl"] {
            margin-left: 0;
            margin-right: 56px;
        }
    }

    @media (max-width: 480px) {
        .modal-user-avatar {
            width: 40px !important;
            height: 40px !important;
        }

        .modal-user-name {
            font-size: 14px;
        }

        .modal-user-meta {
            font-size: 12px;
        }

        .modal-comment-text {
            /*margin-left: 52px;*/
            font-size: 14px;
        }

        .modal-comment-text[dir="rtl"] {
            /*margin-left: 0;*/
            /*margin-right: 52px;*/
        }

        .modal-comment-time {
            margin-left: 52px;
        }

        .modal-comment-time[dir="rtl"] {
            margin-left: 0;
            margin-right: 52px;
        }

        .side-modal-header {
            padding: 12px 16px;
        }

        .side-modal-title {
            font-size: 18px;
        }
    }

    /* Main Layout */
    .viewer-layout {
        display: flex;
        min-height: 100vh;
        gap: 0;
    }

    /* Users Sidebar */
    .users-sidebar {
        width: 30%;
        background: #fff;
        border-right: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: sticky;
        top: 0;
    }

    .users-sidebar-header {
        padding: 16px;
        background: var(--gradient-primary);
        color: #fff;
    }

    .users-sidebar-header h3 {
        margin: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .users-search-box {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .users-list-search {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 13px;
        box-sizing: border-box;
    }

    .users-list-search:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .users-filter-info {
        padding: 0 12px 12px;
        display: none;
    }

    .users-filter-info.visible {
        display: block;
    }

    .clear-filter-btn {
        width: 100%;
        padding: 8px;
        background: #ff6b6b;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
    }

    .users-list-container {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
    }

    /* User Item - Vertical List */
    .user-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        margin-bottom: 4px;
        transition: all 0.2s;
    }

    .user-list-item:hover {
        border-color: var(--primary-color);
    }

    .user-list-item.active {
        background: #f0f4ff;
        border-color: var(--primary-color);
    }

    .user-list-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .user-list-info {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .user-list-name {
        font-weight: 600;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-list-meta {
        font-size: 11px;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-list-count {
        background: var(--gradient-primary);
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        flex-shrink: 0;
    }

    .users-load-more {
        padding: 12px;
        border-top: 1px solid #eee;
        display: none;
    }

    .users-load-more.visible {
        display: block;
    }

    .load-more-users-btn {
        width: 100%;
        padding: 8px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
    }

    /* Main Content */
    .viewer-main-content {
        flex: 1;
        min-width: 0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .viewer-layout {
            flex-direction: column;
        }

        .users-sidebar {
            width: 100%;
            min-width: 100%;
            height: auto;
            max-height: 250px;
            position: relative;
        }

        .users-list-container {
            max-height: 150px;
        }
    }

    .users-loading-more {
        padding: 15px;
        text-align: center;
        display: none;
    }

    .users-loading-more.visible {
        display: block;
    }

    .spinner-small {
        width: 24px;
        height: 24px;
        border: 3px solid #e0e0e0;
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .users-sidebar {
            display: none !important;
        }

        .viewer-main-content {
            width: 100% !important;
            flex: 1 !important;
        }
    }

    @media (max-width: 480px) {
        .users-sidebar {
            display: none !important;
        }
    }
    /* Fix SweetAlert2 appearing behind modal */
    .swal2-container {
        z-index: 99999 !important;
    }

    .swal2-popup {
        z-index: 99999 !important;
    }

    /* Ensure the backdrop is also above the modal */
    .swal2-container.swal2-backdrop-show {
        z-index: 99999 !important;
    }
</style>
