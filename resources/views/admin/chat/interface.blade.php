<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .chat-wrapper {
        padding: 20px;
        min-height: calc(100vh - 120px);
    }

    .chat-container {
        max-width: 1200px;
        margin: 0 auto;
        height: 80vh;
        background: white;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border-radius: 12px;
        overflow: hidden;
    }

    /* Header Styles */
    .chat-header {
        background: white;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 2px solid #f3f4f6;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .back-btn {
        background: none;
        border: none;
        color: #6b7280;
        font-size: 24px;
        cursor: pointer;
        transition: color 0.3s;
    }

    .back-btn:hover {
        color: #111827;
    }

    .profile-pic {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #374151, #111827);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .chat-info {
        flex: 1;
    }

    .chat-info h2 {
        font-size: 20px;
        font-weight: bold;
        color: #111827;
        margin-bottom: 4px;
    }

    .status {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 14px;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
    }

    .refresh-btn {
        background: white;
        border: 2px solid #2563eb;
        color: #2563eb;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .refresh-btn:hover {
        background: #dbeafe;
    }

    /* Chat Messages Area */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        background: white;
        position: relative;
    }

    .message-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading Indicator */
    .loading-indicator {
        text-align: center;
        padding: 20px;
        color: #6b7280;
    }

    .loading-indicator i {
        font-size: 24px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Load More Button */
    .load-more-container {
        text-align: center;
        padding: 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .load-more-btn {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .load-more-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .load-more-btn:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }

    /* Received Messages */
    .received-message {
        justify-content: flex-start;
    }

    .received-message .message-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .received-message .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #374151, #111827);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        flex-shrink: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .received-message .message-bubble {
        background: white;
        color: #1f2937;
        padding: 12px 20px;
        border-radius: 16px;
        border-top-right-radius: 4px;
        max-width: 500px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #f3f4f6;
    }

    /* Sent Messages */
    .sent-message {
        justify-content: flex-end;
    }

    .sent-message .message-content {
        display: flex;
        align-items: flex-end;
        gap: 12px;
    }

    .sent-message .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d97706, #92400e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        font-weight: bold;
        flex-shrink: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .sent-message .message-bubble {
        background: var(--gradient-primary);
        color: white;
        padding: 12px 20px;
        border-radius: 16px;
        border-bottom-right-radius: 4px;
        max-width: 500px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .message-bubble p {
        margin: 0;
        word-wrap: break-word;
        line-height: 1.5;
        font-size: 15px;
    }

    .message-time {
        display: block;
        font-size: 12px;
        margin-top: 6px;
        opacity: 0.7;
    }

    .message-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        margin-top: 6px;
        font-size: 12px;
        opacity: 0.9;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
        opacity: 1;
    }

    .received-message .action-buttons {
        order: 2;
    }

    .sent-message .action-buttons {
        order: 1;
    }

    .edit-btn,
    .delete-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .edit-btn {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .edit-btn:hover {
        background: #dbeafe;
        transform: scale(1.1);
    }

    .delete-btn {
        border-color: #ef4444;
        color: #ef4444;
    }

    .delete-btn:hover {
        background: #fee2e2;
        transform: scale(1.1);
    }

    /* Chat Input Area */
    .chat-input {
        background: white;
        padding: 20px;
        border-top: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .icon-btn {
        background: none;
        border: none;
        color: #6b7280;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .icon-btn:hover {
        color: #10b981;
        transform: scale(1.15);
    }

    .message-input {
        flex: 1;
        padding: 16px 24px;
        background: #f3f4f6;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 16px;
        outline: none;
        transition: all 0.3s;
    }

    .message-input:focus     {
        background: white;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0 var(--primary-color);
    }

    .send-btn {
        width: 56px;
        height: 56px;
        background: var(--gradient-primary);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .send-btn:hover {
        background: var(--gradient-primary);
        transform: scale(1.1);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 32px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalSlide 0.3s ease;
    }

    @keyframes modalSlide {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .modal-header h3 {
        font-size: 24px;
        font-weight: bold;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-header h3 i {
        color: #3b82f6;
    }

    .close-btn {
        background: none;
        border: none;
        color: #9ca3af;
        font-size: 24px;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close-btn:hover {
        color: #6b7280;
    }

    #editMessageText {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 16px;
        font-family: inherit;
        resize: vertical;
        outline: none;
        transition: all 0.3s;
    }

    #editMessageText:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
    }

    .cancel-btn,
    .save-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .cancel-btn {
        background: #f3f4f6;
        color: #374151;
    }

    .cancel-btn:hover {
        background: #e5e7eb;
    }

    .save-btn {
        background: linear-gradient(135deg, #34d399, #10b981);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .save-btn:hover {
        background: linear-gradient(135deg, #10b981, #059669);
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    /* Scrollbar */
    .chat-messages::-webkit-scrollbar {
        width: 8px;
    }

    .chat-messages::-webkit-scrollbar-track {
        background: #f3f4f6;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Avatar Image Styles */
    .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #374151, #111827);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-avatar .avatar-fallback {
        color: white;
        font-size: 16px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .sent-message .user-avatar {
        background: var(--gradient-primary);
    }

    .sent-message .user-avatar.sender {
        width: 40px;
        height: 40px;
    }

    /* User name label for received messages */
    .user-name-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 4px !important;
    }

    /* Top Loading Indicator */
    .top-loading-indicator {
        display: none;
        justify-content: center;
        align-items: center;
        padding: 15px;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        border-radius: 12px;
        margin-bottom: 15px;
    }

    .loading-spinner {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--primary-color) !important;
        font-size: 14px;
        font-weight: 500;
    }

    .loading-spinner i {
        font-size: 18px;
    }

    /* Scroll Up Indicator */
    .scroll-up-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        padding: 10px;
        color: #9ca3af;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .scroll-up-indicator i {
        animation: bounce 1s infinite;
    }

    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    /* Smooth scrolling for chat container */
    .chat-messages {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }

    /* Pull to refresh indicator (optional visual feedback) */
    .chat-messages.loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #10b981, #3b82f6);
        background-size: 200% 100%;
        animation: loading-bar 1.5s infinite;
    }

    @keyframes loading-bar {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    /* Avatar Image Styles */
    .user-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: linear-gradient(135deg, #374151, #111827);
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .user-avatar .avatar-fallback {
        color: white;
        font-size: 16px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .sent-message .user-avatar.sender {
        width: 40px;
        height: 40px;
    }

    /* User name label for received messages */
    .user-name-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 4px !important;
    }

    /* Reply Preview Bar */
    .reply-preview {
        display: none;
        background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        padding: 12px 20px;
        border-left: 4px solid var(--primary-color);
        margin: 0;
        align-items: center;
        justify-content: space-between;
        animation: slideDown 0.2s ease;
    }

    .reply-preview.active {
        display: flex;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .reply-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .reply-indicator {
        width: 4px;
        height: 40px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .reply-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .reply-to-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .reply-to-text {
        font-size: 13px;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 400px;
    }

    .cancel-reply-btn {
        background: none;
        border: none;
        color: #9ca3af;
        font-size: 18px;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.2s;
    }

    .cancel-reply-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    /* Reply Bubble in Message */
    .replied-message {
        background: rgba(59, 130, 246, 0.1);
        border-left: 3px solid var(--primary-color);
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .replied-message:hover {
        background: rgba(59, 130, 246, 0.15);
    }

    .replied-message .replied-user {
        font-size: 12px;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 2px;
    }

    .replied-message .replied-text {
        font-size: 13px;
        color: #6b7280;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    /* Reply Button */
    .reply-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid #10b981;
        background: white;
        color: #10b981;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .reply-btn:hover {
        background: #d1fae5;
        transform: scale(1.1);
    }

    /* Highlight message when scrolled to */
    .message-wrapper.highlighted {
        animation: highlight 2s ease;
    }

    @keyframes highlight {
        0%, 100% {
            background: transparent;
        }
        50% {
            background: var(--primary-color);
        }
    }
</style>

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
            <button class="cancel-btn" onclick="closeEditModal()">
                <i class="fa fa-times"></i> {{ __('Cancel') }}
            </button>
            <button class="save-btn" onclick="saveEdit()">
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
