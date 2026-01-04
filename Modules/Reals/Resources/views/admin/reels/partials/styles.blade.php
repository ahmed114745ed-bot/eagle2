<style>
:root {
    --primary-color: {{ config('themes.primaryColor') }};
    --secondary-color: {{ config('themes.secondaryColor') }};
    --text-primary-color: {{ config('themes.textPrimaryColor') }};
    --text-secondary-color: {{ config('themes.textSecondaryColor') }};
    --box-background-color: {{ config('themes.boxBackgroundColor') }};
    --table-background-color: {{ config('themes.tableBackGroundColor')}};
    --primary-hover-alpha: {{ config('themes.primaryColor')}}33;
    --primary-gradient: linear-gradient(135deg, {{ config('themes.primaryColor') }} 0%, {{ config('themes.secondaryColor') }} 100%);
}

body {
    font-family: 'Cairo', sans-serif;
}

/* Main container adjustment for admin navbar */
.reels-main-container {
    height: calc(100vh - 50px);
    position: relative;
    display: flex;
    gap: 0;
}

.reels-main-container.panel-open {
    gap: 1rem;
}

/* Sidebar adjustments */
.reels-sidebar {
    height: 100%;
    position: relative;
    width: 380px;
    flex-shrink: 0;
}

/* Video container */
.reels-video-container {
    height: 100%;
    flex: 1;
    min-width: 0;
    transition: flex 0.2s ease-in-out;
}

.video-item-height {
    height: 100%;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

/* Interactions Panel - Part of flex layout when open */
.interactions-panel {
    height: 100%;
    width: 0;
    min-width: 0;
    flex-shrink: 0;
    background: white;
    border-left: 1px solid #e5e7eb;
    overflow: hidden;
    position: relative;
    transition: width 0.2s ease-in-out, min-width 0.2s ease-in-out;
}

.interactions-panel.show {
    width: 30%;
    min-width: 400px;
    max-width: 600px;
    overflow-y: auto;
}

/* Show panel as overlay on small/medium screens */
@media (max-width: 1279px) {
    .interactions-panel {
        position: fixed;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 50;
        transform: translateX(100%);
        transition: transform 0.2s ease-in-out;
    }
    
    .interactions-panel.show {
        width: 500px;
        max-width: 90vw;
        transform: translateX(0);
    }
}

/* On XL screens and larger, adjust width only */
@media (min-width: 1280px) {
    .interactions-panel {
        width: 30%;
        min-width: 400px;
        max-width: 600px;
    }
}

/* Tablet (iPad Portrait & Landscape) */
@media (max-width: 1024px) and (min-width: 769px) {
    .reels-sidebar {
        width: 300px;
        margin-top: 50px;
    }
}

/* Mobile & Small Tablets */
@media (max-width: 768px) {
    .reels-main-container {
        height: 100vh;
        flex-direction: column-reverse;
    }
    
    .reels-sidebar {
        display: none;
    }
    
    .reels-video-container {
        width: 100%;
        height: 100vh;
    }
    
    /* Hide desktop sidebar on mobile */
    .reels-sidebar:not(.mobile-sidebar-overlay) {
        display: none !important;
    }
    
    .reels-sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .reels-video-container {
        width: 100%;
        height: 100vh;
    }
    
    /* Mobile Overlay Background */
    .mobile-sidebar-overlay {
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease-in-out;
    }
    
    .mobile-sidebar-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }
    
    /* Mobile Toggle Button */
    .mobile-reels-toggle {
        position: fixed;
        bottom: 120px;
        right: 20px;
        width: 56px;
        height: 56px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 22px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.5), 0 0 0 0 rgba(102, 126, 234, 0.4);
        z-index: 998;
        cursor: pointer;
        transition: all 0.3s ease;
        animation: pulse-button 2s ease-in-out infinite;
        border: 3px solid white;
    }
    
    @keyframes pulse-button {
        0%, 100% {
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }
        50% {
            box-shadow: 0 4px 30px rgba(102, 126, 234, 0.8), 0 0 0 8px rgba(102, 126, 234, 0.2);
        }
    }
    
    .mobile-reels-toggle:active {
        transform: scale(0.95);
    }
    
    .mobile-reels-toggle i {
        transition: transform 0.3s ease;
    }
    
    .mobile-reels-toggle.active {
        animation: none;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .mobile-reels-toggle.active i {
        transform: rotate(180deg);
    }
}

