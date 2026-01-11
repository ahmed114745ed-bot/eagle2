<style>@include('admin.chat.style')</style>

<div class="chat-wrapper">
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <a href="{{ route('admin.group-chat.index') }}" class="back-btn">
                <i class="fa fa-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}"></i>
            </a>
            <div class="profile-pic">
                <i class="fa fa-comments"></i>
            </div>
            <div class="chat-info">
                <h2>{{ __('Group Chat') }}</h2>
                <p class="status">
                    <span class="status-dot"></span>
                    {{ __('Admin View') }}
                </p>
            </div>
            <button class="refresh-btn" onclick="refreshMessages()">
                <span>{{ __('Refresh') }}</span>
                <i class="fa fa-sync-alt"></i>
            </button>
        </div>

        <div class="chat-filter">
            <div class="filter-toggle" onclick="toggleFilterPanel()">
                <i class="fa fa-filter"></i>
                <span>{{ __('Filters') }}</span>
                <i class="fa fa-chevron-down toggle-icon" id="filterToggleIcon"></i>
            </div>

            <div class="filter-panel" id="filterPanel" style="display: none;">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="userIdFilter">{{ __('User ID') }}:</label>
                        <input type="number" id="userIdFilter" class="filter-input" placeholder="{{ __('Enter User ID') }}" min="1">
                    </div>

                    <div class="filter-group">
                        <label for="userNameFilter">{{ __('User Name') }}:</label>
                        <input type="text" id="userNameFilter" class="filter-input" placeholder="{{ __('Enter User Name') }}">
                    </div>

                    <div class="filter-group">
                        <label for="uuidFilter">{{ __('UUID') }}:</label>
                        <input type="text" id="uuidFilter" class="filter-input" placeholder="{{ __('Enter UUID') }}">
                    </div>
                </div>

                <div class="filter-row">
                    <div class="filter-group">
                        <label for="dateFromFilter">{{ __('Date From') }}:</label>
                        <input type="date" id="dateFromFilter" class="filter-input">
                    </div>

                    <div class="filter-group">
                        <label for="dateToFilter">{{ __('Date To') }}:</label>
                        <input type="date" id="dateToFilter" class="filter-input">
                    </div>

                    <div class="filter-actions">
                        <button class="btn-info apply-filter" onclick="applyFilters()">
                            <i class="fa fa-search"></i> {{ __('Apply') }}
                        </button>
                        <button class="btn-danger" onclick="clearFilters()" id="clearFilterBtn" style="display: none;">
                            <i class="fa fa-times"></i> {{ __('Clear') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filters Badge -->
            <div class="active-filters" id="activeFilters" style="display: none;">
                <span class="active-filters-label">{{ __('Active Filters') }}:</span>
                <div class="filter-badges" id="filterBadges"></div>
            </div>
        </div>

        <!-- Reply Preview (shown when replying to a message) -->
        <div class="reply-preview" id="replyPreview" style="display: none;">
            <div class="reply-content">
                <div class="reply-indicator"></div>
                <div class="reply-info">
                    <span class="reply-to-name" id="replyToName">{{ __('Replying to User') }}</span>
                    <span class="reply-to-text" id="replyToText">{{ __('Message text here...') }}</span>
                </div>
            </div>
            <button class="cancel-reply-btn" onclick="cancelReply()">
                <i class="fa fa-times"></i>
            </button>
            <input type="hidden" id="replyToId" value="">
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
{{--            <div class="load-more-container" id="loadMoreContainer" style="display: none;">--}}
{{--                <button class="load-more-btn" id="loadMoreBtn" onclick="loadMoreMessages()">--}}
{{--                    <i class="fa fa-arrow-up"></i> Load More Messages--}}
{{--                </button>--}}
{{--            </div>--}}
{{--            <div class="loading-indicator" id="loadingIndicator">--}}
{{--                <i class="fa fa-spinner"></i>--}}
{{--                <p>{{ __('Loading messages...') }}</p>--}}
{{--            </div>--}}
        </div>

        <!-- Message Input -->
        <div class="chat-input {{ !$canSendMessages ? 'disabled' : '' }}">
            <input type="text" id="messageInput"
                   placeholder="{{ $canSendMessages ? __('Type your message here') : __('You cannot send messages') }}"
                   class="message-input"
                {{ !$canSendMessages ? 'disabled' : '' }}>
            <button class="send-btn" onclick="sendMessage()" {{ !$canSendMessages ? 'disabled' : '' }}>
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa fa-edit"></i>
                {{ __('Edit Message') }}
            </h3>
            <button class="close-btn" onclick="closeEditModal()">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <textarea id="editMessageText" rows="4" placeholder="{{ __('Type your message...') }}"></textarea>
        <input type="hidden" id="editMessageId">
        <div class="modal-footer">
            <button class="btn-danger" onclick="closeEditModal()">
                <i class="fa fa-times"></i> {{ __('Cancel') }}
            </button>
            <button class="btn-info" onclick="saveEdit()">
                <i class="fa fa-check"></i> {{ __('Save') }}
            </button>
        </div>
    </div>
</div>

<script>
    // Translations
    const translations = {
        loadingMessages: "{{ __('Loading messages...') }}",
        failedToLoadMessages: "{{ __('Failed to load messages') }}",
        failedToLoadMoreMessages: "{{ __('Failed to load more messages') }}",
        refreshingMessages: "{{ __('Refreshing messages...') }}",
        pleaseEnterMessage: "{{ __('Please enter a message') }}",
        messageSentSuccessfully: "{{ __('Message sent successfully') }}",
        failedToSendMessage: "{{ __('Failed to send message') }}",
        messageCannotBeEmpty: "{{ __('Message cannot be empty') }}",
        messageUpdatedSuccessfully: "{{ __('Message updated successfully') }}",
        failedToUpdateMessage: "{{ __('Failed to update message') }}",
        confirmDeleteMessage: "{{ __('Are you sure you want to delete this message?') }}",
        messageDeletedSuccessfully: "{{ __('Message deleted successfully') }}",
        failedToDeleteMessage: "{{ __('Failed to delete message') }}",
        messageNotLoaded: "{{ __('Message not loaded. Loading older messages...') }}",
        scrollUpForOlderMessages: "{{ __('Scroll up for older messages') }}",
        replyingTo: "{{ __('Replying to') }}",
        user: "{{ __('User') }}",
        filtersApplied: "{{ __('Filters applied') }}",
        filtersCleared: "{{ __('Filters cleared') }}",
        noFiltersApplied: "{{ __('No filters to clear') }}",
        userId: "{{ __('User ID') }}",
        userName: "{{ __('User Name') }}",
        uuid: "{{ __('UUID') }}",
        dateFrom: "{{ __('Date From') }}",
        dateTo: "{{ __('Date To') }}"
    };

    // Configuration
    const csrfToken = '{{ csrf_token() }}';
    const adminAppId = {{ $adminAppId ?? 0 }};
    const canSendMessages = {{ $canSendMessages ? 'true' : 'false' }};
    const messagesRoute = '{{ route("admin.chat.messages") }}';
    const storeRoute = '{{ route("admin.chat.store") }}';
    const updateRoute = '{{ route("admin.chat.update") }}';
    const deleteRouteBase = '{{ route("admin.chat.delete", "") }}';

    // State variables
    let currentPage = 1;
    let lastPage = 1;
    let isLoading = false;
    let allMessages = [];
    let initialLoad = true;
    let hasMoreMessages = true;
    let loadedMessageIds = new Set();
    let replyingTo = null;

    // Filter state
    let activeFilters = {
        user_id: null,
        user_name: null,
        uuid: null,
        date_from: null,
        date_to: null
    };

    const SCROLL_THRESHOLD = 100;
    let scrollDebounceTimer = null;

    // DOM Elements
    const chatContainer = document.getElementById('chatMessages');
    const filterPanel = document.getElementById('filterPanel');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const clearFilterBtn = document.getElementById('clearFilterBtn');
    const activeFiltersContainer = document.getElementById('activeFilters');
    const filterBadgesContainer = document.getElementById('filterBadges');

    // ==================== FILTER FUNCTIONS ====================

    function toggleFilterPanel() {
        const toggle = document.querySelector('.filter-toggle');

        if (filterPanel.style.display === 'none') {
            filterPanel.style.display = 'block';
            toggle.classList.add('active');
        } else {
            filterPanel.style.display = 'none';
            toggle.classList.remove('active');
        }
    }

    function applyFilters() {
        // Get filter values
        const userId = document.getElementById('userIdFilter').value.trim();
        const userName = document.getElementById('userNameFilter').value.trim();
        const uuid = document.getElementById('uuidFilter').value.trim();
        const dateFrom = document.getElementById('dateFromFilter').value;
        const dateTo = document.getElementById('dateToFilter').value;

        // Update active filters
        activeFilters.user_id = userId || null;
        activeFilters.user_name = userName || null;
        activeFilters.uuid = uuid || null;
        activeFilters.date_from = dateFrom || null;
        activeFilters.date_to = dateTo || null;

        // Check if any filter is applied
        const hasFilters = Object.values(activeFilters).some(v => v !== null);

        // Update UI
        updateFilterUI(hasFilters);

        // Reset and reload messages
        resetAndReload();

        if (hasFilters) {
            toastr.info(translations.filtersApplied);
        }
    }

    function clearFilters() {
        // Check if any filter is active
        const hasFilters = Object.values(activeFilters).some(v => v !== null);

        if (!hasFilters) {
            toastr.info(translations.noFiltersApplied);
            return;
        }

        // Clear filter values
        document.getElementById('userIdFilter').value = '';
        document.getElementById('userNameFilter').value = '';
        document.getElementById('uuidFilter').value = '';
        document.getElementById('dateFromFilter').value = '';
        document.getElementById('dateToFilter').value = '';

        // Reset active filters
        activeFilters = {
            user_id: null,
            user_name: null,
            uuid: null,
            date_from: null,
            date_to: null
        };

        // Update UI
        updateFilterUI(false);

        // Reset and reload messages
        resetAndReload();

        toastr.info(translations.filtersCleared);
    }

    function removeFilter(filterKey) {
        activeFilters[filterKey] = null;

        // Clear corresponding input
        const inputMap = {
            user_id: 'userIdFilter',
            user_name: 'userNameFilter',
            uuid: 'uuidFilter',
            date_from: 'dateFromFilter',
            date_to: 'dateToFilter'
        };

        const inputId = inputMap[filterKey];
        if (inputId) {
            document.getElementById(inputId).value = '';
        }

        // Check if any filter remains
        const hasFilters = Object.values(activeFilters).some(v => v !== null);
        updateFilterUI(hasFilters);

        // Reload messages
        resetAndReload();
    }

    function updateFilterUI(hasFilters) {
        // Show/hide clear button
        clearFilterBtn.style.display = hasFilters ? 'inline-flex' : 'none';

        // Show/hide active filters badges
        activeFiltersContainer.style.display = hasFilters ? 'flex' : 'none';

        if (hasFilters) {
            renderFilterBadges();
        }
    }

    function renderFilterBadges() {
        const labelMap = {
            user_id: translations.userId,
            user_name: translations.userName,
            uuid: translations.uuid,
            date_from: translations.dateFrom,
            date_to: translations.dateTo
        };

        let badgesHtml = '';

        Object.entries(activeFilters).forEach(([key, value]) => {
            if (value !== null) {
                badgesHtml += `
                    <span class="filter-badge">
                        ${labelMap[key]}: ${value}
                        <i class="fa fa-times badge-remove" onclick="removeFilter('${key}')"></i>
                    </span>
                `;
            }
        });

        filterBadgesContainer.innerHTML = badgesHtml;
    }

    function buildFilterQueryString() {
        const params = new URLSearchParams();

        if (activeFilters.user_id) params.append('user_id', activeFilters.user_id);
        if (activeFilters.user_name) params.append('user_name', activeFilters.user_name);
        if (activeFilters.uuid) params.append('uuid', activeFilters.uuid);
        if (activeFilters.date_from) params.append('date_from', activeFilters.date_from);
        if (activeFilters.date_to) params.append('date_to', activeFilters.date_to);

        return params.toString();
    }

    // ==================== SCROLL HANDLER ====================

    function handleScroll() {
        if (scrollDebounceTimer) clearTimeout(scrollDebounceTimer);

        scrollDebounceTimer = setTimeout(() => {
            if (chatContainer.scrollTop <= SCROLL_THRESHOLD && !isLoading && hasMoreMessages) {
                loadMoreMessages();
            }
        }, 300);
    }

    chatContainer.addEventListener('scroll', handleScroll);

    // ==================== MESSAGE LOADING ====================

    function loadMessages() {
        if (isLoading) return;

        isLoading = true;
        showTopLoader();

        let url = `${messagesRoute}?page=1`;
        const filterQuery = buildFilterQueryString();
        if (filterQuery) {
            url += `&${filterQuery}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allMessages = [];
                    loadedMessageIds.clear();

                    (data.messages || []).forEach(msg => {
                        if (!loadedMessageIds.has(msg.id)) {
                            loadedMessageIds.add(msg.id);
                            allMessages.push(msg);
                        }
                    });

                    lastPage = data.last_page || 1;
                    currentPage = 1;
                    hasMoreMessages = currentPage < lastPage;

                    renderMessages();
                    hideTopLoader();

                    if (initialLoad) {
                        setTimeout(() => {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                            initialLoad = false;
                        }, 100);
                    }
                }
                isLoading = false;
            })
            .catch(err => {
                console.error(err);
                toastr.error(translations.failedToLoadMessages);
                hideTopLoader();
                isLoading = false;
            });
    }

    function loadMoreMessages() {
        if (isLoading || !hasMoreMessages || currentPage >= lastPage) return;

        const nextPage = currentPage + 1;
        isLoading = true;
        showTopLoader();

        const anchorElement = chatContainer.querySelector('.message-wrapper');

        let url = `${messagesRoute}?page=${nextPage}`;
        const filterQuery = buildFilterQueryString();
        if (filterQuery) {
            url += `&${filterQuery}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const olderMessages = data.messages || [];

                    const sortedOlderMessages = [...olderMessages].sort((a, b) =>
                        new Date(a.created_at) - new Date(b.created_at)
                    );

                    let htmlToInsert = '';
                    sortedOlderMessages.forEach(msg => {
                        if (!loadedMessageIds.has(msg.id)) {
                            loadedMessageIds.add(msg.id);
                            allMessages.push(msg);
                            htmlToInsert += createMessageElement(msg);
                        }
                    });

                    if (anchorElement && htmlToInsert) {
                        anchorElement.insertAdjacentHTML('beforebegin', htmlToInsert);
                        anchorElement.scrollIntoView({ block: 'start', behavior: 'instant' });
                        chatContainer.scrollTop -= 100;
                    }

                    currentPage = nextPage;
                    hasMoreMessages = currentPage < lastPage;

                    const scrollUpIndicator = document.getElementById('scrollUpIndicator');
                    if (!hasMoreMessages && scrollUpIndicator) {
                        scrollUpIndicator.remove();
                    }

                    hideTopLoader();
                }
                isLoading = false;
            })
            .catch(err => {
                console.error(err);
                toastr.error(translations.failedToLoadMoreMessages);
                hideTopLoader();
                isLoading = false;
            });
    }

    // ==================== LOADER FUNCTIONS ====================

    function showTopLoader() {
        let loader = document.getElementById('topLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'topLoader';
            loader.className = 'top-loading-indicator';
            loader.innerHTML = `<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i> <span>${translations.loadingMessages}</span></div>`;
            chatContainer.prepend(loader);
        }
        loader.style.display = 'flex';
    }

    function hideTopLoader() {
        const loader = document.getElementById('topLoader');
        if (loader) loader.style.display = 'none';
    }

    // ==================== RESET & REFRESH ====================

    function resetAndReload() {
        currentPage = 1;
        lastPage = 1;
        allMessages = [];
        loadedMessageIds.clear();
        initialLoad = true;
        hasMoreMessages = true;
        isLoading = false;
        chatContainer.innerHTML = '';
        loadMessages();
    }

    function refreshMessages() {
        resetAndReload();
        toastr.info(translations.refreshingMessages);
    }

    // ==================== RENDER MESSAGES ====================

    function renderMessages() {
        chatContainer.querySelectorAll('.message-wrapper, .scroll-up-indicator').forEach(el => el.remove());

        const sorted = [...allMessages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        if (hasMoreMessages) {
            const indicator = document.createElement('div');
            indicator.className = 'scroll-up-indicator';
            indicator.id = 'scrollUpIndicator';
            indicator.innerHTML = `<i class="fa fa-arrow-up"></i> <span>${translations.scrollUpForOlderMessages}</span>`;
            const loader = document.getElementById('topLoader');
            if (loader) loader.after(indicator);
            else chatContainer.prepend(indicator);
        }

        sorted.forEach(msg => {
            chatContainer.insertAdjacentHTML('beforeend', createMessageElement(msg));
        });
    }

    function createMessageElement(message) {
        const isAdmin = message.user_id == adminAppId;
        const messageClass = isAdmin ? 'sent-message' : 'received-message';
        const time = new Date(message.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        const avatarUrl = message.user_avatar || '{{ asset("images/default-avatar.png") }}';
        const userName = message.user_name || translations.user;
        const userInitial = userName.charAt(0).toUpperCase();

        let replyHtml = '';
        if (message.parent) {
            replyHtml = `
                <div class="replied-message" onclick="scrollToMessage(${message.parent.id})">
                    <div class="replied-user">${escapeHtml(message.parent.user_name)}</div>
                    <div class="replied-text">${escapeHtml(message.parent.text)}</div>
                </div>`;
        }

        const actionMenuHtml = `
            <div class="message-actions">
                <button class="more-btn" onclick="toggleActionMenu(event, ${message.id})">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <div class="action-menu" id="action-menu-${message.id}">
                    <button class="action-menu-item reply-item" onclick="setReply(${message.id}, '${escapeHtml(userName)}', '${escapeHtml(message.text)}')">
                        <i class="fa fa-reply"></i> {{ __('Reply') }}
        </button>
        <button class="action-menu-item edit-item" onclick="editMessage(${message.id}, '${escapeHtml(message.text)}')">
                        <i class="fa fa-edit"></i> {{ __('Edit') }}
        </button>
        <button class="action-menu-item delete-item" onclick="deleteMessage(${message.id})">
                        <i class="fa fa-trash"></i> {{ __('Delete') }}
        </button>
    </div>
</div>`;

        if (isAdmin) {
            return `
                <div class="message-wrapper ${messageClass}" data-id="${message.id}">
                    ${actionMenuHtml}
                    <div class="message-content">
                        <div class="message-bubble">
                            ${replyHtml}
                            <p>${escapeHtml(message.text)}</p>
                            <div class="message-footer">
                                <span class="message-time">${time}</span>
                                <i class="fa fa-check-double"></i>
                            </div>
                        </div>
                        <div class="user-avatar sender">
                            <img src="${avatarUrl}" alt="${userName}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="avatar-fallback" style="display:none;">${userInitial}</span>
                        </div>
                    </div>
                </div>`;
        } else {
            return `
                <div class="message-wrapper ${messageClass}" data-id="${message.id}">
                    <div class="message-content">
                        <div class="user-avatar">
                            <img src="${avatarUrl}" alt="${userName}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="avatar-fallback" style="display:none;">${userInitial}</span>
                        </div>
                        <div class="message-bubble">
                            ${replyHtml}
                            <p class="user-name-label">${userName}</p>
                            <p>${escapeHtml(message.text)}</p>
                            <span class="message-time">${time}</span>
                        </div>
                    </div>
                    ${actionMenuHtml}
                </div>`;
        }
    }

    // ==================== ACTION MENU ====================

    function toggleActionMenu(event, messageId) {
        event.stopPropagation();
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));

        const menu = document.getElementById(`action-menu-${messageId}`);
        if (!menu) return;

        const wrapper = menu.closest('.message-wrapper');
        const containerRect = chatContainer.getBoundingClientRect();
        const msgRect = wrapper.getBoundingClientRect();

        if (containerRect.bottom - msgRect.bottom < 150) {
            menu.classList.add('open-up');
        } else {
            menu.classList.add('open-down');
        }
        menu.classList.add('show');
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('.message-actions')) {
            document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
        }
    });

    chatContainer.addEventListener('scroll', () => {
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
    });

    // ==================== REPLY FUNCTIONS ====================

    function setReply(messageId, userName, messageText) {
        replyingTo = messageId;
        document.getElementById('replyToId').value = messageId;
        document.getElementById('replyToName').textContent = `${translations.replyingTo} ${userName}`;
        document.getElementById('replyToText').textContent = messageText;
        document.getElementById('replyPreview').style.display = 'flex';
        document.getElementById('messageInput').focus();
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
    }

    function cancelReply() {
        replyingTo = null;
        document.getElementById('replyToId').value = '';
        document.getElementById('replyPreview').style.display = 'none';
    }

    function scrollToMessage(messageId) {
        const el = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('highlighted');
            setTimeout(() => el.classList.remove('highlighted'), 2000);
        } else {
            toastr.info(translations.messageNotLoaded);
        }
    }

    // ==================== UTILITY FUNCTIONS ====================

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // ==================== SEND MESSAGE ====================

    function sendMessage() {
        if (!canSendMessages) {
            toastr.error("{{ __('You cannot send messages. Your account is not linked to an app user.') }}");
            return;
        }

        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text) {
            toastr.warning(translations.pleaseEnterMessage);
            return;
        }

        const payload = { text };
        if (replyingTo) payload.parent_id = replyingTo;

        fetch(storeRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    cancelReply();
                    if (!loadedMessageIds.has(data.message.id)) {
                        allMessages.push(data.message);
                        loadedMessageIds.add(data.message.id);
                    }
                    renderMessages();
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                    toastr.success(translations.messageSentSuccessfully);
                } else {
                    toastr.error(data.error || translations.failedToSendMessage);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(translations.failedToSendMessage);
            });
    }

    // ==================== EDIT MESSAGE ====================

    function editMessage(id, text) {
        document.getElementById('editMessageId').value = id;
        document.getElementById('editMessageText').value = text.replace(/\\'/g, "'").replace(/\\"/g, '"');
        document.getElementById('editModal').classList.add('active');
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function saveEdit() {
        const id = document.getElementById('editMessageId').value;
        const text = document.getElementById('editMessageText').value.trim();

        if (!text) {
            toastr.warning(translations.messageCannotBeEmpty);
            return;
        }

        fetch(updateRoute, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ id, text })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    const idx = allMessages.findIndex(m => m.id == id);
                    if (idx !== -1) {
                        allMessages[idx].text = text;
                        renderMessages();
                    }
                    toastr.success(translations.messageUpdatedSuccessfully);
                } else {
                    toastr.error(data.error || translations.failedToUpdateMessage);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(translations.failedToUpdateMessage);
            });
    }

    // ==================== DELETE MESSAGE ====================

    function deleteMessage(id) {
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));

        if (!confirm(translations.confirmDeleteMessage)) return;

        fetch(`${deleteRouteBase}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allMessages = allMessages.filter(m => m.id != id);
                    loadedMessageIds.delete(parseInt(id));
                    renderMessages();
                    toastr.success(translations.messageDeletedSuccessfully);
                } else {
                    toastr.error(data.error || translations.failedToDeleteMessage);
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error(translations.failedToDeleteMessage);
            });
    }

    // ==================== EVENT LISTENERS ====================

    // Send message on Enter key
    document.getElementById('messageInput').addEventListener('keypress', e => {
        if (e.key === 'Enter') sendMessage();
    });

    // Close modal on backdrop click
    document.getElementById('editModal').addEventListener('click', e => {
        if (e.target === e.currentTarget) closeEditModal();
    });

    // Apply filter on Enter key in filter inputs
    document.getElementById('userIdFilter').addEventListener('keypress', e => {
        if (e.key === 'Enter') applyFilters();
    });

    document.getElementById('userNameFilter').addEventListener('keypress', e => {
        if (e.key === 'Enter') applyFilters();
    });

    document.getElementById('uuidFilter').addEventListener('keypress', e => {
        if (e.key === 'Enter') applyFilters();
    });

    // ==================== INITIALIZE ====================

    loadMessages();
</script>
