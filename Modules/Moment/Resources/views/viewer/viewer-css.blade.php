/* Moment Viewer - Facebook-like Design */
/* استخدام ألوان من config/themes.php */
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
    color: var(--text-primary);
    line-height: 1.34;
    direction: ltr;
}

body[dir="rtl"],
html[dir="rtl"] body {
    direction: rtl;
}

.content-wrapper {
    background: var(--bg-primary) !important;
    min-height: 100vh !important;
    padding-bottom: 40px !important;
}

.content {
    height: auto !important;
    min-height: 100vh !important;
    overflow: visible !important;
}

.viewer-container {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0 80px;
    min-height: 100vh !important;
    height: auto !important;
    overflow: scroll !important;
    display: block !important;
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
    background: var(--bg-secondary);
    padding: 16px 24px;
    margin-bottom: 16px;
    box-shadow: var(--shadow-1);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    max-width: 680px;
    margin-left: auto;
    margin-right: auto;
}

.viewer-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
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
    background: var(--bg-primary);
    color: var(--text-primary);
    transition: all 0.2s;
}

.filter-input:focus,
.search-input:focus {
    outline: none;
    background: var(--bg-secondary);
    border-color: var(--primary);
}

.sort-select {
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 20px;
    font-size: 15px;
    background: var(--bg-primary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
}

.sort-select:hover {
    background: var(--bg-primary);
}

.refresh-btn {
    padding: 8px 16px;
    background: var(--primary-button);
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
    background: var(--primary-hover);
}

/* Feed Container */
.moments-feed {
    width: 100%;
    max-width: 680px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: relative;
    z-index: auto;
}

/* Post Card - Facebook Style */
.moment-post {
    background: var(--bg-primary);
    border-radius: 8px;
    box-shadow: var(--shadow-1);
    border: 1px solid var(--border-color);
    overflow: visible;
    transition: box-shadow 0.2s;
    will-change: transform;
    transform: translateZ(0);
    backface-visibility: hidden;
    position: relative;
    z-index: 1;
}

.moment-post:hover {
    box-shadow: var(--shadow-2);
}

.moment-post:has(.dropdown-menu.show) {
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
    color: var(--text-primary);
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
    color: var(--text-secondary);
    margin-top: 4px;
    line-height: 1.2;
    flex-wrap: wrap;
}

.user-uuid {
    font-size: 12px;
    color: var(--text-secondary);
}

.post-time {
    font-size: 12px;
    color: var(--text-secondary);
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
    color: var(--text-secondary);
    font-size: 20px;
    transition: all 0.2s;
    position: relative;
    z-index: 10;
}

.menu-btn:hover {
    background: var(--bg-primary);
}

.post-menu {
    position: relative;
    z-index: 10;
}

/* Dropdown Menu */
.dropdown-menu {
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
html.rtl .dropdown-menu,
html[dir="rtl"] .dropdown-menu,
[dir="rtl"] .dropdown-menu {
    text-align: right;
    right: auto !important;
    left: auto;
    float: right;
    top: 38px;
}

.dropdown-menu.show {
    display: block;
}

/* إذا لم يكن هناك مساحة على اليمين، اعرضها على اليسار */
@media (max-width: 768px) {
    .dropdown-menu {
        right: 0;
        left: auto;
    }
}

.dropdown-item {
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
}

/* RTL Support for Dropdown Item */
html.rtl .dropdown-item,
html[dir="rtl"] .dropdown-item,
[dir="rtl"] .dropdown-item {
    text-align: right;
    flex-direction: row-reverse;
}

.dropdown-item:hover {
    background: var(--bg-primary);
}

.dropdown-item.delete-item {
    color: #e4405f;
}

.dropdown-item i {
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
    color: var(--text-primary);
    line-height: 1.3333;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin-bottom: 12px;
    unicode-bidi: plaintext;
}

.post-description[dir="rtl"] {
    text-align: right !important;
    direction: rtl;
}

.post-description[dir="ltr"] {
    text-align: left !important;
    direction: ltr;
}

.post-description.collapsed {
    max-height: 80px;
    overflow: hidden;
}

.see-more-btn {
    color: var(--text-secondary);
    font-weight: 600;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-size: 15px;
}

.see-more-btn:hover {
    text-decoration: underline;
}

/* Post Media */
.post-media {
    width: 100%;
    background: #000;
    position: relative;
    overflow: hidden;
    contain: layout style paint;
}

.post-media img,
.post-media video {
    width: 100% !important;
    height: auto !important;
    display: block !important;
    max-height: 600px !important;
    max-width: 100% !important;
    min-height: auto !important;
    min-width: auto !important;
    object-fit: contain !important;
    will-change: transform;
    transform: translateZ(0);
}

.media-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 16px;
    pointer-events: none;
}

.nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #000;
    pointer-events: all;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

.media-counter {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(0, 0, 0, 0.75);
    color: white;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 13px;
    font-weight: 600;
}

.media-item {
    display: none;
}

.media-item.active {
    display: block;
}

/* Post Stats */
.post-stats {
    padding: 12px 16px 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 15px;
    color: var(--text-secondary);
}

.stats-left {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.stats-left:hover {
    text-decoration: underline;
}

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
    color: var(--text-secondary);
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
    color: var(--text-secondary);
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
    color: var(--text-secondary);
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
    color: var(--text-secondary);
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
    .viewer-container {
        padding: 12px 0;
    }

    .viewer-header {
        margin: 0 8px 12px;
        padding: 12px 16px;
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
    }

    .scroll-top {
        bottom: 16px;
        right: 16px;
        width: 48px;
        height: 48px;
    }
    
    .dropdown-menu {
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
    .viewer-container {
        padding: 8px 0;
    }

    .viewer-header {
        margin: 0 4px 8px;
        padding: 10px 12px;
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

    .dropdown-menu {
        min-width: 160px;
        font-size: 13px;
    }

    .dropdown-item {
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
    background: var(--box-background-color);
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
    background: var(--box-background-color);
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
    color: var(--text-secondary);
    font-size: 20px;
    transition: all 0.2s;
}

.side-modal-close:hover {
    background: var(--bg-primary);
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
    padding: 12px 20px;
    transition: background 0.15s ease;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color);
}

.modal-user-item:hover {
    background: var(--bg-primary);
}

.modal-user-item:last-child {
    border-bottom: none;
}

.modal-user-header {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.modal-user-avatar {
    width: 48px !important;
    height: 48px !important;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
    flex-shrink: 0;
}

.modal-user-info {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.modal-user-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-user-meta {
    font-size: 13px;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal-like-icon {
    color: #e4405f;
    font-size: 20px;
    flex-shrink: 0;
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
    margin-left: 60px;
    font-size: 15px;
    color: var(--text-primary);
    line-height: 1.4;
    word-wrap: break-word;
    white-space: pre-wrap;
    unicode-bidi: plaintext;
}

.modal-comment-text[dir="rtl"] {
    text-align: right !important;
    direction: rtl;
    margin-left: 0;
    margin-right: 60px;
}

.modal-comment-text[dir="ltr"] {
    text-align: left !important;
    direction: ltr;
    margin-left: 60px;
    margin-right: 0;
}

/* RTL Support for Comment Text */
[dir="rtl"] .modal-comment-text:not([dir="ltr"]) {
    margin-left: 0;
    margin-right: 60px;
}

.modal-comment-time {
    margin-top: 6px;
    margin-left: 60px;
    font-size: 12px;
    color: var(--text-secondary);
}

.modal-comment-time[dir="rtl"] {
    margin-left: 0;
    margin-right: 60px;
}

/* RTL Support for Comment Time */
[dir="rtl"] .modal-comment-time:not([dir="ltr"]) {
    margin-left: 0;
    margin-right: 60px;
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
    color: var(--text-secondary);
    margin-bottom: 16px;
    opacity: 0.5;
}

.modal-empty p {
    font-size: 16px;
    color: var(--text-secondary);
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
        margin-left: 56px;
        font-size: 14px;
    }
    
    .modal-comment-text[dir="rtl"] {
        margin-left: 0;
        margin-right: 56px;
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
        margin-left: 52px;
        font-size: 14px;
    }
    
    .modal-comment-text[dir="rtl"] {
        margin-left: 0;
        margin-right: 52px;
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