/* Hide mobile elements on desktop */
@media (min-width: 769px) {
    .mobile-reels-toggle,
    .mobile-sidebar-overlay {
        display: none !important;
    }
}

/* User Profile Overlay */
@media (max-width: 768px) {
    .absolute.top-4 {
        top: 0.5rem;
        right: 0.5rem;
        left: 0.5rem;
    }
    
    .absolute.top-4 .w-12.h-12 {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .absolute.top-4 .text-lg {
        font-size: 0.9rem;
    }
    
    .absolute.top-4 .text-sm {
        font-size: 0.75rem;
    }
    
    /* Video Info Overlay */
    .absolute.bottom-20 {
        bottom: 5rem;
        right: 0.5rem;
        left: 0.5rem;
    }
    
    .absolute.bottom-20 .text-xl {
        font-size: 1rem;
    }
    
    .absolute.bottom-20 .text-sm {
        font-size: 0.8rem;
    }
    
    /* Progress Bar */
    .absolute.bottom-4 {
        bottom: 0.5rem;
        left: 0.5rem;
        right: 0.5rem;
    }
    
    /* Interaction Buttons */
    .absolute.left-4.bottom-24 {
        left: 0.5rem;
        bottom: 13.5rem;
        
    }
    
    .w-14.h-14 {
        width: 4.75rem;
        height: 4.75rem;
        margin: 12px 2px;
    }
    
    .w-14.h-14 i {
        font-size: 1rem;
    }
    
    .w-14.h-14 span {
        font-size: 0.7rem;
    }
    
    /* Admin Action Buttons */
    .w-10.h-10 {
        width: 2.25rem;
        height: 2.25rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 480px) {
    .absolute.top-4 .w-12.h-12 {
        width: 2rem;
        height: 2rem;
    }
    
    .absolute.top-4 .text-lg {
        font-size: 0.85rem;
    }
    
    .absolute.bottom-20 .text-xl {
        font-size: 0.9rem;
    }
    
    .w-14.h-14 {
        width: 4.75rem;
        height: 4.75rem;
        margin: 12px 2px;
    }
    
    .w-10.h-10 {
        width: 2rem;
        height: 2rem;
    }
    
    .gap-4 {
        gap: 0.5rem;
    }
}

/* Mobile Reels Button Animation */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

@media (max-width: 768px) {
    .group:hover .animate-float {
        animation: float 2s ease-in-out infinite;
    }
}

/* Skeleton Loader Styles */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
}

/* User Avatar Styles */
.interactions-panel img.rounded-full {
    object-fit: cover;
    background-color: #f3f4f6;
}

/* Flag Image Styles */
.interactions-panel .flag-image,
.interactions-panel img[title] {
    display: inline-block;
    vertical-align: middle;
}

/* User Info Card Hover */
.interactions-panel [style*="background-color: var(--primary-hover-alpha)"]:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
    transition: all 0.2s ease-in-out;
}
    animation: skeleton-loading 1.5s infinite;
}

@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.skeleton-text {
    height: 12px;
    border-radius: 4px;
    margin-bottom: 8px;
}

.skeleton-circle {
    border-radius: 50%;
}

/* Optimize rendering */
.video-item-height {
    content-visibility: auto;
    contain-intrinsic-height: 100vh;
}

/* Fade in animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Alpine cloak */
[x-cloak] { display: none !important; }

</style>