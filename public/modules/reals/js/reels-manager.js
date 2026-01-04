function reelsManager() {
    return {
        filteredReels: [],
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
        loadedVideos: new Set([0, 1, 2]),
        videoProgress: {},
        videoDurations: {},
        videoStates: {},
        videoReadyStates: {},
        isGlobalMuted: false,
        showEditModal: false,
        editingReel: null,
        showDeleteModal: false,
        deletingReel: null,
        
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
            const reelsData = window.initialReelsData || [];
            
            // تحميل أول 6 فقط للبداية السريعة مع تهيئة thumbnailLoaded
            this.allReels = reelsData.map(reel => {
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
                
                // تحديث الأرقام للريلز الأولية
                this.refreshVisibleReelsCounts();
                
                // تحميل بقية الريلز بعد 500ms
                setTimeout(() => {
                    if (this.filteredReels.length < this.allReels.length) {
                        const nextBatch = this.allReels.slice(6, 15);
                        this.filteredReels = [...this.filteredReels, ...nextBatch];
                        // تحديث أرقام الدفعة الجديدة
                        this.refreshVisibleReelsCounts();
                    }
                }, 500);
                
                // تحديث الأرقام كل 30 ثانية للريلز المرئية
                setInterval(() => {
                    this.refreshVisibleReelsCounts();
                }, 30000);
            });
        },
        
        loadThumbnail(element, reel) {
            // يتم تحميل الصورة فقط عندما تكون قريبة من الرؤية
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
        
        onThumbnailError(reel, event) {
            reel.thumbnailLoaded = true;
        },
        
        onVideoLoaded(event, reelId) {
            const video = event.target;
            this.markVideoReady(reelId);
            
            video.muted = this.isGlobalMuted;
            
            const rect = video.getBoundingClientRect();
            const screenHeight = window.innerHeight;
            
            if (rect.top >= 0 && rect.bottom <= screenHeight) {
                video.muted = true;
                video.play().then(() => {
                    setTimeout(() => {
                        video.muted = this.isGlobalMuted;
                    }, 100);
                }).catch(e => console.log('خطأ في التشغيل التلقائي:', e));
            }
        },
        
        shouldLoadVideo(index) {
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
            
            if (scrollPercentage >= 75) {
                this.loadMoreReels();
            }
        },
        
        setupInfiniteScroll() {
            const container = this.$refs.reelsContainer;
            if (!container) return;
            
            let scrollTimeout;
            let lastScrollTop = 0;
            
            container.addEventListener('scroll', () => {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    const scrollTop = container.scrollTop;
                    
                    if (Math.abs(scrollTop - lastScrollTop) > 50) {
                        this.handleScroll();
                        lastScrollTop = scrollTop;
                    }
                    
                    if (this.loading || !this.hasMore) return;
                    
                    const scrollHeight = container.scrollHeight;
                    const clientHeight = container.clientHeight;
                    const scrollPercentage = ((scrollTop + clientHeight) / scrollHeight) * 100;
                    
                    if (scrollPercentage >= 70) {
                        this.loadMoreReels();
                    }
                }, 150);
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
                        newReels.forEach(reel => {
                            this.allReels.push(reel);
                            if (!this.searchQuery) {
                                this.filteredReels.push(reel);
                            }
                        });
                        
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
            
            const newIndex = Math.round(scrollTop / screenHeight);
            
            if (newIndex !== this.currentVideoIndex) {
                this.currentVideoIndex = newIndex;
                
                for (let i = newIndex - 1; i <= newIndex + 2; i++) {
                    if (i >= 0 && i < this.filteredReels.length) {
                        this.loadedVideos.add(i);
                    }
                }
                
                const farVideos = Array.from(this.loadedVideos).filter(i => Math.abs(i - newIndex) > 5);
                farVideos.forEach(i => {
                    const video = document.getElementById('video-' + this.filteredReels[i]?.id);
                    if (video) {
                        video.src = '';
                        video.load();
                    }
                    this.loadedVideos.delete(i);
                    delete this.videoReadyStates[this.filteredReels[i]?.id];
                });
            }
            
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
                        
                        const currentIndex = this.filteredReels.findIndex(r => r.id === reelId);
                        const remaining = this.filteredReels.length - currentIndex;
                        
                        if (remaining <= 10 && this.hasMore && !this.loading) {
                            this.loadMoreReels();
                        }
                    }
                    
                    const isInCenter = rect.top >= -50 && rect.bottom <= screenHeight + 50;
                    if (isInCenter) {
                        activeVideo = video;
                        if (video.paused && video.src) {
                            video.muted = true;
                            setTimeout(() => {
                                video.play().then(() => {
                                    setTimeout(() => {
                                        video.muted = this.isGlobalMuted;
                                    }, 100);
                                }).catch(e => console.log('خطأ في التشغيل:', e));
                            }, 100);
                        } else if (video.src) {
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
                 i <= Math.min(this.filteredReels.length - 1, this.currentVideoIndex + 2); 
                 i++) {
                visibleIndexes.push(i);
            }
            
            // Batch update for better performance
            const reelIds = visibleIndexes.map(i => this.filteredReels[i]?.id).filter(Boolean);
            
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
            // Update in filteredReels
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
            this.filteredReels = [...this.filteredReels];
            this.allReels = [...this.allReels];
        },
        
        async selectReel(reelId) {
            this.selectedReelId = reelId;
            this.selectedReel = this.filteredReels.find(r => r.id === reelId);
            
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
                
                // Update the selected reel with fresh data
                if (data.reel) {
                    this.selectedReel = data.reel;
                    
                    // Update the reel in the lists with actual counts - REACTIVE UPDATE
                    const reelIndex = this.filteredReels.findIndex(r => r.id === this.selectedReelId);
                    if (reelIndex !== -1) {
                        // Create a new object to trigger reactivity
                        this.filteredReels[reelIndex] = {
                            ...this.filteredReels[reelIndex],
                            likes_count: data.reel.likes_count || 0,
                            comments_count: data.reel.comments_count || 0,
                            gifts_count: data.reel.gifts_count || 0,
                            views_count: data.reel.views_count || 0
                        };
                    }
                    
                    const allReelIndex = this.allReels.findIndex(r => r.id === this.selectedReelId);
                    if (allReelIndex !== -1) {
                        // Create a new object to trigger reactivity
                        this.allReels[allReelIndex] = {
                            ...this.allReels[allReelIndex],
                            likes_count: data.reel.likes_count || 0,
                            comments_count: data.reel.comments_count || 0,
                            gifts_count: data.reel.gifts_count || 0,
                            views_count: data.reel.views_count || 0
                        };
                    }
                    
                    // Force Alpine.js to re-render by updating the arrays
                    this.filteredReels = [...this.filteredReels];
                    this.allReels = [...this.allReels];
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
                this.filteredReels = this.allReels.filter(reel => {
                    const titleMatch = reel.title.toLowerCase().includes(query);
                    const userNameMatch = reel.user?.name?.toLowerCase().includes(query);
                    const idMatch = reel.id.toString().includes(query);
                    const userIdMatch = reel.user?.id.toString().includes(query);
                    
                    return titleMatch || userNameMatch || idMatch || userIdMatch;
                });
            }
            
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
                    
                    alert('✅ تم تحديث الكابشن بنجاح');
                    this.showEditModal = false;
                } else {
                    alert('❌ حدث خطأ في التحديث: ' + (result.message || 'خطأ غير معروف'));
                }
            } catch (error) {
                console.error('❌ Error updating reel:', error);
                alert('❌ حدث خطأ في الاتصال: ' + error.message);
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
                    alert('✅ تم حذف الريل بنجاح');
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
