function reelsManager() {
    return {
        filteredReels: [],
        visibleReels: [],
        allReels: [],
        likes: [],
        comments: [],
        gifts: [],
        activeTab: 'likes',
        selectedReelId: null,
        selectedReel: null,
        showInteractionPanel: false,
        searchQuery: '',
        isMobileSidebarOpen: false,
        loading: false,
        hasMore: true,
        offset: 0,
        reelsLoaded: false,
        currentVideoIndex: 0,
        loadedVideos: new Set([0]),
        videoProgress: {},
        videoDurations: {},
        videoStates: {},
        videoReadyStates: {},
        isGlobalMuted: false,
        thumbnailGenerating: {},
        scrollingToSelection: false,
        showEditModal: false,
        editingReel: null,
        showDeleteModal: false,
        deletingReel: null,
        scrollRAF: null,
        
        init() {
            this.isGlobalMuted = false;
            
            this.$nextTick(() => {
                if (window.requestIdleCallback) {
                    requestIdleCallback(() => this.loadInitialData());
                } else {
                    setTimeout(() => this.loadInitialData(), 100);
                }
            });
        },
        
        loadInitialData() {
            const reelsData = window.initialReelsData || [];
            
            this.allReels = reelsData.map(reel => {
                return {
                    ...reel,
                    thumbnailLoaded: Boolean(reel.thumbnail_url)
                };
            });
            this.visibleReels = this.allReels.slice(0, 3); // تحميل 3 فيديوهات فقط في البداية
            this.filteredReels = this.allReels.slice(0, 6);
            this.reelsLoaded = true;

            this.offset = this.allReels.length;
            this.hasMore = this.allReels.length >= 10;
            
            // Load first video only
            this.loadedVideos.add(0);
            
            this.isMobileSidebarOpen = false;
            
            this.$nextTick(() => {
                this.playFirstVideo();
                this.setupInfiniteScroll();
                this.setupSidebarScroll();
                
                this.refreshVisibleReelsCounts();

                this.captureMissingThumbnails(this.filteredReels.slice(0, 6));
                
                // تحميل تدريجي بعد التهيئة لتحسين الأداء
                setTimeout(() => {
                    if (this.filteredReels.length < this.allReels.length) {
                        const nextBatch = this.allReels.slice(6, 12);
                        this.filteredReels = [...this.filteredReels, ...nextBatch];
                        this.captureMissingThumbnails(nextBatch);
                        this.refreshVisibleReelsCounts();
                    }
                }, 1000);
                
                // تقليل تردد التحديث لتحسين الأداء
                setInterval(() => {
                    this.refreshVisibleReelsCounts();
                }, 60000);
            });
        },
        
        loadThumbnail(element, reel) {
        },
        
        isVideoReady(reelId) {
            return this.videoReadyStates[reelId] === true;
        },
        
        markVideoReady(reelId) {
            this.videoReadyStates[reelId] = true;
        },
        
        toggleMobileSidebar() {
            this.isMobileSidebarOpen = !this.isMobileSidebarOpen;
        },
        
        closeMobileSidebar() {
            this.isMobileSidebarOpen = false;
        },
        
        onThumbnailLoad(reel, event) {
            reel.thumbnailLoaded = true;
        },
        
        async onThumbnailError(reel, event) {
            // Attempt to regenerate the thumbnail from the video if loading fails
            await this.generateThumbnailFromVideo(reel);
        },
        
        onVideoLoaded(event, reelId) {
            const video = event.target;
            this.markVideoReady(reelId);
            
            // تطبيق حالة mute من الإعدادات العامة
            video.muted = this.isGlobalMuted;

            // السماح بالتشغيل اليدوي فقط
            if (video.autoplay) {
                video.pause();
            }
            
            // تحميل مسبق للفيديو التالي
            const currentIndex = this.visibleReels.findIndex(r => r.id === reelId);
            if (currentIndex >= 0 && currentIndex < this.visibleReels.length - 1) {
                const nextReel = this.visibleReels[currentIndex + 1];
                const nextVideo = document.getElementById('video-' + nextReel.id);
                if (nextVideo && !nextVideo.src) {
                    nextVideo.preload = 'metadata';
                }
            }
        },
        
        shouldLoadVideo(index) {
            const currentIndex = this.currentVideoIndex;
            // تحميل الفيديو الحالي والمجاور
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
            
            if (scrollPercentage >= 75) {
                this.loadMoreReels();
            }
        },
        
        setupInfiniteScroll() {
            const container = this.$refs.reelsContainer;
            if (!container) return;
            
            let scrollTimeout;
            let lastScrollTop = 0;
            
            // تحسين الأداء باستخدام passive listener
            container.addEventListener('scroll', () => {
                const scrollTop = container.scrollTop;
                
                // استدعاء handleScroll مباشرة للتفاعل السريع
                if (Math.abs(scrollTop - lastScrollTop) > 50) {
                    this.handleScroll();
                    lastScrollTop = scrollTop;
                }
                
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    if (this.loading || !this.hasMore) return;
                    
                    const scrollHeight = container.scrollHeight;
                    const clientHeight = container.clientHeight;
                    const scrollPercentage = ((scrollTop + clientHeight) / scrollHeight) * 100;
                    
                    // تحميل أقل تكراراً
                    if (scrollPercentage >= 80) {
                        this.loadMoreReels();
                    }
                }, 200);
            }, { passive: true });
        },
        
        async loadMoreReels() {
            if (this.loading || !this.hasMore) {
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch(`/admin/view/reels/load-more?offset=${this.offset}&limit=20`);
                const data = await response.json();
                
                if (data.reels && data.reels.length > 0) {
                    const existingIds = new Set(this.allReels.map(r => r.id));
                    const newReels = data.reels.filter(reel => !existingIds.has(reel.id));
                    
                    if (newReels.length > 0) {
                        const normalized = newReels.map(reel => ({
                            ...reel,
                            thumbnailLoaded: Boolean(reel.thumbnail_url)
                        }));

                        normalized.forEach(item => {
                            this.allReels.push(item);
                            // إضافة 3 فيديوهات فقط للعرض في كل مرة
                            if (this.visibleReels.length < this.allReels.length) {
                                this.visibleReels.push(item);
                            }
                            if (!this.searchQuery) {
                                this.filteredReels.push(item);
                            }
                        });
                        this.captureMissingThumbnails(this.filteredReels.slice(-newReels.length));
                        
                        if (this.searchQuery) {
                            this.filterReels();
                        }
                    }
                    
                    this.offset += data.reels.length;
                    
                    if (data.reels.length < 20) {
                        this.hasMore = false;
                    }
                } else {
                    this.hasMore = false;
                }
            } catch (error) {
                console.error('خطأ في تحميل الريلز:', error);
            } finally {
                this.loading = false;
            }
        },
        
        playFirstVideo() {
            // تعيين أول ريل كاختيار مبدئي
            this.selectedReelId = this.visibleReels[0]?.id;
            this.selectedReel = this.visibleReels[0] || null;
            
            // تشغيل الفيديو الأول بعد التحميل
            this.$nextTick(() => {
                setTimeout(() => {
                    const firstVideo = document.getElementById('video-' + this.selectedReelId);
                    if (firstVideo && firstVideo.readyState >= 2) {
                        firstVideo.play().catch(err => {
                            console.log('تشغيل تلقائي معطل:', err);
                        });
                    }
                }, 500);
            });
        },
        
        handleScroll() {
            const container = this.$refs.reelsContainer;
            if (!container) return;
            
            const scrollTop = container.scrollTop;
            const screenHeight = window.innerHeight;
            
            const newIndex = Math.round(scrollTop / screenHeight);
            
            if (newIndex !== this.currentVideoIndex) {
                const oldIndex = this.currentVideoIndex;
                this.currentVideoIndex = newIndex;
                
                // تحميل الفيديو الحالي والمجاور
                for (let i = newIndex - 1; i <= newIndex + 1; i++) {
                    if (i >= 0 && i < this.visibleReels.length) {
                        this.loadedVideos.add(i);
                    }
                }
                
                // إيقاف الفيديو القديم فقط
                if (oldIndex !== newIndex && oldIndex >= 0) {
                    const oldVideo = document.getElementById('video-' + this.visibleReels[oldIndex]?.id);
                    if (oldVideo && !oldVideo.paused) {
                        oldVideo.pause();
                        oldVideo.currentTime = 0;
                    }
                }
                
                // تشغيل الفيديو الجديد
                const newVideo = document.getElementById('video-' + this.visibleReels[newIndex]?.id);
                if (newVideo && newVideo.paused && newVideo.readyState >= 2) {
                    newVideo.play().catch(() => {});
                }
                
                // تنظيف الفيديوهات البعيدة جداً
                const farVideos = Array.from(this.loadedVideos).filter(i => Math.abs(i - newIndex) > 5);
                farVideos.forEach(i => {
                    const video = document.getElementById('video-' + this.visibleReels[i]?.id);
                    if (video) {
                        video.pause();
                        video.removeAttribute('src');
                        video.load();
                    }
                    this.loadedVideos.delete(i);
                    delete this.videoReadyStates[this.visibleReels[i]?.id];
                });
            }
        },
        
        updateVisibleVideos(container, screenHeight) {
            const videos = container.querySelectorAll('video');
            let activeVideo = null;
            let activeReelId = null;
            
            videos.forEach((video) => {
                const rect = video.getBoundingClientRect();
                const isInCenter = rect.top >= -100 && rect.bottom <= screenHeight + 100;
                
                if (isInCenter) {
                    const reelId = parseInt(video.id.replace('video-', ''));
                    
                    if (this.selectedReelId !== reelId) {
                        this.selectedReelId = reelId;
                        this.selectedReel = this.visibleReels.find(r => r.id === reelId);
                        
                        const currentIndex = this.visibleReels.findIndex(r => r.id === reelId);
                        const remaining = this.visibleReels.length - currentIndex;
                        
                        if (remaining <= 5 && this.hasMore && !this.loading) {
                            this.loadMoreReels();
                        }
                    }
                    
                    activeVideo = video;
                    activeReelId = reelId;
                    
                    // تشغيل الفيديو فقط إذا كان متوقف ولديه src
                    if (video.src && video.paused && video.readyState >= 2) {
                        video.play().catch(() => {});
                    }
                }
            });
            
            // إيقاف الفيديوهات غير النشطة فقط
            videos.forEach(v => {
                const vId = parseInt(v.id.replace('video-', ''));
                if (vId !== activeReelId && !v.paused) {
                    v.pause();
                }
            });
        },
        
        togglePlay(event) {
            event.stopPropagation();
            const video = event.target;
            
            if (video.paused) {
                video.play().catch(err => {
                    console.log('تعذر تشغيل الفيديو:', err);
                });
            } else {
                video.pause();
            }
        },
        
        onVideoEnded(event) {
            // إعادة تشغيل الفيديو تلقائياً (loop)
            const video = event.target;
            video.currentTime = 0;
            video.play().catch(() => {});
        },

        async captureMissingThumbnails(reels) {
            const batch = Array.isArray(reels) ? reels : [reels];
            for (const reel of batch) {
                if (!reel) continue;
                if (reel.thumbnail_url) continue;
                await this.generateThumbnailFromVideo(reel);
            }
        },

        async generateThumbnailFromVideo(reel) {
            if (!reel || !reel.video_url) {
                return;
            }

            // Avoid double work for the same reel
            if (this.thumbnailGenerating[reel.id]) {
                return;
            }

            this.thumbnailGenerating[reel.id] = true;

            const thumbnail = await this.captureFrameFromVideo(reel.video_url);
            if (thumbnail) {
                reel.thumbnail_url = thumbnail;
                reel.thumbnailLoaded = true;
            } else {
                reel.thumbnail_url = `https://picsum.photos/400/700?random=${reel.id || Math.random()}`;
                reel.thumbnailLoaded = true;
            }

            delete this.thumbnailGenerating[reel.id];
        },

        captureFrameFromVideo(videoUrl) {
            return new Promise(resolve => {
                if (!videoUrl) {
                    return resolve(null);
                }

                const video = document.createElement('video');
                video.crossOrigin = 'anonymous';
                video.src = videoUrl;
                video.muted = true;
                video.playsInline = true;
                video.preload = 'auto';

                const handleError = () => resolve(null);
                video.onerror = handleError;

                video.onloadeddata = () => {
                    if (!video.videoWidth || !video.videoHeight) {
                        return resolve(null);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) {
                        return resolve(null);
                    }

                    try {
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                        resolve(dataUrl);
                    } catch (e) {
                        resolve(null);
                    }
                };

                video.currentTime = 1;
                video.load();
            });
        },
        
        async toggleInteraction(tab, reelId) {
            this.activeTab = tab;
            this.selectedReelId = reelId;
            this.selectedReel = this.filteredReels.find(r => r.id === reelId);
            this.showInteractionPanel = true;
            
            // Load data in background without waiting and refresh counts
            await this.loadTabData();
        },
        
        closeInteractionPanel() {
            this.showInteractionPanel = false;
            // Refresh counts when closing to catch any updates
            if (this.selectedReelId) {
                this.refreshReelCounts(this.selectedReelId);
            }
        },
        
        async refreshReelCounts(reelId) {
            try {
                const response = await fetch(`/admin/view/reels/${reelId}`);
                const data = await response.json();
                
                if (data.reel) {
                    this.updateReelCounts(reelId, data.reel);
                }
            } catch (error) {
                console.error('Error refreshing reel counts:', error);
            }
        },
        
        async refreshVisibleReelsCounts() {
            // Get currently visible reels (current +/- 2)
            const visibleIndexes = [];
            for (let i = Math.max(0, this.currentVideoIndex - 2); 
                 i <= Math.min(this.visibleReels.length - 1, this.currentVideoIndex + 2); 
                 i++) {
                visibleIndexes.push(i);
            }
            
            // Batch update for better performance
            const reelIds = visibleIndexes.map(i => this.visibleReels[i]?.id).filter(Boolean);
            
            if (reelIds.length === 0) return;
            
            try {
                // Fetch counts for multiple reels in one request
                const response = await fetch(`/admin/view/reels/batch-counts`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ reel_ids: reelIds })
                });
                
                if (!response.ok) {
                    // Fallback: update one by one
                    for (const reelId of reelIds.slice(0, 3)) { // Limit to 3 to avoid too many requests
                        await this.refreshReelCounts(reelId);
                    }
                    return;
                }
                
                const data = await response.json();
                
                if (data.reels) {
                    data.reels.forEach(reel => {
                        this.updateReelCounts(reel.id, reel);
                    });
                }
            } catch (error) {
                console.error('Error refreshing visible reels counts:', error);
            }
        },
        
        updateReelCounts(reelId, reelData) {
            // Update in visibleReels (player)
            const visIndex = this.visibleReels.findIndex(r => r.id === reelId);
            if (visIndex !== -1) {
                this.visibleReels[visIndex] = {
                    ...this.visibleReels[visIndex],
                    likes_count: reelData.likes_count || 0,
                    comments_count: reelData.comments_count || 0,
                    gifts_count: reelData.gifts_count || 0,
                    views_count: reelData.views_count || 0
                };
            }

            // Update in filteredReels (sidebar)
            const reelIndex = this.filteredReels.findIndex(r => r.id === reelId);
            if (reelIndex !== -1) {
                this.filteredReels[reelIndex] = {
                    ...this.filteredReels[reelIndex],
                    likes_count: reelData.likes_count || 0,
                    comments_count: reelData.comments_count || 0,
                    gifts_count: reelData.gifts_count || 0,
                    views_count: reelData.views_count || 0
                };
            }
            
            // Update in allReels
            const allReelIndex = this.allReels.findIndex(r => r.id === reelId);
            if (allReelIndex !== -1) {
                this.allReels[allReelIndex] = {
                    ...this.allReels[allReelIndex],
                    likes_count: reelData.likes_count || 0,
                    comments_count: reelData.comments_count || 0,
                    gifts_count: reelData.gifts_count || 0,
                    views_count: reelData.views_count || 0
                };
            }
            
            // Force reactivity
            this.visibleReels = [...this.visibleReels];
            this.filteredReels = [...this.filteredReels];
            this.allReels = [...this.allReels];
        },
        
        async selectReel(reelId) {
            this.scrollingToSelection = true;
            this.pauseAllVideos();
            this.selectedReelId = reelId;
            this.selectedReel = this.visibleReels.find(r => r.id === reelId);

            const targetIndex = this.visibleReels.findIndex(r => r.id === reelId);
            if (targetIndex !== -1) {
                this.currentVideoIndex = targetIndex;
                this.loadedVideos = new Set([
                    Math.max(0, targetIndex - 1),
                    targetIndex,
                    Math.min(this.visibleReels.length - 1, targetIndex + 1)
                ]);
            }

            const reelElement = document.querySelector(`[data-reel-id="${reelId}"]`);
            if (reelElement) {
                const container = this.$refs.reelsContainer;
                if (container) {
                    container.scrollTo({ top: reelElement.offsetTop, behavior: 'auto' });
                } else {
                    reelElement.scrollIntoView({ behavior: 'auto', block: 'center' });
                }
            }

            // Play only the selected video after scroll settles
            this.$nextTick(() => {
                this.playVideoById(reelId);
                // Allow normal scroll autoplay again
                this.scrollingToSelection = false;
            });
        },

        playVideoById(reelId) {
            const video = document.getElementById('video-' + reelId);
            if (!video) return;
            if (!video.src) return;
            video.muted = this.isGlobalMuted;
            if (video.readyState < 2) {
                const onCanPlay = () => {
                    video.removeEventListener('canplay', onCanPlay);
                    video.play().catch(() => {});
                };
                video.addEventListener('canplay', onCanPlay, { once: true });
                video.load();
            } else {
                video.play().catch(() => {});
            }
        },

        pauseAllVideos() {
            const allVideos = document.querySelectorAll('video');
            allVideos.forEach(v => {
                if (!v.paused) {
                    v.pause();
                }
            });
        },
        
        async loadTabData() {
            if (!this.selectedReelId) return;
            
            try {
                const response = await fetch(`/admin/view/reels/${this.selectedReelId}`);
                const data = await response.json();
                
                // Update the selected reel with fresh data
                if (data.reel) {
                    this.selectedReel = data.reel;
                    
                    // Sync counts across lists
                    this.updateReelCounts(this.selectedReelId, data.reel);
                }
                
                this.likes = data.likes || [];
                this.comments = data.comments || [];
                this.gifts = data.gifts || [];
            } catch (error) {
                console.error('Error fetching reel data:', error);
                // Set empty arrays on error
                this.likes = [];
                this.comments = [];
                this.gifts = [];
            }
        },
        
        filterReels() {
            const query = this.searchQuery.toLowerCase().trim();
            if (!query) {
                this.filteredReels = this.allReels;
            } else {
                const matches = this.allReels.filter(reel => {
                    const titleMatch = reel.title.toLowerCase().includes(query);
                    const userNameMatch = reel.user?.name?.toLowerCase().includes(query);
                    const idMatch = reel.id.toString().includes(query);
                    const userIdMatch = reel.user?.id.toString().includes(query);
                    return titleMatch || userNameMatch || idMatch || userIdMatch;
                });

                // عرض النتائج فقط حتى لو فارغة
                this.filteredReels = matches;
            }
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
            this.isGlobalMuted = !this.isGlobalMuted;
            
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
                    
                    if (this.selectedReel && this.selectedReel.id === this.editingReel.id) {
                        this.selectedReel.title = this.editingReel.title;
                        this.selectedReel.description = this.editingReel.description;
                    }
                    
                    // alert('✅ تم تحديث الكابشن بنجاح');
                    this.showEditModal = false;
                } else {
                    // alert('❌ حدث خطأ في التحديث: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error updating reel:', error);
                // alert('❌ حدث خطأ في الاتصال: ' + error.message);
            }
        },
        
        deleteReel(reelId) {
            // Find the reel to delete
            const reel = this.filteredReels.find(r => r.id === reelId);
            if (reel) {
                this.deletingReel = reel;
                this.showDeleteModal = true;
            }
        },
        
        async confirmDelete() {
            if (!this.deletingReel) return;
            
            const reelId = this.deletingReel.id;
            this.showDeleteModal = false;
            
            try {
                const response = await fetch(`/admin/view/reels/${reelId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    this.allReels = this.allReels.filter(r => r.id !== reelId);
                    this.filteredReels = this.filteredReels.filter(r => r.id !== reelId);
                    
                    if (this.selectedReelId === reelId) {
                        this.showInteractionPanel = false;
                        this.selectedReelId = null;
                        this.selectedReel = null;
                    }
                    
                    this.deletingReel = null;
                    
                    // Success notification
                    // alert('✅ تم حذف الريل بنجاح');
                } else {
                    this.deletingReel = null;
                    alert('❌ حدث خطأ في الحذف: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error deleting reel:', error);
                this.deletingReel = null;
                alert('❌ حدث خطأ في الاتصال: ' + error.message);
            }
        }
    }
}
