(function($) {
    'use strict';

    const cfg = window.MomentViewerConfig || {};
    const routes = cfg.routes || {};
    const texts = cfg.texts || {};
    const adminUserUrl = cfg.adminUserUrl || '';
    const defaultAvatar = cfg.defaultAvatar || '';
    const storageUrl = cfg.storageUrl || '';
    const csrf = cfg.csrf || '';

    let currentPage = 1;
    let currentSort = 'newest';
    let totalPages = 1;
    let searchQuery = '';
    let userIdFilter = '';
    let isLoading = false;
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const perPage = isMobile ? 5 : 10; // تحميل أقل على الموبايل

    $(document).ready(function() {
        // اكتشاف اللغة وتطبيق الاتجاه
        detectAndApplyDirection();
        
        loadMoments();
        setupEventHandlers();
        setupScrollHandling();
    });

    function detectAndApplyDirection() {
        // تحقق من عدة مصادر لتحديد اللغة
        const htmlLang = document.documentElement.lang || '';
        const htmlDir = document.documentElement.getAttribute('dir') || '';
        const bodyDir = document.body.getAttribute('dir') || '';
        const browserLang = navigator.language || navigator.userLanguage || 'en';
        
        // إذا كان dir محدد بالفعل، استخدمه
        if (htmlDir === 'rtl' || bodyDir === 'rtl') {
            document.documentElement.setAttribute('dir', 'rtl');
            document.body.setAttribute('dir', 'rtl');
            $('html').addClass('rtl');
            return;
        }
        
        // تحقق من اللغة
        const isRTL = htmlLang.startsWith('ar') || 
                      htmlLang.startsWith('he') || 
                      htmlLang.startsWith('fa') ||
                      browserLang.startsWith('ar') ||
                      browserLang.startsWith('he') ||
                      browserLang.startsWith('fa') ||
                      // تحقق من محتوى النصوص في الصفحة
                      checkPageTextDirection();
        
        if (isRTL) {
            document.documentElement.setAttribute('dir', 'rtl');
            document.body.setAttribute('dir', 'rtl');
            $('html').addClass('rtl');
            $('.viewer-container').attr('dir', 'rtl');
        } else {
            document.documentElement.setAttribute('dir', 'ltr');
            document.body.setAttribute('dir', 'ltr');
            $('html').removeClass('rtl');
            $('.viewer-container').attr('dir', 'ltr');
        }
    }
    
    function checkPageTextDirection() {
        // تحقق من نصوص الأزرار والعناوين
        const sampleTexts = [
            cfg.texts?.comments || '',
            cfg.texts?.likes || '',
            cfg.texts?.editDesc || '',
            cfg.texts?.deleteMoment || ''
        ].join(' ');
        
        const rtlChars = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\u0590-\u05FF]/;
        return rtlChars.test(sampleTexts);
    }

    function setupEventHandlers() {
        // فلتر بالمعرف
        let filterTimeout;
        $('#userIdFilter').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                userIdFilter = $('#userIdFilter').val().trim();
                currentPage = 1;
                loadMoments();
            }, 500);
        });

        // بحث بالاسم أو UUID
        let searchTimeout;
        $('#userSearch').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchQuery = $('#userSearch').val().trim();
                currentPage = 1;
                loadMoments();
            }, 500);
        });

        // الترتيب
        $('#sortSelect').on('change', function() {
            currentSort = $(this).val();
            currentPage = 1;
            if (currentSort === 'random' && routes.resetRandom) {
                $.post(routes.resetRandom, {_token: csrf}).always(() => loadMoments());
            } else {
                loadMoments();
            }
        });

        // تحديث
        $('#refreshBtn').on('click', function() {
            const icon = $(this).find('i');
            icon.addClass('fa-spin');
            currentPage = 1;
            loadMoments().always(() => {
                setTimeout(() => icon.removeClass('fa-spin'), 400);
            });
        });

        // زر تحميل المزيد
        $('#loadMoreBtn').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                loadMoments(true);
            }
        });

        // زر العودة للأعلى
        $('#scrollTopBtn').on('click', scrollToTop);

        // إغلاق القوائم المنسدلة عند النقر خارجها
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.post-menu').length) {
                $('.dropdown-menu').removeClass('show');
            }
        });
    }

    function setupScrollHandling() {
        let ticking = false;
        let lastScrollTime = 0;
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const scrollDelay = isMobile ? 200 : 100; // تأخير أكبر على الموبايل
        
        // دعم التمرير من عدة عناصر
        const scrollElements = [
            $('.content-wrapper'),
            $(window),
            $(document)
        ];
        
        scrollElements.forEach(el => {
            if (el.length) {
                el.on('scroll', function() {
                    const now = Date.now();
                    if (now - lastScrollTime < scrollDelay) return; // تجاهل الـ scroll السريع
                    
                    if (!ticking) {
                        window.requestAnimationFrame(function() {
                            handleScroll(el);
                            ticking = false;
                            lastScrollTime = now;
                        });
                        ticking = true;
                    }
                });
            }
        });
        
        // تحقق دوري من الموقع - أطول على الموبايل
        const checkInterval = isMobile ? 2000 : 1000;
        setInterval(() => {
            if (!isLoading) {
                const scrollEl = $('.content-wrapper').length ? $('.content-wrapper') : $(window);
                handleScroll(scrollEl);
            }
        }, checkInterval);
    }

    function handleScroll(scrollEl) {
        const scrollTop = scrollEl.scrollTop();
        const scrollHeight = scrollEl[0]?.scrollHeight || $(document).height();
        const clientHeight = scrollEl.height();

        // إظهار/إخفاء زر العودة للأعلى
        if (scrollTop > 300) {
            $('#scrollTopBtn').addClass('visible');
        } else {
            $('#scrollTopBtn').removeClass('visible');
        }

        // تحميل المزيد عند الوصول لأسفل الصفحة
        const distanceFromBottom = scrollHeight - (scrollTop + clientHeight);
        console.log('Scroll:', { distanceFromBottom, isLoading, currentPage, totalPages });
        if (distanceFromBottom < 300 && !isLoading && currentPage < totalPages) {
            console.log('Loading next page:', currentPage + 1);
            currentPage++;
            loadMoments(true);
        }
    }

    function scrollToTop() {
        const scrollEl = $('.content-wrapper').length ? $('.content-wrapper') : $('html, body');
        scrollEl.animate({ scrollTop: 0 }, 300);
    }

    function loadMoments(append = false) {
        if (isLoading) return $.Deferred().resolve();
        
        const feed = $('#momentsFeed');
        if (!append) {
            feed.html('<div class="loading-container"><div class="spinner"></div></div>');
        } else {
            feed.append('<div id="feed-loading" class="loading-container"><div class="spinner"></div></div>');
        }
        
        isLoading = true;
        const loadMoreContainer = $('#loadMoreContainer');
        loadMoreContainer.hide();

        return $.ajax({
            url: routes.moments,
            type: 'GET',
            data: { 
                sort: currentSort, 
                page: currentPage, 
                per_page: perPage, 
                search: searchQuery,
                user_id: userIdFilter 
            },
            success: function(response) {
                if (response.success) {
                    renderMoments(response.data, append);
                    if (response.pagination) {
                        updatePagination(response.pagination);
                    }
                } else {
                    showError(texts.noData || 'No data returned');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || xhr.statusText || 'Unknown error';
                showError((texts.failedLoad || 'Failed to load moments') + ': ' + message);
            },
            complete: function() {
                isLoading = false;
                $('#feed-loading').remove();
            }
        });
    }

    function renderMoments(moments, append = false) {
        const feed = $('#momentsFeed');
        
        if (!moments || moments.length === 0) {
            if (append) {
                totalPages = currentPage;
                $('#loadMoreContainer').hide();
                return;
            }
            
            feed.html(`
                <div class="empty-state">
                    <i class="fas fa-photo-video"></i>
                    <h3>${texts.noMoments || 'No Moments Found'}</h3>
                    <p>${texts.noMomentsMsg || 'There are no moments to display'}</p>
                    ${(searchQuery || userIdFilter) ? `
                        <button class="refresh-btn" onclick="clearFilters()" style="margin-top: 20px;">
                            <i class="fas fa-times"></i> ${texts.clearSearch || 'Clear Filters'}
                        </button>
                    ` : ''}
                </div>
            `);
            $('#loadMoreContainer').hide();
            return;
        }

        let html = '';
        moments.forEach(moment => {
            html += renderMomentCard(moment);
        });

        if (append) {
            feed.append(html);
        } else {
            feed.html(html);
        }
    }

    function renderMomentCard(moment) {
        const user = moment.user || {};
        const avatar = getUserAvatar(user);
        const userName = user.name || 'Unknown User';
        const userUuid = user.uuid || 'N/A';
        const userId = moment.user_id;
        const userUrl = adminUserUrl + userId;
        
        // فلترة الميديا وإزالة الفارغة
        const images = moment.images || [];
        const allMedia = images.length > 0 ? images : (moment.img ? [{image: moment.img}] : []);
        const validMedia = allMedia.filter(media => media && media.image && media.image.trim() !== '');
        
        const createdAt = new Date(moment.created_at);
        const timeAgo = getTimeAgo(createdAt);

        return `
            <div class="moment-post" data-moment-id="${moment.id}">
                <div class="post-header">
                    <img src="${avatar}" alt="${userName}" class="user-avatar" loading="lazy" 
                         onclick="window.open('${userUrl}', '_blank')">
                    <div class="user-info">
                        <div class="user-name" onclick="window.open('${userUrl}', '_blank')">
                            ${escapeHtml(userName)}
                        </div>
                        <div class="user-meta">
                            <span class="user-uuid">ID: ${userId} • UUID: ${userUuid}</span>
                            <span class="post-time"> • ${timeAgo}</span>
                        </div>
                    </div>
                    <div class="post-menu">
                        <button class="menu-btn" onclick="toggleMenu(${moment.id}, event)">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <div class="dropdown-menu" id="menu-${moment.id}">
                            <button class="dropdown-item" onclick="editMoment(${moment.id}, event)">
                                <i class="fas fa-edit"></i>
                                <span>${texts.editDesc || 'Edit Description'}</span>
                            </button>
                            <button class="dropdown-item delete-item" onclick="deleteMoment(${moment.id}, event)">
                                <i class="fas fa-trash"></i>
                                <span>${texts.deleteMoment || 'Delete Moment'}</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                ${moment.description ? `
                    <div class="post-content">
                        <div class="post-description" id="desc-${moment.id}" dir="${detectTextDirection(moment.description)}" style="text-align: ${detectTextDirection(moment.description) === 'rtl' ? 'right' : 'left'};">${escapeHtml(moment.description)}</div>
                    </div>
                ` : ''}
                
                ${validMedia && validMedia.length > 0 ? renderMedia(moment.id, validMedia) : ''}
                
                <div class="post-stats">
                    <div class="stats-left" onclick="toggleLikes(${moment.id}, event)">
                        ${moment.likes_count > 0 ? `
                            <div class="like-icon">
                                <i class="fas fa-heart"></i>
                                <span>${moment.likes_count}</span>
                            </div>
                        ` : '<span></span>'}
                    </div>
                    <div class="stats-right">
                        ${moment.comments_count > 0 ? `
                            <span onclick="toggleComments(${moment.id}, event)">${moment.comments_count} ${texts.comments || 'comments'}</span>
                        ` : ''}
                    </div>
                </div>
                
                <div class="post-actions">
                    <button class="action-btn" onclick="toggleComments(${moment.id}, event)">
                        <i class="far fa-comment"></i>
                        <span>${texts.comment || 'Comment'}</span>
                    </button>
                </div>
                
                <div class="likes-section" id="likes-${moment.id}"></div>
                <div class="comments-section" id="comments-${moment.id}"></div>
            </div>
        `;
    }

    function renderMedia(momentId, allMedia) {
        if (!allMedia || allMedia.length === 0) return '';
        
        // فلترة الوسائط لإزالة العناصر الفارغة
        const validMedia = allMedia.filter(media => media && media.image && media.image.trim() !== '');
        
        if (validMedia.length === 0) return '';
        
        let mediaHtml = '<div class="post-media"><div class="media-gallery" id="gallery-' + momentId + '">';
        
        validMedia.forEach((media, index) => {
            const mediaPath = getImagePath(media.image);
            const isVideo = mediaPath && (mediaPath.includes('.mp4') || mediaPath.includes('.mov') || mediaPath.includes('.webm'));
            
            mediaHtml += `
                <div class="media-item ${index === 0 ? 'active' : ''}" data-index="${index}">
                    ${isVideo ? 
                        `<video src="${mediaPath}" controls preload="metadata"></video>` : 
                        `<img src="${mediaPath}" alt="Moment" loading="lazy" 
                             onerror="this.style.display='none'">`
                    }
                </div>
            `;
        });
        
        mediaHtml += '</div>';
        
        if (validMedia.length > 1) {
            mediaHtml += `
                <div class="media-counter">${validMedia.length} <i class="fas fa-images"></i></div>
                <div class="media-navigation">
                    <button class="nav-btn" onclick="navigateGallery(${momentId}, -1, event)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-btn" onclick="navigateGallery(${momentId}, 1, event)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            `;
        }
        
        mediaHtml += '</div>';
        return mediaHtml;
    }

    function updatePagination(pagination) {
        totalPages = pagination.last_page || 1;
        currentPage = pagination.current_page || 1;
        
        const loadMoreContainer = $('#loadMoreContainer');
        if (currentPage < totalPages) {
            loadMoreContainer.show();
        } else {
            loadMoreContainer.hide();
        }
    }

    function showError(message) {
        $('#momentsFeed').html(`
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle" style="color: #e4405f;"></i>
                <h3>${texts.error || 'Error'}</h3>
                <p>${message}</p>
                <button class="refresh-btn" onclick="retryLoad()" style="margin-top: 20px;">
                    <i class="fas fa-sync-alt"></i> ${texts.tryAgain || 'Try Again'}
                </button>
            </div>
        `);
    }

    // Global Functions
    window.toggleMenu = function(momentId, event) {
        if (event) event.stopPropagation();
        const menu = $(`#menu-${momentId}`);
        $('.dropdown-menu').not(menu).removeClass('show');
        menu.toggleClass('show');
    };

    window.navigateGallery = function(momentId, direction, event) {
        if (event) event.stopPropagation();
        const gallery = $(`#gallery-${momentId}`);
        const items = gallery.find('.media-item');
        const currentIndex = items.filter('.active').data('index');
        let newIndex = currentIndex + direction;
        
        if (newIndex < 0) newIndex = items.length - 1;
        if (newIndex >= items.length) newIndex = 0;
        
        items.removeClass('active');
        items.eq(newIndex).addClass('active');
    };

    window.toggleComments = function(momentId, event) {
        if (event) event.stopPropagation();
        openSideModal('comments', momentId);
    };

    window.toggleLikes = function(momentId, event) {
        if (event) event.stopPropagation();
        openSideModal('likes', momentId);
    };

    function openSideModal(type, momentId) {
        const modalId = 'sideModal';
        let modal = $(`#${modalId}`);
        
        // إنشاء الموديل إذا لم يكن موجوداً
        if (modal.length === 0) {
            $('body').append(`
                <div id="${modalId}" class="side-modal">
                    <div class="side-modal-overlay"></div>
                    <div class="side-modal-content">
                        <div class="side-modal-header">
                            <h3 class="side-modal-title"></h3>
                            <button class="side-modal-close" onclick="closeSideModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="side-modal-body"></div>
                    </div>
                </div>
            `);
            modal = $(`#${modalId}`);
            
            // إغلاق عند النقر على الخلفية
            modal.find('.side-modal-overlay').on('click', closeSideModal);
        }
        
        // تحديث العنوان والمحتوى
        const title = type === 'comments' 
            ? (texts.comments || 'Comments') 
            : (texts.likes || 'Likes');
        
        modal.find('.side-modal-title').text(title);
        modal.find('.side-modal-body').html('<div class="loading-container"><div class="spinner"></div></div>');
        
        // فتح الموديل
        modal.addClass('active');
        $('body').addClass('modal-open');
        
        // تحميل البيانات
        if (type === 'comments') {
            loadCommentsInModal(momentId);
        } else {
            loadLikesInModal(momentId);
        }
    }

    window.closeSideModal = function() {
        $('#sideModal').removeClass('active');
        $('body').removeClass('modal-open');
    };

    let modalCommentsPage = 1;
    let modalCommentsTotalPages = 1;
    let modalCommentsLoading = false;
    let currentModalMomentId = null;
    let currentModalType = null;

    function loadCommentsInModal(momentId, page = 1, append = false) {
        if (modalCommentsLoading) return;
        
        const url = routes.comments.replace(':id', momentId);
        const container = $('#sideModal .side-modal-body');
        
        if (!append) {
            currentModalMomentId = momentId;
            currentModalType = 'comments';
            modalCommentsPage = 1;
            container.html('<div class="loading-container"><div class="spinner"></div></div>');
        } else {
            container.find('.modal-list').append('<div class="modal-loading"><div class="spinner"></div></div>');
        }
        
        modalCommentsLoading = true;
        
        $.ajax({
            url: url,
            type: 'GET',
            data: { page: page, per_page: 20 },
            success: function(response) {
                if (response.success) {
                    if (response.pagination) {
                        modalCommentsPage = response.pagination.current_page;
                        modalCommentsTotalPages = response.pagination.last_page;
                    }
                    
                    if (response.data.length > 0) {
                        let html = '';
                        response.data.forEach(comment => {
                            const user = comment.user || {};
                            const avatar = user.profile?.avatar ? getImagePath(user.profile.avatar) : defaultAvatar;
                            const userName = user.name || 'Unknown';
                            const userUuid = user.uuid || '';
                            const userId = user.id || '';
                            const userUrl = adminUserUrl + userId;
                            const timeAgo = getTimeAgo(new Date(comment.created_at));
                            const commentDir = detectTextDirection(comment.comment);
                            
                            html += `
                                <div class="modal-user-item">
                                    <div class="modal-user-header" onclick="window.open('${userUrl}', '_blank')">
                                        <img src="${avatar}" alt="${escapeHtml(userName)}" class="modal-user-avatar" loading="lazy">
                                        <div class="modal-user-info">
                                            <div class="modal-user-name">${escapeHtml(userName)}</div>
                                            <div class="modal-user-meta">ID: ${userId}${userUuid ? ' • ' + userUuid : ''}</div>
                                        </div>
                                        <button class="modal-delete-btn" onclick="event.stopPropagation(); deleteCommentFromModal(${comment.id}, ${momentId}, event)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="modal-comment-text" dir="${commentDir}" style="text-align: ${commentDir === 'rtl' ? 'right' : 'left'};">${escapeHtml(comment.comment)}</div>
                                    <div class="modal-comment-time">${timeAgo}</div>
                                </div>
                            `;
                        });
                        
                        if (append) {
                            container.find('.modal-loading').remove();
                            container.find('.modal-list').append(html);
                        } else {
                            container.html('<div class="modal-list">' + html + '</div>');
                            setupModalScrolling();
                        }
                    } else if (!append) {
                        container.html(`
                            <div class="modal-empty">
                                <i class="fas fa-comment-slash"></i>
                                <p>${texts.noComments || 'No comments yet'}</p>
                            </div>
                        `);
                    }
                }
            },
            error: function() {
                if (append) {
                    container.find('.modal-loading').remove();
                } else {
                    container.html(`
                        <div class="modal-empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p style="color: #e4405f;">${texts.failComments || 'Failed to load comments'}</p>
                        </div>
                    `);
                }
            },
            complete: function() {
                modalCommentsLoading = false;
            }
        });
    }

    let modalLikesPage = 1;
    let modalLikesTotalPages = 1;
    let modalLikesLoading = false;

    function loadLikesInModal(momentId, page = 1, append = false) {
        if (modalLikesLoading) return;
        
        const url = routes.likes.replace(':id', momentId);
        const container = $('#sideModal .side-modal-body');
        
        if (!append) {
            currentModalMomentId = momentId;
            currentModalType = 'likes';
            modalLikesPage = 1;
            container.html('<div class="loading-container"><div class="spinner"></div></div>');
        } else {
            container.find('.modal-list').append('<div class="modal-loading"><div class="spinner"></div></div>');
        }
        
        modalLikesLoading = true;
        
        $.ajax({
            url: url,
            type: 'GET',
            data: { page: page, per_page: 20 },
            success: function(response) {
                if (response.success) {
                    if (response.pagination) {
                        modalLikesPage = response.pagination.current_page;
                        modalLikesTotalPages = response.pagination.last_page;
                    }
                    
                    if (response.data.length > 0) {
                        let html = '';
                        response.data.forEach(like => {
                            const user = like.user || {};
                            const avatar = user.profile?.avatar ? getImagePath(user.profile.avatar) : defaultAvatar;
                            const userName = user.name || 'Unknown';
                            const userUuid = user.uuid || '';
                            const userId = user.id || '';
                            const userUrl = adminUserUrl + userId;
                            
                            html += `
                                <div class="modal-user-item" onclick="window.open('${userUrl}', '_blank')">
                                    <img src="${avatar}" alt="${escapeHtml(userName)}" class="modal-user-avatar" loading="lazy">
                                    <div class="modal-user-info">
                                        <div class="modal-user-name">${escapeHtml(userName)}</div>
                                        <div class="modal-user-meta">ID: ${userId}${userUuid ? ' • ' + userUuid : ''}</div>
                                    </div>
                                    <i class="fas fa-heart modal-like-icon"></i>
                                </div>
                            `;
                        });
                        
                        if (append) {
                            container.find('.modal-loading').remove();
                            container.find('.modal-list').append(html);
                        } else {
                            container.html('<div class="modal-list">' + html + '</div>');
                            setupModalScrolling();
                        }
                    } else if (!append) {
                        container.html(`
                            <div class="modal-empty">
                                <i class="fas fa-heart-broken"></i>
                                <p>${texts.noLikes || 'No likes yet'}</p>
                            </div>
                        `);
                    }
                }
            },
            error: function() {
                if (append) {
                    container.find('.modal-loading').remove();
                } else {
                    container.html(`
                        <div class="modal-empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p style="color: #e4405f;">${texts.failLikes || 'Failed to load likes'}</p>
                        </div>
                    `);
                }
            },
            complete: function() {
                modalLikesLoading = false;
            }
        });
    }

    function setupModalScrolling() {
        const modalBody = $('#sideModal .side-modal-body');
        
        modalBody.off('scroll').on('scroll', function() {
            const scrollTop = $(this).scrollTop();
            const scrollHeight = this.scrollHeight;
            const clientHeight = $(this).height();
            const distanceFromBottom = scrollHeight - (scrollTop + clientHeight);
            
            if (distanceFromBottom < 100) {
                if (currentModalType === 'comments' && modalCommentsPage < modalCommentsTotalPages && !modalCommentsLoading) {
                    loadCommentsInModal(currentModalMomentId, modalCommentsPage + 1, true);
                } else if (currentModalType === 'likes' && modalLikesPage < modalLikesTotalPages && !modalLikesLoading) {
                    loadLikesInModal(currentModalMomentId, modalLikesPage + 1, true);
                }
            }
        });
    }

    function loadComments(momentId) {
        const url = routes.comments.replace(':id', momentId);
        const container = $(`#comments-${momentId}`);
        
        container.html('<div class="loading-container"><div class="spinner"></div></div>');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(comment => {
                        const user = comment.user || {};
                        const avatar = user.profile?.avatar ? getImagePath(user.profile.avatar) : defaultAvatar;
                        const timeAgo = getTimeAgo(new Date(comment.created_at));
                        
                        html += `
                            <div class="comment-item">
                                <img src="${avatar}" alt="${escapeHtml(user.name)}" class="comment-avatar" loading="lazy">
                                <div class="comment-content">
                                    <div class="comment-bubble">
                                        <div class="comment-author">${escapeHtml(user.name || 'Unknown')}</div>
                                        <div class="comment-text">${escapeHtml(comment.comment)}</div>
                                    </div>
                                    <div class="comment-actions">
                                        <span>${timeAgo}</span>
                                        <span class="comment-delete" onclick="deleteComment(${comment.id}, ${momentId})">
                                            ${texts.delete || 'Delete'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html(`
                        <p style="text-align: center; color: var(--text-secondary); padding: 20px;">
                            ${texts.noComments || 'No comments yet'}
                        </p>
                    `);
                }
            },
            error: function() {
                container.html(`
                    <p style="text-align: center; color: #e4405f; padding: 20px;">
                        ${texts.failComments || 'Failed to load comments'}
                    </p>
                `);
            }
        });
    }

    function loadLikes(momentId) {
        const url = routes.likes.replace(':id', momentId);
        const container = $(`#likes-${momentId}`);
        
        container.html('<div class="loading-container"><div class="spinner"></div></div>');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(like => {
                        const user = like.user || {};
                        const avatar = user.profile?.avatar ? getImagePath(user.profile.avatar) : defaultAvatar;
                        const userUrl = adminUserUrl + user.id;
                        
                        html += `
                            <div class="like-item" onclick="window.open('${userUrl}', '_blank')">
                                <img src="${avatar}" alt="${escapeHtml(user.name)}" class="like-avatar" loading="lazy">
                                <div class="like-user-info">
                                    <div class="like-name">${escapeHtml(user.name || 'Unknown')}</div>
                                    <div class="like-uuid">${user.uuid || ''}</div>
                                </div>
                            </div>
                        `;
                    });
                    container.html(html);
                } else {
                    container.html(`
                        <p style="text-align: center; color: var(--text-secondary); padding: 20px;">
                            ${texts.noLikes || 'No likes yet'}
                        </p>
                    `);
                }
            },
            error: function() {
                container.html(`
                    <p style="text-align: center; color: #e4405f; padding: 20px;">
                        ${texts.failLikes || 'Failed to load likes'}
                    </p>
                `);
            }
        });
    }

    window.editMoment = function(momentId, event) {
        if (event) event.stopPropagation();
        $('.dropdown-menu').removeClass('show');
        
        const descElement = $(`#desc-${momentId}`);
        const currentDesc = descElement.text().trim();
        
        Swal.fire({
            title: texts.editDesc || 'Edit Description',
            input: 'textarea',
            inputValue: currentDesc,
            inputAttributes: { rows: 5 },
            showCancelButton: true,
            confirmButtonColor: '#1877f2',
            cancelButtonColor: '#65676b',
            confirmButtonText: texts.save || 'Save',
            cancelButtonText: texts.cancel || 'Cancel',
            inputValidator: (value) => {
                if (value && value.length > 1000) {
                    return texts.descTooLong || 'Description is too long (max 1000 characters)';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateMomentDescription(momentId, result.value);
            }
        });
    };

    function updateMomentDescription(momentId, description) {
        $.ajax({
            url: routes.updateDescription.replace(':id', momentId),
            method: 'PUT',
            data: { description: description, _token: csrf },
            success: function(response) {
                if (response.success) {
                    $(`#desc-${momentId}`).html(escapeHtml(response.description));
                    const direction = detectTextDirection(response.description);
                    $(`#desc-${momentId}`).attr('dir', direction).css('text-align', direction === 'rtl' ? 'right' : 'left');
                    Swal.fire({ 
                        icon: 'success', 
                        title: texts.updated || 'Updated', 
                        text: texts.descUpdated || 'Description updated successfully', 
                        timer: 1500, 
                        showConfirmButton: false 
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({ 
                    icon: 'error', 
                    title: texts.error || 'Error', 
                    text: xhr.responseJSON?.message || texts.failUpdate || 'Failed to update description' 
                });
            }
        });
    }

    window.deleteMoment = function(momentId, event) {
        if (event) event.stopPropagation();
        $('.dropdown-menu').removeClass('show');
        
        Swal.fire({
            title: texts.sure || 'Are you sure?',
            text: texts.noRevert || 'You will not be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e4405f',
            cancelButtonColor: '#65676b',
            confirmButtonText: texts.yesDelete || 'Yes, delete it!',
            cancelButtonText: texts.cancel || 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.deleteMoment.replace(':id', momentId),
                    type: 'DELETE',
                    data: { _token: csrf },
                    success: function(response) {
                        if (response.success) {
                            $(`.moment-post[data-moment-id="${momentId}"]`).fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire({ 
                                icon: 'success', 
                                title: texts.deleted || 'Deleted!', 
                                text: texts.momentDeleted || 'Moment deleted successfully', 
                                timer: 1500, 
                                showConfirmButton: false 
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ 
                            icon: 'error', 
                            title: texts.error || 'Error', 
                            text: xhr.responseJSON?.message || texts.failDeleteMoment || 'Failed to delete moment' 
                        });
                    }
                });
            }
        });
    };

    window.deleteComment = function(commentId, momentId) {
        deleteCommentFromModal(commentId, momentId);
    };

    window.deleteCommentFromModal = function(commentId, momentId, event) {
        if (event) event.stopPropagation();
        
        Swal.fire({
            title: texts.sure || 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e4405f',
            cancelButtonColor: '#65676b',
            confirmButtonText: texts.yesDelete || 'Yes, delete it!',
            cancelButtonText: texts.cancel || 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.deleteComment.replace(':id', commentId),
                    type: 'DELETE',
                    data: { _token: csrf },
                    success: function(response) {
                        if (response.success) {
                            loadCommentsInModal(momentId);
                            Swal.fire({ 
                                icon: 'success', 
                                title: texts.deleted || 'Deleted!', 
                                text: texts.commentDeleted || 'Comment deleted successfully', 
                                timer: 1200, 
                                showConfirmButton: false 
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({ 
                            icon: 'error', 
                            title: texts.error || 'Error', 
                            text: xhr.responseJSON?.message || texts.failDeleteComment || 'Failed to delete comment' 
                        });
                    }
                });
            }
        });
    };

    window.clearFilters = function() {
        $('#userIdFilter').val('');
        $('#userSearch').val('');
        userIdFilter = '';
        searchQuery = '';
        currentPage = 1;
        loadMoments();
    };

    window.retryLoad = function() {
        currentPage = 1;
        loadMoments();
    };

    // Helper Functions
    function getUserAvatar(user) {
        if (user.profile?.avatar) return getImagePath(user.profile.avatar);
        if (user.avatar) return getImagePath(user.avatar);
        return defaultAvatar;
    }

    function getImagePath(path) {
        if (!path) return defaultAvatar;
        if (path.startsWith('http')) return path;
        return storageUrl ? storageUrl + '/' + path : '/storage/' + path;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function detectTextDirection(text) {
        if (!text) return 'ltr';
        
        // تحقق من وجود أحرف عربية أو عبرية أو فارسية
        const rtlChars = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\u0590-\u05FF]/;
        
        // ابحث عن أول حرف في النص
        const firstChar = text.trim().charAt(0);
        
        // إذا كان أول حرف من اليمين لليسار
        if (rtlChars.test(firstChar)) {
            return 'rtl';
        }
        
        // تحقق من نسبة الأحرف RTL في النص
        const rtlCount = (text.match(rtlChars) || []).length;
        const totalChars = text.replace(/\s/g, '').length;
        
        // إذا كانت أكثر من 30% من الأحرف RTL
        if (totalChars > 0 && (rtlCount / totalChars) > 0.3) {
            return 'rtl';
        }
        
        return 'ltr';
    }

    function applyTextDirection(text) {
        const direction = detectTextDirection(text);
        return `<div dir="${direction}" style="text-align: ${direction === 'rtl' ? 'right' : 'left'};">${text}</div>`;
    }

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        const intervals = [
            { label: texts.years || 'y', seconds: 31536000 },
            { label: texts.months || 'm', seconds: 2592000 },
            { label: texts.days || 'd', seconds: 86400 },
            { label: texts.hours || 'h', seconds: 3600 },
            { label: texts.minutes || 'm', seconds: 60 }
        ];
        
        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count >= 1) return count + interval.label;
        }
        return texts.now || 'just now';
    }

    window.loadMoments = loadMoments;

})(jQuery);
