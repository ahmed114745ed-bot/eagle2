
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" as="style">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" media="print" onload="this.media='all'">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
<style>
    /* Remove default padding */
    /* .content-wrapper {
        padding: 0 !important;
        margin-left: 0 !important;
    } */
    
    /* .content {
        padding: 0 !important;
    } */
    
    /* Main container adjustment for admin navbar */
    .reels-main-container {
        height: calc(100vh - 50px);
        position: relative;
    }
    
    /* Sidebar adjustments */
    .reels-sidebar {
        height: 100%;
        position: relative;
        /* z-index: 1000; */
        width: 380px;
        flex-shrink: 0;
    }
    
    /* Video container */
    .reels-video-container {
        height: 100%;
        flex: 1;
    }
    
    .video-item-height {
        height: 100%;
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
        
        /* Mobile Sidebar will be the overlay only */
        
        .reels-sidebar.mobile-open {
            transform: translateX(0);
        }
        
        .reels-video-container {
            width: 100%;
            height: 100vh;
        }
        
        /* Mobile Overlay Background */
        .mobile-sidebar-overlay {
            /* position: fixed; */
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        /* User Profile Overlay */
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
            bottom: 5.5rem;
        }
        
        .w-14.h-14 {
            width: 3rem;
            height: 3rem;
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
            width: 2.75rem;
            height: 2.75rem;
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

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="reels-main-container flex bg-gray-100" x-data="reelsManager()" x-cloak>
    <!-- Mobile Overlay Background -->
    <div class="mobile-sidebar-overlay" 
         :class="{ 'active': isMobileSidebarOpen }"
         @click="closeMobileSidebar()"
         x-show="isMobileSidebarOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>
    
    <!-- Mobile Toggle Button -->
    <button class="mobile-reels-toggle md:hidden" 
            :class="{ 'active': isMobileSidebarOpen }"
            @click="toggleMobileSidebar()">
        <i class="fas" :class="isMobileSidebarOpen ? 'fa-times' : 'fa-list'"></i>
        <!-- Badge for reels count -->
        <span x-show="!isMobileSidebarOpen && filteredReels.length > 0"
              class="absolute -top-1 -left-1 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center"
              x-text="filteredReels.length"></span>
    </button>
    
    <!-- Reels List (Sidebar) -->
    <div class="reels-sidebar bg-white border-l border-gray-200 shadow-lg flex flex-col"
         :class="{ 'mobile-open': isMobileSidebarOpen }">
        <!-- Search Filter -->
        <div class="p-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white border-b relative">
            <!-- Close Button for Mobile -->
            <button @click="closeMobileSidebar()" 
                    class="md:hidden absolute top-3 left-3 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition">
                <i class="fas fa-times"></i>
            </button>
            
            <h2 class="text-lg font-bold mb-3 flex items-center">
                <i class="fas fa-film ml-2"></i>
                قائمة الريلز
            </h2>
            
            <!-- Search Box -->
            <div class="relative">
                <input type="text" 
                       x-model="searchQuery"
                       @input="filterReels()"
                       placeholder="ابحث بالاسم أو المعرف..."
                       class="w-full px-3 py-2 pr-10 rounded-md text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 border border-white/30">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>
            
            <div class="flex items-center justify-between mt-2">
                <p class="text-sm opacity-90" x-text="filteredReels.length + ' ريل'"></p>
                <button @click="loadMoreReels()" 
                        x-show="hasMore && !loading"
                        class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded">
                    <i class="fas fa-sync-alt ml-1"></i>
                    تحديث
                </button>
            </div>
        </div>
        
        <!-- Reels Grid with Scroll -->
        <div class="flex-1 overflow-y-auto bg-gray-50" 
             x-ref="sidebarContainer"
             @scroll="handleSidebarScroll()"
             style="height: calc(100% - 130px);">
            <!-- Skeleton Loader for Initial Load -->
            <template x-if="!reelsLoaded && filteredReels.length === 0">
                <div class="grid grid-cols-3 gap-2 p-2">
                    <template x-for="i in 9" :key="i">
                        <div class="rounded-md overflow-hidden shadow">
                            <div class="relative bg-gray-200 skeleton" style="padding-bottom: 177.78%;"></div>
                        </div>
                    </template>
                </div>
            </template>
            
            <div class="grid grid-cols-3 gap-2 p-2" x-show="reelsLoaded || filteredReels.length > 0">
                <template x-for="reel in filteredReels" :key="reel.id">
                    <div @click="selectReel(reel.id); closeMobileSidebar()" 
                         :class="selectedReelId === reel.id ? 'ring-2 ring-blue-500 shadow-lg' : ''"
                         class="cursor-pointer rounded-md overflow-hidden shadow hover:shadow-md transition relative group fade-in">
                        <div class="relative bg-gray-200" style="padding-bottom: 177.78%; /* 16:9 ratio */">
                            <!-- Skeleton until image loads -->
                            <div class="absolute inset-0 skeleton" x-show="!reel.thumbnailLoaded"></div>
                            
                            <img :src="reel.thumbnail_url" 
                                 :alt="reel.title" 
                                 class="absolute inset-0 w-full h-full object-cover"
                                 x-show="reel.thumbnailLoaded"
                                 x-on:load="onThumbnailLoad(reel, $event)"
                                 x-on:error="onThumbnailError(reel, $event)">
                            
                            <!-- User Info Overlay on Hover -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xs border border-white/50 flex-shrink-0">
                                        <span x-text="reel.user?.name?.charAt(0)"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-xs font-bold truncate" x-text="reel.user?.name"></p>
                                        <p class="text-white/70 text-[10px]" x-text="'ID: ' + reel.user?.id"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-white text-[10px]">
                                    <span class="flex items-center">
                                        <i class="fas fa-eye ml-1"></i>
                                        <span x-text="formatNumber(reel.views_count)"></span>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-heart ml-1"></i>
                                        <span x-text="formatNumber(reel.likes_count)"></span>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-comment ml-1"></i>
                                        <span x-text="formatNumber(reel.comments_count)"></span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-1.5 group-hover:opacity-0 transition-opacity">
                                <p class="text-white text-xs font-semibold truncate" x-text="reel.title"></p>
                                <div class="flex items-center gap-2 text-white text-xs mt-0.5">
                                    <span class="flex items-center">
                                        <i class="fas fa-eye ml-1"></i>
                                        <span x-text="formatNumber(reel.views_count)"></span>
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-heart ml-1"></i>
                                        <span x-text="formatNumber(reel.likes_count)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Sidebar Loading Indicator -->
            <div x-show="loading" class="p-3 text-center">
                <div class="inline-flex items-center space-x-2 space-x-reverse bg-blue-100 px-3 py-2 rounded-md">
                    <div class="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-blue-600"></div>
                    <span class="text-blue-600 text-xs font-semibold">جاري التحميل...</span>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div x-show="hasMore && !loading && filteredReels.length > 0" class="p-2">
                <button @click="loadMoreReels()" 
                        class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold text-sm transition shadow">
                    <i class="fas fa-plus ml-1"></i>
                    تحميل المزيد
                    <span class="text-xs opacity-90 mr-2">
                        (<span x-text="filteredReels.length"></span>)
                    </span>
                </button>
            </div>
            
            <!-- No More Message -->
            <div x-show="!hasMore && filteredReels.length > 0" class="p-3 text-center text-gray-500 text-xs">
                <i class="fas fa-check-circle mb-1 text-green-500"></i>
                <p class="font-semibold">تم تحميل الكل</p>
            </div>
        </div>
    </div>

    <!-- Main Video Player (Center) -->
    <div class="reels-video-container  overflow-y-auto snap-y snap-mandatory scroll-smooth" 
         x-ref="reelsContainer"
         @scroll.passive="handleScroll()">
        <template x-for="(reel, index) in filteredReels" :key="reel.id">
            <div class="video-item-height snap-start flex items-center justify-center relative"
                 :data-reel-id="reel.id"
                 :data-index="index">
                <div class="w-full max-w-2xl h-full relative">
                    <!-- Video Container -->
                    <div class="relative h-full bg-black flex items-center justify-center">
                        <!-- Skeleton Loader while video loading -->
                        <template x-if="!shouldLoadVideo(index)">
                            <div class="w-full h-full flex items-center justify-center bg-gray-900">
                                <div class="text-center text-white/50">
                                    <div class="skeleton skeleton-circle w-20 h-20 mx-auto mb-4 bg-gray-700"></div>
                                    <div class="skeleton skeleton-text w-32 mx-auto bg-gray-700"></div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Lazy load video only when needed -->
                        <template x-if="shouldLoadVideo(index)">
                            <div class="w-full h-full relative">
                                <!-- Loading skeleton while video loads -->
                                <div x-show="!isVideoReady(reel.id)" class="absolute inset-0 flex items-center justify-center bg-gray-900">
                                    <div class="text-center text-white/50">
                                        <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-500 mx-auto mb-4"></div>
                                        <p class="text-sm">جاري تحميل الفيديو...</p>
                                    </div>
                                </div>
                                
                                <video :src="reel.video_url" 
                                       :id="'video-' + reel.id"
                                       class="w-full h-full object-contain"
                                       x-show="isVideoReady(reel.id)"
                                       loop
                                       autoplay
                                       muted
                                       preload="none"
                                       playsinline
                                       x-ref="video"
                                       @click="togglePlay($event)"
                                       @loadedmetadata="updateProgress($event); onVideoLoaded($event, reel.id)"
                                       @canplay="markVideoReady(reel.id)"
                                       @timeupdate.throttle.500ms="updateProgress($event)"
                                       @play="updateProgress($event)"
                                       @pause="updateProgress($event)">
                                </video>
                            </div>
                        </template>
                        
                        <!-- Loading Spinner -->
                        <div class="loading-spinner absolute inset-0 flex items-center justify-center bg-black/50 hidden">
                            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-500"></div>
                        </div>
                        
                        <!-- User Profile Overlay (Top) -->
                        <div class="absolute top-4 right-4 left-4 flex items-center justify-between z-10">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg border-2 border-white shadow-lg" style=" margin: 10px;">
                                    <span x-text="reel.user?.name?.charAt(0)"></span>
                                </div>
                                <div class="mr-3">
                                    <p class="text-white font-bold text-lg drop-shadow-lg" x-text="reel.user?.name"></p>
                                    <p class="text-white/80 text-sm drop-shadow" x-text="'ID: ' + reel.user?.id"></p>
                                </div>
                            </div>
                            
                            <!-- Admin Actions -->
                            <div class="flex gap-2">
                                <button @click.stop="editReel(reel)" 
                                        class="w-10 h-10 bg-blue-500/80 hover:bg-blue-600 backdrop-blur-md rounded-full flex items-center justify-center text-white transition shadow-lg">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click.stop="deleteReel(reel.id)" 
                                        class="w-10 h-10 bg-red-500/80 hover:bg-red-600 backdrop-blur-md rounded-full flex items-center justify-center text-white transition shadow-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Video Info Overlay (Bottom) -->
                        <div class="absolute bottom-20 right-4 left-4 z-10">
                            <h3 class="text-white font-bold text-xl drop-shadow-lg mb-2" x-text="reel.title"></h3>
                            <p class="text-white/90 text-sm drop-shadow-lg" x-text="reel.description"></p>
                            <div class="flex items-center mt-3 text-white text-sm">
                                <i class="fas fa-eye ml-2"></i>
                                <span x-text="formatNumber(reel.views_count) + ' مشاهدة'"></span>
                            </div>
                        </div>

                        <!-- Progress Bar with Mute Button -->
                        <div class="absolute bottom-4 left-4 right-4 z-20">
                            <div class="flex items-center gap-3">
                                <!-- Mute/Unmute Button -->
                                <button @click.stop="toggleMute(reel.id)" 
                                        :class="isMuted(reel.id) ? 'bg-red-500/80' : 'bg-white/20'"
                                        class="w-10 h-10 hover:bg-white/30 rounded-full backdrop-blur-md flex items-center justify-center text-white transition transform hover:scale-110 shadow-xl flex-shrink-0">
                                    <i :class="isMuted(reel.id) ? 'fa-volume-mute' : 'fa-volume-up'" class="fas text-lg"></i>
                                </button>
                                
                                <!-- Progress Bar with Time -->
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-white text-xs mb-1 px-1">
                                        <span x-text="formatTime(getCurrentTime(reel.id))">0:00</span>
                                        <span x-text="formatTime(getDuration(reel.id))">0:00</span>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-sm rounded-full h-2 cursor-pointer" 
                                         @click="seekVideo($event, reel.id)">
                                        <div class="bg-purple-500 h-full rounded-full transition-all duration-100" 
                                             :style="'width: ' + getProgress(reel.id) + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Interaction Buttons (Right Side) -->
                        <div class="absolute left-4 bottom-24 flex flex-col gap-4 z-20 md:left-auto md:right-4">
                            <!-- Likes Button -->
                            <button @click.stop="toggleInteraction('likes', reel.id)"
                                    :class="activeTab === 'likes' && selectedReelId === reel.id ? 'bg-red-500 scale-110' : 'bg-white/20 hover:bg-white/30'"
                                    class="w-14 h-14 rounded-full backdrop-blur-md flex flex-col items-center justify-center text-white transition transform hover:scale-110 shadow-xl">
                                <i class="fas fa-heart text-xl"></i>
                                <span class="text-xs mt-1 font-semibold" x-text="formatNumber(reel.likes_count)"></span>
                            </button>

                            <!-- Comments Button -->
                            <button @click.stop="toggleInteraction('comments', reel.id)"
                                    :class="activeTab === 'comments' && selectedReelId === reel.id ? 'bg-blue-500 scale-110' : 'bg-white/20 hover:bg-white/30'"
                                    class="w-14 h-14 rounded-full backdrop-blur-md flex flex-col items-center justify-center text-white transition transform hover:scale-110 shadow-xl">
                                <i class="fas fa-comment text-xl"></i>
                                <span class="text-xs mt-1 font-semibold" x-text="formatNumber(reel.comments_count)"></span>
                            </button>

                            <!-- Gifts Button -->
                            <button @click.stop="toggleInteraction('gifts', reel.id)"
                                    :class="activeTab === 'gifts' && selectedReelId === reel.id ? 'bg-yellow-500 scale-110' : 'bg-white/20 hover:bg-white/30'"
                                    class="w-14 h-14 rounded-full backdrop-blur-md flex flex-col items-center justify-center text-white transition transform hover:scale-110 shadow-xl">
                                <i class="fas fa-gift text-xl"></i>
                                <span class="text-xs mt-1 font-semibold" x-text="formatNumber(reel.gifts_count)"></span>
                            </button>

                          
                        </div>

                        <!-- Scroll Indicator -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white/60 animate-bounce z-10"
                             x-show="index < filteredReels.length - 1">
                            <i class="fas fa-chevron-down text-2xl"></i>
                        </div>
                        
                        <!-- Loading Indicator -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10"
                             x-show="loading && index === filteredReels.length - 1">
                            <div class="flex items-center space-x-2 space-x-reverse bg-black/70 backdrop-blur-md px-4 py-2 rounded-full">
                                <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-white"></div>
                                <span class="text-white text-sm">جاري التحميل...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- End of List Message -->
        <template x-if="!hasMore && filteredReels.length > 0">
            <div class="h-32 flex items-center justify-center ">
                <div class="text-center text-white/60">
                    <i class="fas fa-check-circle text-3xl mb-2"></i>
                    <p>لا يوجد المزيد من الريلز</p>
                    <p class="text-sm mt-2">إجمالي: <span x-text="filteredReels.length"></span> ريل</p>
                </div>
            </div>
        </template>
        
        <!-- Manual Load More Button -->
        <template x-if="hasMore && filteredReels.length > 0 && !loading">
            <div class="h-32 flex items-center justify-center ">
                <button @click="loadMoreReels()" 
                        class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-full font-semibold transition shadow-lg">
                    <i class="fas fa-arrow-down ml-2"></i>
                    تحميل المزيد
                    <span class="text-sm block mt-1">(<span x-text="filteredReels.length"></span> من 100)</span>
                </button>
            </div>
        </template>
    </div>


    <!-- Interactions Panel (Right Side) - Slide In Panel -->
    <div class=" right-0 top-0 md:top-0 bottom-0 w-full sm:w-[90%] md:w-[500px] lg:w-[600px] bg-white shadow-2xl transform transition-transform duration-300 ease-in-out z-50 max-md:top-[30px]"
         :class="showInteractionPanel ? 'translate-x-0' : 'ltr:translate-x-full rtl:-translate-x-full'"
         x-show="showInteractionPanel"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full">
        
        <!-- Close Button -->
        <button @click="closeInteractionPanel()"
                class="absolute top-4 right-4 ltr:right-4 rtl:left-4 w-10 h-10 sm:w-12 sm:h-12 bg-red-500 hover:bg-red-600 rounded-full flex items-center justify-center text-white shadow-lg z-10 transition">
            <i class="fas fa-times text-lg sm:text-xl"></i>
        </button>

        <!-- Tabs Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-3 sm:p-4 pt-5 sm:pt-6">
            <div class="flex space-x-1 sm:space-x-2 space-x-reverse">
                <button @click="activeTab = 'likes'; loadTabData()" 
                        :class="activeTab === 'likes' ? 'bg-white text-purple-600' : 'bg-white/20 text-white hover:bg-white/30'"
                        class="flex-1 py-2 sm:py-3 px-2 sm:px-4 rounded-lg text-sm sm:text-base font-semibold transition">
                    <i class="fas fa-heart ml-1 sm:ml-2"></i>
                    <span class="hidden sm:inline">الإعجابات</span>
                    <span class="sm:hidden">إعجاب</span>
                    <span x-show="selectedReel" 
                          class="block text-xs sm:text-sm mt-1" 
                          x-text="selectedReel?.likes_count || 0"></span>
                </button>
                
                <button @click="activeTab = 'comments'; loadTabData()" 
                        :class="activeTab === 'comments' ? 'bg-white text-purple-600' : 'bg-white/20 text-white hover:bg-white/30'"
                        class="flex-1 py-2 sm:py-3 px-2 sm:px-4 rounded-lg text-sm sm:text-base font-semibold transition">
                    <i class="fas fa-comment ml-1 sm:ml-2"></i>
                    <span class="hidden sm:inline">التعليقات</span>
                    <span class="sm:hidden">تعليق</span>
                    <span x-show="selectedReel" 
                          class="block text-xs sm:text-sm mt-1" 
                          x-text="selectedReel?.comments_count || 0"></span>
                </button>
                
                <button @click="activeTab = 'gifts'; loadTabData()" 
                        :class="activeTab === 'gifts' ? 'bg-white text-purple-600' : 'bg-white/20 text-white hover:bg-white/30'"
                        class="flex-1 py-2 sm:py-3 px-2 sm:px-4 rounded-lg text-sm sm:text-base font-semibold transition">
                    <i class="fas fa-gift ml-1 sm:ml-2"></i>
                    <span class="hidden sm:inline">الهدايا</span>
                    <span class="sm:hidden">هدية</span>
                    <span x-show="selectedReel" 
                          class="block text-xs sm:text-sm mt-1" 
                          x-text="selectedReel?.gifts_count || 0"></span>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="h-[calc(100vh-120px)] sm:h-[calc(100vh-140px)] overflow-y-auto p-3 sm:p-4">
            <!-- Likes Tab -->
            <div x-show="activeTab === 'likes'">
                <template x-if="likes.length > 0">
                    <div class="space-y-3">
                        <template x-for="like in likes" :key="like.id">
                            <div class="flex items-center p-3 bg-pink-50 rounded-lg hover:bg-pink-100 transition">
                                <div class="w-10 h-10 bg-gradient-to-br from-pink-400 to-red-500 rounded-full flex items-center justify-center text-white font-bold">
                                    <span x-text="like.user?.name?.charAt(0)"></span>
                                </div>
                                <div class="mr-3 flex-1">
                                    <p class="font-semibold text-gray-800" x-text="like.user?.name"></p>
                                    <p class="text-xs text-gray-500">UUID: <span x-text="like.user?.uuid || like.user?.id"></span></p>
                                    <p class="text-xs text-gray-400" x-text="formatDate(like.created_at)"></p>
                                </div>
                                <i class="fas fa-heart text-red-500 text-xl"></i>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="likes.length === 0 && selectedReel">
                    <div class="text-center text-gray-400 py-12">
                        <i class="fas fa-heart text-5xl mb-3 opacity-30"></i>
                        <p>لا توجد إعجابات بعد</p>
                    </div>
                </template>
            </div>

            <!-- Comments Tab -->
            <div x-show="activeTab === 'comments'">
                <template x-if="comments.length > 0">
                    <div class="space-y-3">
                        <template x-for="comment in comments" :key="comment.id">
                            <div class="bg-blue-50 rounded-lg p-4 hover:bg-blue-100 transition">
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                        <span x-text="comment.user?.name?.charAt(0)"></span>
                                    </div>
                                    <div class="mr-3 flex-1">
                                        <p class="font-semibold text-gray-800" x-text="comment.user?.name"></p>
                                        <p class="text-xs text-gray-500">UUID: <span x-text="comment.user?.uuid || comment.user?.id"></span></p>
                                        <p class="text-gray-700 mt-1" x-text="comment.comment"></p>
                                        <p class="text-xs text-gray-400 mt-2" x-text="formatDate(comment.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="comments.length === 0 && selectedReel">
                    <div class="text-center text-gray-400 py-12">
                        <i class="fas fa-comment text-5xl mb-3 opacity-30"></i>
                        <p>لا توجد تعليقات بعد</p>
                    </div>
                </template>
            </div>

            <!-- Gifts Tab -->
            <div x-show="activeTab === 'gifts'">
                <template x-if="gifts.length > 0">
                    <div class="space-y-3">
                        <template x-for="gift in gifts" :key="gift.id">
                            <div class="bg-yellow-50 rounded-lg p-4 hover:bg-yellow-100 transition">
                                <div class="flex items-start gap-3">
                                    <!-- صورة المستخدم -->
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                        <span x-text="gift.user?.name?.charAt(0)"></span>
                                    </div>
                                    
                                    <!-- معلومات المستخدم -->
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800" x-text="gift.user?.name"></p>
                                        <p class="text-xs text-gray-500">UUID: <span x-text="gift.user?.uuid || gift.user?.id"></span></p>
                                        
                                        <!-- معلومات الهدية -->
                                        <div class="mt-2 flex items-center gap-2 p-2 bg-white/70 rounded-lg">
                                            <div class="text-3xl" x-text="getGiftEmoji(gift.gift_type)"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-700" x-text="getGiftName(gift.gift_type)"></p>
                                                <p class="text-xs text-gray-500">النوع: <span x-text="gift.gift_type"></span></p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xl font-bold text-yellow-600" x-text="gift.gift_value"></span>
                                                <p class="text-xs text-gray-500">نقطة</p>
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs text-gray-400 mt-2" x-text="formatDate(gift.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="gifts.length === 0 && selectedReel">
                    <div class="text-center text-gray-400 py-12">
                        <i class="fas fa-gift text-5xl mb-3 opacity-30"></i>
                        <p>لا توجد هدايا بعد</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/50 z-40"
         x-show="showInteractionPanel"
         @click="closeInteractionPanel()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
    
    <!-- Edit Modal -->
    <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
         x-show="showEditModal"
         @click.self="showEditModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6"
             x-show="showEditModal"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-edit text-blue-500 ml-2"></i>
                    تعديل الكابشن
                </h3>
                <button @click="showEditModal = false" 
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition">
                    <i class="fas fa-times text-gray-600"></i>
                </button>
            </div>
            
            <template x-if="editingReel">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">العنوان</label>
                        <input type="text" 
                               x-model="editingReel.title"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                               placeholder="أدخل عنوان الريل...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">الوصف</label>
                        <textarea x-model="editingReel.description"
                                  rows="4"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition resize-none"
                                  placeholder="أدخل وصف الريل..."></textarea>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button @click="updateReelCaption()" 
                                class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-bold transition shadow-lg">
                            <i class="fas fa-check ml-2"></i>
                            حفظ التعديلات
                        </button>
                        <button @click="showEditModal = false" 
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition">
                            <i class="fas fa-times ml-2"></i>
                            إلغاء
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Mobile Reels List Toggle Button -->
    <button @click="toggleMobileSidebar()" 
            class="md:hidden fixed top-16 right-3 z-[60] group">
        <div class="relative">
            <!-- Main Button -->
            <div class="w-14 h-14 bg-gradient-to-br from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 rounded-2xl shadow-xl flex items-center justify-center transform transition-all duration-300 group-hover:scale-110">
                <i class="fas fa-film text-white text-xl"></i>
            </div>
            <!-- Counter Badge -->
            <div class="absolute -top-1 -left-1 min-w-[24px] h-6 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white text-xs font-bold px-1.5" x-text="filteredReels.length"></span>
            </div>
            <!-- Pulse Animation -->
            <div class="absolute inset-0 bg-purple-400 rounded-2xl animate-ping opacity-20"></div>
        </div>
    </button>
    
    <!-- Mobile Sidebar Overlay Background -->
    <div class="md:hidden fixed inset-0 bg-black/50 z-[55] transition-opacity duration-300"
         x-show="isMobileSidebarOpen"
         @click="closeMobileSidebar()"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div class="md:hidden fixed top-0 right-0 bottom-0 w-full sm:w-[90%] max-w-[400px] bg-white shadow-2xl transform transition-transform duration-300 z-[56] overflow-y-auto"
         :class="isMobileSidebarOpen ? 'translate-x-0' : 'translate-x-full'"
         x-show="isMobileSidebarOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">
        
        <div class="p-3 sm:p-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold">
                <i class="fas fa-list ml-2"></i>
                قائمة الريلز
            </h2>
            <button @click="closeMobileSidebar()"
                    class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center">
                <i class="fas fa-times text-lg sm:text-xl"></i>
            </button>
        </div>
        
        <!-- Search Box -->
        <div class="p-3 sm:p-4">
            <div class="relative">
                <input type="text" 
                       x-model="searchQuery"
                       @input="filterReels()"
                       placeholder="ابحث بالاسم أو المعرف..."
                       class="w-full px-3 sm:px-4 py-2 pr-10 text-sm sm:text-base rounded-lg border-2 border-purple-300 focus:border-purple-500 focus:outline-none">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>
            <p class="text-xs sm:text-sm mt-2 text-gray-600" x-text="filteredReels.length + ' ريل'"></p>
        </div>
        
        <!-- Reels Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-2 sm:p-3">
            <template x-for="reel in filteredReels" :key="reel.id">
                <div @click="selectReel(reel.id); closeMobileSidebar()" 
                     :class="selectedReelId === reel.id ? 'ring-2 sm:ring-4 ring-purple-500' : ''"
                     class="cursor-pointer rounded-lg overflow-hidden shadow-md hover:shadow-xl transition relative group">
                    <div class="relative aspect-[9/16] bg-gray-200">
                        <!-- Skeleton Loader -->
                        <div class="absolute inset-0 skeleton" x-show="!reel.thumbnailLoaded"></div>
                        
                        <img :src="reel.thumbnail_url" 
                             :alt="reel.title" 
                             class="absolute inset-0 w-full h-full object-cover"
                             x-show="reel.thumbnailLoaded"
                             x-on:load="onThumbnailLoad(reel, $event)"
                             x-on:error="onThumbnailError(reel, $event)">
                        
                        <!-- User Info Overlay on Hover for Mobile -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-2">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-xs border border-white/50 flex-shrink-0">
                                    <span x-text="reel.user?.name?.charAt(0)"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-xs font-bold truncate" x-text="reel.user?.name"></p>
                                    <p class="text-white/70 text-[10px]" x-text="'ID: ' + reel.user?.id"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-white text-[10px]">
                                <span class="flex items-center">
                                    <i class="fas fa-eye ml-1"></i>
                                    <span x-text="formatNumber(reel.views_count)"></span>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-heart ml-1"></i>
                                    <span x-text="formatNumber(reel.likes_count)"></span>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-comment ml-1"></i>
                                    <span x-text="formatNumber(reel.comments_count)"></span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-1.5 sm:p-2 group-hover:opacity-0 transition-opacity">
                            <p class="text-white text-xs font-semibold truncate" x-text="reel.title"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function reelsManager() {
    return {
        allReels: [],
        filteredReels: [],
        searchQuery: '',
        selectedReelId: null,
        selectedReel: null,
        activeTab: 'likes',
        likes: [],
        comments: [],
        gifts: [],
        showInteractionPanel: false,
        currentVideoElement: null,
        loading: false,
        hasMore: true,
        offset: 10,
        currentVideoIndex: 0,
        loadedVideos: new Set(),
        videoProgress: {},
        isGlobalMuted: false,
        showEditModal: false,
        editingReel: null,
        videoStates: {},
        videoDurations: {},
        isMobileSidebarOpen: false,
        reelsLoaded: false,
        videoReadyStates: {},
        
        init() {
            // تحميل البيانات بشكل تدريجي لتحسين الأداء
            this.isGlobalMuted = false;
            
            // تحميل البيانات بعد رندر الصفحة
            this.$nextTick(() => {
                // استخدام requestIdleCallback لتحميل البيانات في وقت الفراغ
                if (window.requestIdleCallback) {
                    requestIdleCallback(() => this.loadInitialData());
                } else {
                    setTimeout(() => this.loadInitialData(), 100);
                }
            });
        },
        
        loadInitialData() {
            // تحميل البيانات من السيرفر
            const reelsData = @json($reels);
            
            // تحميل أول 6 فقط للبداية السريعة مع تهيئة thumbnailLoaded
            this.allReels = reelsData.map(reel => {
                // تحميل الصورة فوراً لأول 6 ريلز
                const shouldPreload = reelsData.indexOf(reel) < 6;
                return {
                    ...reel,
                    thumbnailLoaded: false
                };
            });
            
            // تحميل تدريجي - أول 6 للبداية
            this.filteredReels = this.allReels.slice(0, 6);
            this.reelsLoaded = true;
            
            // Load first video only
            this.loadedVideos.add(0);
            
            // التأكد من أن القائمة مغلقة والريل مفتوح
            this.isMobileSidebarOpen = false;
            
            this.$nextTick(() => {
                this.playFirstVideo();
                this.setupInfiniteScroll();
                this.setupSidebarScroll();
                
                // تحميل بقية الريلز بعد 500ms
                setTimeout(() => {
                    if (this.filteredReels.length < this.allReels.length) {
                        const nextBatch = this.allReels.slice(6, 15);
                        this.filteredReels = [...this.filteredReels, ...nextBatch];
                    }
                }, 500);
            });
        },
        
        loadThumbnail(element, reel) {
            // يتم تحميل الصورة فقط عندما تكون قريبة من الرؤية
            // x-intersect.once سيتولى هذا تلقائياً
        },
        
        isVideoReady(reelId) {
            return this.videoReadyStates[reelId] === true;
        },
        
        markVideoReady(reelId) {
            this.videoReadyStates[reelId] = true;
        },
        
        toggleMobileSidebar() {
            this.isMobileSidebarOpen = !this.isMobileSidebarOpen;
            console.log('Toggle Mobile Sidebar:', this.isMobileSidebarOpen);
        },
        
        closeMobileSidebar() {
            this.isMobileSidebarOpen = false;
            console.log('Close Mobile Sidebar');
        },
        
        onThumbnailLoad(reel, event) {
            reel.thumbnailLoaded = true;
            console.log('✅ صورة محملة:', reel.id);
        },
        
        onThumbnailError(reel, event) {
            reel.thumbnailLoaded = true; // Show fallback
            console.log('❌ خطأ في تحميل الصورة:', reel.id);
        },
        
        onVideoLoaded(event, reelId) {
            const video = event.target;
            this.markVideoReady(reelId);
            
            // تطبيق حالة الصوت العامة
            video.muted = this.isGlobalMuted;
            
            // تشغيل الفيديو إذا كان مرئياً
            const rect = video.getBoundingClientRect();
            const screenHeight = window.innerHeight;
            
            if (rect.top >= 0 && rect.bottom <= screenHeight) {
                // بدء التشغيل مع mute أولاً
                video.muted = true;
                video.play().then(() => {
                    // بعد بدء التشغيل، تطبيق حالة الصوت العامة
                    setTimeout(() => {
                        video.muted = this.isGlobalMuted;
                    }, 100);
                }).catch(e => console.log('خطأ في التشغيل التلقائي:', e));
            }
        },
        
        shouldLoadVideo(index) {
            // تحميل الفيديو الحالي + 1 قبله + 2 بعده فقط لتوفير الذاكرة
            const currentIndex = this.currentVideoIndex;
            return Math.abs(index - currentIndex) <= 2 || this.loadedVideos.has(index);
        },
        
        setupSidebarScroll() {
            const sidebarContainer = this.$refs.sidebarContainer;
            if (!sidebarContainer) return;
            
            sidebarContainer.addEventListener('scroll', () => {
                this.handleSidebarScroll();
            });
        },
        
        handleSidebarScroll() {
            const container = this.$refs.sidebarContainer;
            if (!container || this.loading || !this.hasMore) return;
            
            const scrollHeight = container.scrollHeight;
            const scrollTop = container.scrollTop;
            const clientHeight = container.clientHeight;
            
            const scrollPercentage = ((scrollTop + clientHeight) / scrollHeight) * 100;
            
            // Load more when user scrolls to 75% of sidebar
            if (scrollPercentage >= 75) {
                console.log('🔽 Sidebar scroll detected:', scrollPercentage.toFixed(0) + '%');
                this.loadMoreReels();
            }
        },
        
        setupInfiniteScroll() {
            const container = this.$refs.reelsContainer;
            if (!container) return;
            
            let scrollTimeout;
            let lastScrollTop = 0;
            
            container.addEventListener('scroll', () => {
                // Debounce scroll event للأداء
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    const scrollTop = container.scrollTop;
                    
                    // تحديث الفيديو الحالي فقط عند التغيير
                    if (Math.abs(scrollTop - lastScrollTop) > 50) {
                        this.handleScroll();
                        lastScrollTop = scrollTop;
                    }
                    
                    if (this.loading || !this.hasMore) return;
                    
                    const scrollHeight = container.scrollHeight;
                    const clientHeight = container.clientHeight;
                    const scrollPercentage = ((scrollTop + clientHeight) / scrollHeight) * 100;
                    
                    // Load more when user is near bottom (70% scrolled)
                    if (scrollPercentage >= 70) {
                        console.log('🔽 Main scroll detected:', scrollPercentage.toFixed(0) + '%');
                        this.loadMoreReels();
                    }
                }, 150); // تحسين الـ debounce للأداء
            }, { passive: true }); // passive للأداء الأفضل
        },
        
        async loadMoreReels() {
            if (this.loading || !this.hasMore) {
                console.log('تم إيقاف التحميل:', { loading: this.loading, hasMore: this.hasMore });
                return;
            }
            
            this.loading = true;
            console.log('🔄 جاري تحميل المزيد من الريلز...', { offset: this.offset, current: this.allReels.length });
            
            try {
                const response = await fetch(`/admin/view/reels/load-more?offset=${this.offset}&limit=20`);
                const data = await response.json();
                
                console.log('✅ تم استلام:', data.reels?.length || 0, 'ريل');
                
                if (data.reels && data.reels.length > 0) {
                    // Get existing IDs to avoid duplicates
                    const existingIds = new Set(this.allReels.map(r => r.id));
                    
                    // Filter out duplicates and add only new reels
                    const newReels = data.reels.filter(reel => !existingIds.has(reel.id));
                    
                    console.log('➕ ريلز جديدة فعلية:', newReels.length, 'من أصل', data.reels.length);
                    
                    if (newReels.length > 0) {
                        // Add new reels using push to maintain reactivity
                        newReels.forEach(reel => {
                            this.allReels.push(reel);
                            if (!this.searchQuery) {
                                this.filteredReels.push(reel);
                            }
                        });
                        
                        // Apply filter if search is active
                        if (this.searchQuery) {
                            this.filterReels();
                        }
                        
                        console.log('📊 إجمالي الريلز:', this.allReels.length, '| المعروضة:', this.filteredReels.length);
                    }
                    
                    this.offset += data.reels.length;
                    
                    // If we got less than requested, no more data
                    if (data.reels.length < 20) {
                        this.hasMore = false;
                        console.log('🏁 تم تحميل جميع الريلز');
                    }
                } else {
                    this.hasMore = false;
                    console.log('⚠️ لم يتم استلام أي بيانات');
                }
            } catch (error) {
                console.error('❌ خطأ في تحميل الريلز:', error);
            } finally {
                this.loading = false;
                console.log('✔️ انتهى التحميل');
            }
        },
        
        playFirstVideo() {
            const firstVideo = document.querySelector('video');
            if (firstVideo) {
                firstVideo.play().catch(() => {});
                this.selectedReelId = this.filteredReels[0]?.id;
            }
        },
        
        handleScroll() {
            const container = this.$refs.reelsContainer;
            const scrollTop = container.scrollTop;
            const screenHeight = window.innerHeight;
            
            // حساب الفيديو الحالي بناءً على scroll position
            const newIndex = Math.round(scrollTop / screenHeight);
            
            if (newIndex !== this.currentVideoIndex) {
                this.currentVideoIndex = newIndex;
                
                // تحميل الفيديوهات المجاورة فقط (تحسين الذاكرة)
                for (let i = newIndex - 1; i <= newIndex + 2; i++) {
                    if (i >= 0 && i < this.filteredReels.length) {
                        this.loadedVideos.add(i);
                    }
                }
                
                // إزالة الفيديوهات البعيدة من الذاكرة
                const farVideos = Array.from(this.loadedVideos).filter(i => Math.abs(i - newIndex) > 5);
                farVideos.forEach(i => {
                    const video = document.getElementById('video-' + this.filteredReels[i]?.id);
                    if (video) {
                        video.src = ''; // تفريغ الفيديو
                        video.load(); // إعادة تحميل (فارغ)
                    }
                    this.loadedVideos.delete(i);
                    delete this.videoReadyStates[this.filteredReels[i]?.id];
                });
                
                console.log('📍 الفيديو:', newIndex, '| محملة:', this.loadedVideos.size);
            }
            
            // إدارة تشغيل الفيديوهات
            const videos = container.querySelectorAll('video');
            let activeVideo = null;
            
            videos.forEach((video, idx) => {
                const rect = video.getBoundingClientRect();
                const isVisible = rect.top >= -100 && rect.bottom <= screenHeight + 100;
                
                if (isVisible) {
                    const reelId = parseInt(video.id.replace('video-', ''));
                    
                    if (this.selectedReelId !== reelId) {
                        this.selectedReelId = reelId;
                        this.selectedReel = this.filteredReels.find(r => r.id === reelId);
                        
                        // Check if we're near the end
                        const currentIndex = this.filteredReels.findIndex(r => r.id === reelId);
                        const remaining = this.filteredReels.length - currentIndex;
                        
                        if (remaining <= 10 && this.hasMore && !this.loading) {
                            console.log('⚡ تحميل تلقائي: بقي', remaining, 'فيديوهات');
                            this.loadMoreReels();
                        }
                    }
                    
                    // تشغيل الفيديو المرئي فقط
                    const isInCenter = rect.top >= -50 && rect.bottom <= screenHeight + 50;
                    if (isInCenter) {
                        activeVideo = video;
                        if (video.paused && video.src) {
                            // تشغيل الفيديو المرئي فوراً مع تطبيق حالة الصوت
                            video.muted = true; // بدء muted للسماح بالتشغيل
                            setTimeout(() => {
                                video.play().then(() => {
                                    // تطبيق حالة الصوت العامة بعد البدء
                                    setTimeout(() => {
                                        video.muted = this.isGlobalMuted;
                                    }, 100);
                                }).catch(e => console.log('خطأ في التشغيل:', e));
                            }, 100);
                        } else if (video.src) {
                            // تطبيق حالة الصوت العامة على الفيديو الحالي
                            video.muted = this.isGlobalMuted;
                        }
                    } else {
                        if (!video.paused) {
                            video.pause();
                        }
                    }
                } else {
                    if (!video.paused) {
                        video.pause();
                    }
                }
            });
            
            // التأكد من إيقاف جميع الفيديوهات ماعدا النشط
            if (activeVideo) {
                videos.forEach(v => {
                    if (v !== activeVideo && !v.paused) {
                        v.pause();
                    }
                });
            }
        },
        
        togglePlay(event) {
            const video = event.target;
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        },
        
        async toggleInteraction(tab, reelId) {
            this.activeTab = tab;
            this.selectedReelId = reelId;
            this.selectedReel = this.filteredReels.find(r => r.id === reelId);
            this.showInteractionPanel = true;
            
            await this.loadTabData();
        },
        
        closeInteractionPanel() {
            this.showInteractionPanel = false;
        },
        
        async selectReel(reelId) {
            this.selectedReelId = reelId;
            this.selectedReel = this.filteredReels.find(r => r.id === reelId);
            
            // Scroll to the reel
            const reelElement = document.querySelector(`[data-reel-id="${reelId}"]`);
            if (reelElement) {
                reelElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        
        async loadTabData() {
            if (!this.selectedReelId) return;
            
            try {
                const response = await fetch(`/admin/view/reels/${this.selectedReelId}`);
                const data = await response.json();
                
                this.selectedReel = data.reel;
                this.likes = data.likes;
                this.comments = data.comments;
                this.gifts = data.gifts;
            } catch (error) {
                console.error('Error fetching reel data:', error);
            }
        },
        
        filterReels() {
            const query = this.searchQuery.toLowerCase().trim();
            
            if (!query) {
                this.filteredReels = this.allReels;
            } else {
                this.filteredReels = this.allReels.filter(reel => {
                    const titleMatch = reel.title.toLowerCase().includes(query);
                    const userNameMatch = reel.user?.name?.toLowerCase().includes(query);
                    const idMatch = reel.id.toString().includes(query);
                    const userIdMatch = reel.user?.id.toString().includes(query);
                    
                    return titleMatch || userNameMatch || idMatch || userIdMatch;
                });
            }
            
            // إعادة تعيين الفيديو الحالي
            this.currentVideoIndex = 0;
            this.loadedVideos = new Set([0, 1, 2]);
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'الآن';
            if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
            if (diffHours < 24) return `منذ ${diffHours} ساعة`;
            if (diffDays < 7) return `منذ ${diffDays} يوم`;
            
            return date.toLocaleDateString('ar-EG');
        },
        
        formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        },
        
        getGiftEmoji(giftType) {
            const emojis = {
                'rose': '🌹',
                'heart': '❤️',
                'diamond': '💎',
                'star': '⭐',
                'fire': '🔥',
                'crown': '👑'
            };
            return emojis[giftType] || '🎁';
        },
        
        getGiftName(giftType) {
            const names = {
                'rose': 'وردة حمراء',
                'heart': 'قلب',
                'diamond': 'ألماسة',
                'star': 'نجمة',
                'fire': 'نار',
                'crown': 'تاج'
            };
            return names[giftType] || 'هدية';
        },
        
        toggleMute(reelId) {
            // تبديل الحالة العامة
            this.isGlobalMuted = !this.isGlobalMuted;
            
            // تطبيق على جميع الفيديوهات المحملة
            const allVideos = document.querySelectorAll('video');
            allVideos.forEach(video => {
                video.muted = this.isGlobalMuted;
            });
        },
        
        isMuted(reelId) {
            return this.isGlobalMuted;
        },
        
        updateProgress(event) {
            const video = event.target;
            const reelId = parseInt(video.id.replace('video-', ''));
            
            if (video.duration) {
                this.videoProgress[reelId] = (video.currentTime / video.duration) * 100;
                this.videoDurations[reelId] = video.duration;
                this.videoStates[reelId] = {
                    currentTime: video.currentTime,
                    duration: video.duration,
                    paused: video.paused
                };
            }
        },
        
        getProgress(reelId) {
            return this.videoProgress[reelId] || 0;
        },
        
        getCurrentTime(reelId) {
            return this.videoStates[reelId]?.currentTime || 0;
        },
        
        getDuration(reelId) {
            return this.videoDurations[reelId] || 0;
        },
        
        formatTime(seconds) {
            if (!seconds || isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },
        
        seekVideo(event, reelId) {
            const video = document.getElementById(`video-${reelId}`);
            if (!video) return;
            
            const rect = event.currentTarget.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const percentage = (clickX / rect.width) * 100;
            
            video.currentTime = (percentage / 100) * video.duration;
        },
        
        skipForward(reelId) {
            const video = document.getElementById(`video-${reelId}`);
            if (!video) return;
            
            video.currentTime = Math.min(video.currentTime + 10, video.duration);
        },
        
        skipBackward(reelId) {
            const video = document.getElementById(`video-${reelId}`);
            if (!video) return;
            
            video.currentTime = Math.max(video.currentTime - 10, 0);
        },
        
        togglePlayPause(reelId) {
            const video = document.getElementById(`video-${reelId}`);
            if (!video) return;
            
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        },
        
        isPlaying(reelId) {
            const video = document.getElementById(`video-${reelId}`);
            return video && !video.paused;
        },
        
        editReel(reel) {
            // إنشاء نسخة من الريل للتعديل
            this.editingReel = { ...reel };
            this.showEditModal = true;
        },
        
        async updateReelCaption() {
            if (!this.editingReel) return;
            
            try {
                const response = await fetch(`/admin/view/reels/${this.editingReel.id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: this.editingReel.title,
                        description: this.editingReel.description
                    })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // تحديث الريل في المصفوفات
                    const reelIndex = this.allReels.findIndex(r => r.id === this.editingReel.id);
                    if (reelIndex !== -1) {
                        this.allReels[reelIndex].title = this.editingReel.title;
                        this.allReels[reelIndex].description = this.editingReel.description;
                    }
                    
                    const filteredReelIndex = this.filteredReels.findIndex(r => r.id === this.editingReel.id);
                    if (filteredReelIndex !== -1) {
                        this.filteredReels[filteredReelIndex].title = this.editingReel.title;
                        this.filteredReels[filteredReelIndex].description = this.editingReel.description;
                    }
                    
                    // تحديث الريل المحدد
                    if (this.selectedReel && this.selectedReel.id === this.editingReel.id) {
                        this.selectedReel.title = this.editingReel.title;
                        this.selectedReel.description = this.editingReel.description;
                    }
                    
                    alert('✅ تم تحديث الكابشن بنجاح');
                    this.showEditModal = false;
                    
                    console.log('✅ Reel updated successfully:', result);
                } else {
                    alert('❌ حدث خطأ في التحديث: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error updating reel:', error);
                alert('❌ حدث خطأ في الاتصال: ' + error.message);
            }
        },
        
        async deleteReel(reelId) {
            if (!confirm('هل أنت متأكد من حذف هذا الريل؟')) return;
            
            try {
                const response = await fetch(`/admin/view/reels/${reelId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Remove from arrays
                    this.allReels = this.allReels.filter(r => r.id !== reelId);
                    this.filteredReels = this.filteredReels.filter(r => r.id !== reelId);
                    
                    // إغلاق الـ interactions panel إذا كان الريل المحذوف محدد
                    if (this.selectedReelId === reelId) {
                        this.showInteractionPanel = false;
                        this.selectedReelId = null;
                        this.selectedReel = null;
                    }
                    
                    alert('✅ تم حذف الريل بنجاح');
                    console.log('✅ Reel deleted successfully:', reelId);
                } else {
                    alert('❌ حدث خطأ في الحذف: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error deleting reel:', error);
                alert('❌ حدث خطأ في الاتصال: ' + error.message);
            }
        }
    }
}
</script>
