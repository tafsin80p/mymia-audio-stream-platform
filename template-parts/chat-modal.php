<?php
/**
 * ========================================
 * NYMIA THEME - CHAT MODAL
 * ========================================
 * Chat interface for messaging between users
 * 
 * @package Nymia
 * @version 1.0
 */
?>

<!-- Floating Messenger Button -->
<div class="nymia-floating-messenger">
    <div class="nymia-floating-messenger-following" id="nymia-floating-recent" aria-live="polite" aria-label="<?php esc_attr_e('Recent chats', 'nymia'); ?>"></div>
    <div class="nymia-floating-notice" id="nymia-floating-notice" aria-live="assertive" aria-hidden="true"></div>
    <button type="button" class="nymia-floating-messenger-btn" id="nymia-floating-messenger-toggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="nymia-messenger-badge" id="nymia-messenger-badge" style="display: none;">0</span>
    </button>
</div>

<!-- Conversations Drawer -->
<div id="nymia-conversations-drawer" class="nymia-conversations-drawer" style="display: none;">
    <div class="nymia-conversations-header">
        <h3>Messages</h3>
        <button type="button" class="nymia-conversations-close" id="nymia-conversations-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="nymia-conversations-list" id="nymia-conversations-list">
        <div class="nymia-conversations-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>No conversations yet</p>
        </div>
    </div>
</div>

