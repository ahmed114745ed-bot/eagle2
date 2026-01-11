<style>@include('admin.chat.style')</style>

<div class="chat-wrapper">
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <a href="{{ route('admin.group-chat.index') }}" class="back-btn">
                <i class="fa fa-arrow-right"></i>
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
        <div class="chat-input">

            <input type="text" id="messageInput" placeholder="{{ __('Type your message here') }}" class="message-input">

            <button class="send-btn" onclick="sendMessage()">
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
        user: "{{ __('User') }}"
    };

    const csrfToken = '{{ csrf_token() }}';
    const adminUserId = {{ Admin::user()->id ?? 1 }};

    let currentPage = 1;
    let lastPage = 1;
    let isLoading = false;
    let allMessages = [];
    let initialLoad = true;
    let hasMoreMessages = true;
    let loadedMessageIds = new Set();
    let replyingTo = null;

    const SCROLL_THRESHOLD = 100;
    let scrollDebounceTimer = null;

    const chatContainer = document.getElementById('chatMessages');

    // Scroll handler - load more when at TOP
    function handleScroll() {
        if (scrollDebounceTimer) clearTimeout(scrollDebounceTimer);

        scrollDebounceTimer = setTimeout(() => {
            if (chatContainer.scrollTop <= SCROLL_THRESHOLD && !isLoading && hasMoreMessages) {
                loadMoreMessages();
            }
        }, 300);
    }

    chatContainer.addEventListener('scroll', handleScroll);

    // Load initial messages (newest - page 1)
    function loadMessages() {
        if (isLoading) return;

        isLoading = true;
        showTopLoader();

        fetch(`{{ route("admin.chat.messages") }}?page=1`)
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

    // Load older messages (higher page numbers)
    function loadMoreMessages() {
        if (isLoading || !hasMoreMessages || currentPage >= lastPage) return;

        const nextPage = currentPage + 1;
        isLoading = true;
        showTopLoader();

        // IMPORTANT: Store scroll height BEFORE loading
        const scrollHeightBefore = chatContainer.scrollHeight;

        fetch(`{{ route("admin.chat.messages") }}?page=${nextPage}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const olderMessages = data.messages || [];

                    // Add older messages (they go to the TOP visually)
                    olderMessages.forEach(msg => {
                        if (!loadedMessageIds.has(msg.id)) {
                            loadedMessageIds.add(msg.id);
                            allMessages.push(msg); // Just add, sorting handles position
                        }
                    });

                    currentPage = nextPage;
                    hasMoreMessages = currentPage < lastPage;

                    renderMessages();

                    // IMPORTANT: Maintain scroll position
                    // New content was added at TOP, so adjust scroll
                    setTimeout(() => {
                        const scrollHeightAfter = chatContainer.scrollHeight;
                        const addedHeight = scrollHeightAfter - scrollHeightBefore;
                        chatContainer.scrollTop = addedHeight;
                    }, 10);

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

    function refreshMessages() {
        currentPage = 1;
        lastPage = 1;
        allMessages = [];
        loadedMessageIds.clear();
        initialLoad = true;
        hasMoreMessages = true;
        isLoading = false;
        chatContainer.innerHTML = '';
        loadMessages();
        toastr.info(translations.refreshingMessages);
    }

    // Render: Sort oldest TOP, newest BOTTOM
    function renderMessages() {
        chatContainer.querySelectorAll('.message-wrapper, .scroll-up-indicator').forEach(el => el.remove());

        // Sort: oldest first (small date/id) at TOP
        const sorted = [...allMessages].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        // Show indicator if more pages
        if (hasMoreMessages) {
            const indicator = document.createElement('div');
            indicator.className = 'scroll-up-indicator';
            indicator.id = 'scrollUpIndicator';
            indicator.innerHTML = `<i class="fa fa-arrow-up"></i> <span>${translations.scrollUpForOlderMessages}</span>`;
            const loader = document.getElementById('topLoader');
            if (loader) loader.after(indicator);
            else chatContainer.prepend(indicator);
        }

        // Render oldest to newest
        sorted.forEach(msg => {
            chatContainer.insertAdjacentHTML('beforeend', createMessageElement(msg));
        });
    }

    function createMessageElement(message) {
        const isAdmin = message.user_id == adminUserId;
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

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();
        if (!text) { toastr.warning(translations.pleaseEnterMessage); return; }

        const payload = { text, user_id: adminUserId };
        if (replyingTo) payload.parent_id = replyingTo;

        fetch('{{ route("admin.chat.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
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
                }
            })
            .catch(err => { console.error(err); toastr.error(translations.failedToSendMessage); });
    }

    function editMessage(id, text) {
        document.getElementById('editMessageId').value = id;
        document.getElementById('editMessageText').value = text;
        document.getElementById('editModal').classList.add('active');
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    function saveEdit() {
        const id = document.getElementById('editMessageId').value;
        const text = document.getElementById('editMessageText').value.trim();
        if (!text) { toastr.warning(translations.messageCannotBeEmpty); return; }

        fetch('{{ route("admin.chat.update") }}', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id, text })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    const idx = allMessages.findIndex(m => m.id == id);
                    if (idx !== -1) { allMessages[idx].text = text; renderMessages(); }
                    toastr.success(translations.messageUpdatedSuccessfully);
                }
            })
            .catch(err => { console.error(err); toastr.error(translations.failedToUpdateMessage); });
    }

    function deleteMessage(id) {
        document.querySelectorAll('.action-menu.show').forEach(m => m.classList.remove('show', 'open-up', 'open-down'));
        if (!confirm(translations.confirmDeleteMessage)) return;

        fetch(`{{ route("admin.chat.delete", "") }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    allMessages = allMessages.filter(m => m.id != id);
                    loadedMessageIds.delete(parseInt(id));
                    renderMessages();
                    toastr.success(translations.messageDeletedSuccessfully);
                }
            })
            .catch(err => { console.error(err); toastr.error(translations.failedToDeleteMessage); });
    }

    document.getElementById('messageInput').addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });
    document.getElementById('editModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeEditModal(); });

    // START
    loadMessages();
</script>
