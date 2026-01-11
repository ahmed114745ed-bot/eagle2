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
            <div class="loading-indicator" id="loadingIndicator">
                <i class="fa fa-spinner"></i>
                <p>{{ __('Loading messages...') }}</p>
            </div>
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
    // Translations object
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

    // CSRF Token
    const csrfToken = '{{ csrf_token() }}';
    const adminUserId = {{ Admin::user()->id ?? 1 }};

    // Pagination state
    let currentPage = 1;
    let lastPage = 1;
    let isLoading = false;
    let allMessages = [];
    let initialLoad = true;

    // Reply state
    let replyingTo = null;

    // Scroll threshold
    const SCROLL_THRESHOLD = 100;

    // Get chat container
    const chatContainer = document.getElementById('chatMessages');

    // Scroll event listener
    chatContainer.addEventListener('scroll', function() {
        if (chatContainer.scrollTop <= SCROLL_THRESHOLD && !isLoading && currentPage < lastPage) {
            loadMoreMessages();
        }
    });

    // Load initial messages
    function loadMessages() {
        if (isLoading) return;

        isLoading = true;
        showTopLoader();

        fetch(`{{ route("admin.chat.messages") }}?page=${currentPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allMessages = data.messages;
                    lastPage = data.last_page;
                    currentPage = data.current_page;

                    renderMessages();
                    hideTopLoader();

                    if (initialLoad) {
                        scrollToBottom();
                        initialLoad = false;
                    }
                }
                isLoading = false;
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                toastr.error(translations.failedToLoadMessages);
                hideTopLoader();
                isLoading = false;
            });
    }

    // Load more messages
    function loadMoreMessages() {
        if (currentPage >= lastPage || isLoading) return;

        const nextPage = currentPage + 1;
        isLoading = true;
        showTopLoader();

        const previousScrollHeight = chatContainer.scrollHeight;

        fetch(`{{ route("admin.chat.messages") }}?page=${nextPage}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allMessages = [...data.messages, ...allMessages];
                    currentPage = data.current_page;
                    lastPage = data.last_page;

                    renderMessages();

                    const newScrollHeight = chatContainer.scrollHeight;
                    chatContainer.scrollTop = newScrollHeight - previousScrollHeight;

                    hideTopLoader();
                }
                isLoading = false;
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error(translations.failedToLoadMoreMessages);
                hideTopLoader();
                isLoading = false;
            });
    }

    // Show top loader
    function showTopLoader() {
        let loader = document.getElementById('topLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'topLoader';
            loader.className = 'top-loading-indicator';
            loader.innerHTML = `
                <div class="loading-spinner">
                    <i class="fa fa-spinner fa-spin"></i>
                    <span>${translations.loadingMessages}</span>
                </div>
            `;
            chatContainer.insertBefore(loader, chatContainer.firstChild);
        }
        loader.style.display = 'flex';
    }

    // Hide top loader
    function hideTopLoader() {
        const loader = document.getElementById('topLoader');
        if (loader) loader.style.display = 'none';

        const initialLoader = document.getElementById('loadingIndicator');
        if (initialLoader) initialLoader.style.display = 'none';
    }

    // Refresh messages
    function refreshMessages() {
        currentPage = 1;
        allMessages = [];
        initialLoad = true;

        const messageElements = chatContainer.querySelectorAll('.message-wrapper');
        messageElements.forEach(el => el.remove());

        loadMessages();
        toastr.info(translations.refreshingMessages);
    }

    // Render messages
    function renderMessages() {
        const messageElements = chatContainer.querySelectorAll('.message-wrapper');
        messageElements.forEach(el => el.remove());

        let scrollIndicator = document.getElementById('scrollUpIndicator');
        if (currentPage < lastPage) {
            if (!scrollIndicator) {
                scrollIndicator = document.createElement('div');
                scrollIndicator.id = 'scrollUpIndicator';
                scrollIndicator.className = 'scroll-up-indicator';
                scrollIndicator.innerHTML = `
                    <i class="fa fa-arrow-up"></i>
                    <span>${translations.scrollUpForOlderMessages}</span>
                `;
            }
            const loader = document.getElementById('topLoader');
            if (loader && loader.nextSibling) {
                chatContainer.insertBefore(scrollIndicator, loader.nextSibling);
            }
        } else if (scrollIndicator) {
            scrollIndicator.remove();
        }

        allMessages.forEach(message => {
            const messageHtml = createMessageElement(message);
            chatContainer.insertAdjacentHTML('beforeend', messageHtml);
        });
    }

    // Create message element
    function createMessageElement(message) {
        const isAdmin = message.user_id == adminUserId;
        const messageClass = isAdmin ? 'sent-message' : 'received-message';
        const time = new Date(message.created_at).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const avatarUrl = message.user_avatar || '{{ asset("images/default-avatar.png") }}';
        const userName = message.user_name || translations.user;
        const userInitial = userName.charAt(0).toUpperCase();

        // Reply bubble HTML
        let replyHtml = '';
        if (message.parent) {
            replyHtml = `
                <div class="replied-message" onclick="scrollToMessage(${message.parent.id})">
                    <div class="replied-user">${escapeHtml(message.parent.user_name)}</div>
                    <div class="replied-text">${escapeHtml(message.parent.text)}</div>
                </div>
            `;
        }

        if (isAdmin) {
            return `
            <div class="message-wrapper ${messageClass}" data-id="${message.id}">
                <div class="action-buttons">
                    <button class="reply-btn" onclick="setReply(${message.id}, '${escapeHtml(userName)}', '${escapeHtml(message.text)}')">
                        <i class="fa fa-reply"></i>
                    </button>
                    <button class="edit-btn" onclick="editMessage(${message.id}, '${escapeHtml(message.text)}')">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteMessage(${message.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
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
            </div>
        `;
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
                <div class="action-buttons">
                    <button class="reply-btn" onclick="setReply(${message.id}, '${escapeHtml(userName)}', '${escapeHtml(message.text)}')">
                        <i class="fa fa-reply"></i>
                    </button>
                    <button class="edit-btn" onclick="editMessage(${message.id}, '${escapeHtml(message.text)}')">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteMessage(${message.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        }
    }

    // Set reply
    function setReply(messageId, userName, messageText) {
        replyingTo = messageId;
        document.getElementById('replyToId').value = messageId;
        document.getElementById('replyToName').textContent = `${translations.replyingTo} ${userName}`;
        document.getElementById('replyToText').textContent = messageText;
        document.getElementById('replyPreview').classList.add('active');
        document.getElementById('replyPreview').style.display = 'flex';
        document.getElementById('messageInput').focus();
    }

    // Cancel reply
    function cancelReply() {
        replyingTo = null;
        document.getElementById('replyToId').value = '';
        document.getElementById('replyPreview').classList.remove('active');
        document.getElementById('replyPreview').style.display = 'none';
    }

    // Scroll to message
    function scrollToMessage(messageId) {
        const messageElement = document.querySelector(`.message-wrapper[data-id="${messageId}"]`);
        if (messageElement) {
            messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            messageElement.classList.add('highlighted');
            setTimeout(() => {
                messageElement.classList.remove('highlighted');
            }, 2000);
        } else {
            toastr.info(translations.messageNotLoaded);
        }
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // Scroll to bottom
    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Send message
    function sendMessage() {
        const input = document.getElementById('messageInput');
        const text = input.value.trim();

        if (text === '') {
            toastr.warning(translations.pleaseEnterMessage);
            return;
        }

        const payload = {
            text: text,
            user_id: adminUserId
        };

        if (replyingTo) {
            payload.parent_id = replyingTo;
        }

        fetch('{{ route("admin.chat.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    cancelReply();
                    allMessages.push(data.message);
                    renderMessages();
                    scrollToBottom();
                    toastr.success(translations.messageSentSuccessfully);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error(translations.failedToSendMessage);
            });
    }

    // Edit message
    function editMessage(id, text) {
        document.getElementById('editMessageId').value = id;
        document.getElementById('editMessageText').value = text;
        document.getElementById('editModal').classList.add('active');
    }

    // Close edit modal
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Save edit
    function saveEdit() {
        const id = document.getElementById('editMessageId').value;
        const text = document.getElementById('editMessageText').value.trim();

        if (text === '') {
            toastr.warning(translations.messageCannotBeEmpty);
            return;
        }

        fetch('{{ route("admin.chat.update") }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ id: id, text: text })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                    const index = allMessages.findIndex(m => m.id == id);
                    if (index !== -1) {
                        allMessages[index].text = text;
                        renderMessages();
                    }
                    toastr.success(translations.messageUpdatedSuccessfully);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error(translations.failedToUpdateMessage);
            });
    }

    // Delete message
    function deleteMessage(id) {
        if (!confirm(translations.confirmDeleteMessage)) return;

        fetch(`{{ route("admin.chat.delete", "") }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allMessages = allMessages.filter(m => m.id != id);
                    renderMessages();
                    toastr.success(translations.messageDeletedSuccessfully);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error(translations.failedToDeleteMessage);
            });
    }

    // Enter key to send
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });

    // Close modal on outside click
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    // Load initial messages
    loadMessages();
</script>