<!-- Chat Modal -->
<div id="nymia-chat-modal" class="nymia-chat-modal" style="display: none;">
    <div class="nymia-chat-container">
        <!-- Chat Header -->
        <div class="nymia-chat-header">
            <button type="button" class="nymia-chat-back" id="nymia-chat-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            <div class="nymia-chat-header-info">
                <img id="nymia-chat-avatar" src="" alt="" class="nymia-chat-header-avatar" />
                <div class="nymia-chat-header-text">
                    <h3 id="nymia-chat-name"></h3>
                    <span id="nymia-chat-status" class="nymia-chat-status offline">
                        <span class="nymia-chat-online-indicator-small"></span>
                        <span class="nymia-chat-status-text"><?php esc_html_e('Offline', 'nymia'); ?></span>
                    </span>
                </div>
            </div>
            <button type="button" class="nymia-chat-close" id="nymia-chat-close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div class="nymia-chat-messages" id="nymia-chat-messages">
            <div class="nymia-chat-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <p>Start a conversation</p>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="nymia-chat-input-wrapper">
            <textarea 
                id="nymia-chat-input" 
                class="nymia-chat-input" 
                placeholder="Type a message..." 
                rows="1"
                maxlength="500"></textarea>
            <button type="button" class="nymia-chat-send" id="nymia-chat-send">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentRecipientId = null;
    let messageInterval = null;
    let conversationsInterval = null;
    const chatNonce = '<?php echo wp_create_nonce("nymia_chat_action"); ?>';
    const labelOnline = '<?php echo esc_js(__('Online', 'nymia')); ?>';
    const labelOffline = '<?php echo esc_js(__('Offline', 'nymia')); ?>';
    const labelLastSeen = '<?php echo esc_js(__('Last seen', 'nymia')); ?>';
    const labelAgo = '<?php echo esc_js(__('ago', 'nymia')); ?>';
    const $chatStatus = $('#nymia-chat-status');

    function setChatStatus(isOnline, lastActiveHuman) {
        const $indicator = $chatStatus.find('.nymia-chat-online-indicator-small');
        const $text = $chatStatus.find('.nymia-chat-status-text');
        $chatStatus.removeClass('online offline');

        if (isOnline) {
            $chatStatus.addClass('online');
            $text.text(labelOnline);
        } else {
            $chatStatus.addClass('offline');
            if (lastActiveHuman) {
                $text.text(`${labelLastSeen} ${lastActiveHuman} ${labelAgo}`);
            } else {
                $text.text(labelOffline);
            }
        }
    }

    function fetchChatStatus(userId) {
        if (!userId) return;

        const formData = new FormData();
        formData.append('action', 'nymia_get_user_status');
        formData.append('nonce', chatNonce);
        formData.append('user_id', userId);

        $.ajax({
            url: nymiaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data) {
                    setChatStatus(response.data.is_online, response.data.last_active_human);
                }
            }
        });
    }

    function pingChatActivity() {
        const formData = new FormData();
        formData.append('action', 'nymia_chat_ping');
        formData.append('nonce', chatNonce);

        $.ajax({
            url: nymiaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        });
    }

    pingChatActivity();
    setInterval(pingChatActivity, 30000);
    
    // Open chat with user
    window.openChat = function(userId, userName, userAvatar, isOnline = null, lastActiveHuman = '') {
        $('#nymia-conversations-drawer').fadeOut(300);
        currentRecipientId = userId;
        $('#nymia-chat-name').text(userName);
        $('#nymia-chat-avatar').attr('src', userAvatar);
        $('#nymia-chat-modal').fadeIn(300);
        if (isOnline === true || isOnline === false) {
            setChatStatus(isOnline, lastActiveHuman);
        } else {
            setChatStatus(false, '');
        }
        fetchChatStatus(userId);
        loadMessages();
        
        // Start auto-refresh
        if (messageInterval) clearInterval(messageInterval);
        messageInterval = setInterval(loadMessages, 2000);
        
        // Focus input
        setTimeout(() => $('#nymia-chat-input').focus(), 400);
    };
    
    // Close chat
    function closeChat() {
        $('#nymia-chat-modal').fadeOut(300);
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
        currentRecipientId = null;
    }
    
    $('#nymia-chat-close').on('click', closeChat);
    $('#nymia-chat-back').on('click', function() {
        closeChat();
        showConversations();
    });
    
    // Click outside to close
    $('#nymia-chat-modal').on('click', function(e) {
        if ($(e.target).is('#nymia-chat-modal')) {
            closeChat();
            showConversations();
        }
    });
    
    // Floating messenger toggle
    $('#nymia-floating-messenger-toggle').on('click', function() {
        if ($('#nymia-chat-modal').is(':visible')) {
            closeChat();
        }
        showConversations();
    });
    
    // Close conversations drawer
    $('#nymia-conversations-close').on('click', function() {
        $('#nymia-conversations-drawer').fadeOut(300);
    });
    
    // Show conversations drawer
    function showConversations() {
        $('#nymia-conversations-drawer').fadeIn(300);
        loadConversations();
        
        // Start auto-refresh conversations
        if (conversationsInterval) clearInterval(conversationsInterval);
        conversationsInterval = setInterval(loadConversations, 3000);
    }
    
    // Load conversations
    function loadConversations() {
        const formData = new FormData();
        formData.append('action', 'nymia_get_chat_conversations');
        formData.append('nonce', chatNonce);
        
        $.ajax({
            url: nymiaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data.conversations) {
                    renderConversations(response.data.conversations);
                }
                if (response.success && response.data.notifications) {
                    renderFloatingNotifications(response.data.notifications);
                }
            }
        });
    }
    
    // Render conversations
    function renderConversations(conversations) {
        const container = $('#nymia-conversations-list');
        const recentContainer = $('#nymia-floating-recent');
        
        if (!conversations || conversations.length === 0) {
            container.html(`
                <div class="nymia-conversations-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p>No conversations yet</p>
                </div>
            `);
            $('#nymia-messenger-badge').hide();
            if (recentContainer.length) {
                recentContainer.html('');
            }
            return;
        }
        
        let totalUnread = 0;
        let html = '';
        let recentHtml = '';
        conversations.forEach(conv => {
            totalUnread += conv.unread_count;
            const time = new Date(conv.last_message_time);
            const timeStr = time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            const lastActiveHuman = conv.last_active_human || '';
            const isOnline = !!conv.is_online;
            const indicatorClass = isOnline ? 'is-online' : 'is-offline';
            const indicatorTitle = isOnline ? labelOnline : (lastActiveHuman ? `${labelLastSeen} ${lastActiveHuman} ${labelAgo}` : labelOffline);
            const lastActiveEsc = lastActiveHuman.replace(/'/g, "\\'");
            const indicatorTitleAttr = indicatorTitle.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            const userNameEsc = conv.user_name.replace(/'/g, "\\'");
            
            html += `
                <div class="nymia-conversation-item" onclick="openChat(${conv.user_id}, '${userNameEsc}', '${conv.user_avatar}', ${isOnline ? 'true' : 'false'}, '${lastActiveEsc}');">
                    <div class="nymia-conversation-avatar-wrapper">
                        <img src="${conv.user_avatar}" alt="${conv.user_name}" class="nymia-conversation-avatar" />
                        <span class="nymia-conversation-online-indicator ${indicatorClass}" title="${indicatorTitleAttr}"></span>
                    </div>
                    <div class="nymia-conversation-info">
                        <div class="nymia-conversation-header">
                            <span class="nymia-conversation-name">${conv.user_name}</span>
                            <span class="nymia-conversation-time">${timeStr}</span>
                        </div>
                        <p class="nymia-conversation-preview">${conv.last_message}</p>
                    </div>
                    ${conv.unread_count > 0 ? `<span class="nymia-conversation-unread">${conv.unread_count}</span>` : ''}
                </div>
            `;
        });
        
        container.html(html);
        
        if (recentContainer.length) {
            const maxRecent = 4;
            recentHtml = conversations.slice(0, maxRecent).map(conv => {
                const lastActiveHuman = conv.last_active_human || '';
                const isOnline = !!conv.is_online;
                const userNameEsc = conv.user_name.replace(/'/g, "\\'");
                const lastActiveEsc = lastActiveHuman.replace(/'/g, "\\'");
                const preview = (conv.last_message || '').replace(/'/g, "\\'");
                const statusLabel = isOnline ? labelOnline : (lastActiveHuman ? `${labelLastSeen} ${lastActiveHuman} ${labelAgo}` : labelOffline);
                const statusLabelAttr = statusLabel.replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                const unreadBadge = conv.unread_count > 0 ? `<span class="nymia-floating-recent-unread">${conv.unread_count > 9 ? '9+' : conv.unread_count}</span>` : '';
                return `
                    <button type="button" class="nymia-floating-recent-item" title="${statusLabelAttr}" onclick="openChat(${conv.user_id}, '${userNameEsc}', '${conv.user_avatar}', ${isOnline ? 'true' : 'false'}, '${lastActiveEsc}');">
                        <span class="nymia-floating-recent-avatar">
                            <img src="${conv.user_avatar}" alt="${conv.user_name}" />
                            <span class="nymia-floating-recent-status ${isOnline ? 'is-online' : 'is-offline'}"></span>
                            ${unreadBadge}
                        </span>
                        <span class="nymia-floating-recent-name">
                            <strong>${conv.user_name}</strong>
                            <em>${preview}</em>
                        </span>
                    </button>
                `;
            }).join('');
            recentContainer.html(recentHtml);
        }
        
        // Update badge
        if (totalUnread > 0) {
            $('#nymia-messenger-badge').text(totalUnread > 99 ? '99+' : totalUnread).show();
        } else {
            $('#nymia-messenger-badge').hide();
        }
    }
    
    // Render notification previews
    function renderFloatingNotifications(notifications) {
        const noticeContainer = $('#nymia-floating-notice');
        if (!noticeContainer.length) {
            return;
        }

        if (!notifications || !notifications.length) {
            noticeContainer.removeClass('is-visible').attr('aria-hidden', 'true').html('');
            return;
        }

        const maxNotices = 3;
        const content = notifications.slice(0, maxNotices).map(function (item) {
            const sender = item.user_name || '<?php echo esc_js(__('Unknown user', 'nymia')); ?>';
            const preview = item.preview || '';
            const unread = item.unread_count || 0;
            const safeSender = $('<div>').text(sender).html();
            const safePreview = $('<div>').text(preview).html();
            const unreadBadge = unread > 0 ? `<span class="nymia-floating-notice-badge">${unread > 9 ? '9+' : unread}</span>` : '';

            return `
                <button type="button" class="nymia-floating-notice-item" onclick="openChat(${item.user_id}, '${safeSender.replace(/'/g, "\\'")}', '${item.user_avatar}', ${item.is_online ? 'true' : 'false'}, '${(item.last_active_human || '').replace(/'/g, "\\'")}');">
                    <span class="nymia-floating-notice-avatar">
                        <img src="${item.user_avatar}" alt="${safeSender}">
                        <span class="nymia-floating-notice-status ${item.is_online ? 'is-online' : 'is-offline'}"></span>
                    </span>
                    <span class="nymia-floating-notice-body">
                        <strong>${safeSender}</strong>
                        <em>${safePreview}</em>
                    </span>
                    ${unreadBadge}
                </button>
            `;
        }).join('');

        noticeContainer.html(content).addClass('is-visible').attr('aria-hidden', 'false');

        clearTimeout(noticeContainer.data('dismissTimeout'));
        const timeout = setTimeout(function () {
            noticeContainer.removeClass('is-visible').attr('aria-hidden', 'true');
        }, 5000);
        noticeContainer.data('dismissTimeout', timeout);
    }
    
    // Load conversations on page load
    loadConversations();
    if (conversationsInterval) clearInterval(conversationsInterval);
    conversationsInterval = setInterval(loadConversations, 3000);
    
    // Send message
    function sendMessage() {
        const message = $('#nymia-chat-input').val().trim();
        if (!message || !currentRecipientId) return;
        
        const formData = new FormData();
        formData.append('action', 'nymia_send_chat_message');
        formData.append('nonce', chatNonce);
        formData.append('recipient_id', currentRecipientId);
        formData.append('message', message);
        
        $.ajax({
            url: nymiaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#nymia-chat-input').val('');
                    loadMessages();
                } else {
                    alert(response.data.message || 'Failed to send message');
                }
            },
            error: function() {
                alert('Error sending message');
            }
        });
    }
    
    // Send button click
    $('#nymia-chat-send').on('click', sendMessage);
    
    // Enter key to send
    $('#nymia-chat-input').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Auto-resize textarea
    $('#nymia-chat-input').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Load messages
    function loadMessages() {
        if (!currentRecipientId) return;
        
        const formData = new FormData();
        formData.append('action', 'nymia_get_chat_messages');
        formData.append('nonce', chatNonce);
        formData.append('other_user_id', currentRecipientId);
        
        $.ajax({
            url: nymiaAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success && response.data.messages) {
                    renderMessages(response.data.messages);
                    fetchChatStatus(currentRecipientId);
                }
            }
        });
    }
    
    // Render messages
    function renderMessages(messages) {
        const container = $('#nymia-chat-messages');
        const currentUserId = <?php echo get_current_user_id(); ?>;
        
        if (!messages || messages.length === 0) {
            container.html(`
                <div class="nymia-chat-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p>Start a conversation</p>
                </div>
            `);
            return;
        }
        
        let html = '';
        messages.forEach(msg => {
            const isOwn = msg.sender_id == currentUserId;
            const time = new Date(msg.time);
            const timeStr = time.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            
            html += `
                <div class="nymia-chat-message ${isOwn ? 'nymia-chat-message-own' : ''}">
                    ${!isOwn ? `<img src="${msg.sender_avatar}" alt="${msg.sender_name}" class="nymia-chat-message-avatar" />` : ''}
                    <div class="nymia-chat-message-content">
                        <div class="nymia-chat-message-bubble">
                            <p>${msg.message.replace(/\n/g, '<br>')}</p>
                            <span class="nymia-chat-message-time">${timeStr}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
        container.scrollTop(container[0].scrollHeight);
    }
});
</script>

