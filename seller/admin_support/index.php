<?php
session_start();
require '../../config/db.php';
require '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Chat</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --user-message-bg: #4361ee;
            --admin-message-bg: #f0f2f5;
            --text-dark: #212529;
            --text-light: #f8f9fa;
        }
        
        .chat-container {
            height: 80vh;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            background-color: white;
        }
        
        .chat-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chat-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin: 0;
        }
        
        .status-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8fafc;
            scroll-behavior: smooth;
        }
        
        .message {
            max-width: 75%;
            margin-bottom: 1rem;
            position: relative;
            animation: fadeIn 0.3s ease-out;
        }
        
        .user-message {
            margin-left: auto;
            background-color: var(--user-message-bg);
            color: white;
            border-radius: 18px 18px 0 18px;
            padding: 0.75rem 1rem;
        }
        
        .admin-message {
            margin-right: auto;
            background-color: var(--admin-message-bg);
            color: var(--text-dark);
            border-radius: 18px 18px 18px 0;
            padding: 0.75rem 1rem;
        }
        
        .message-content {
            word-wrap: break-word;
            line-height: 1.5;
        }
        
        .message-time {
            font-size: 0.7rem;
            opacity: 0.8;
            margin-top: 0.3rem;
            display: block;
            text-align: right;
        }
        
        .chat-input-container {
            padding: 1rem;
            background-color: white;
            border-top: 1px solid #e9ecef;
            position: relative;
        }
        
        .message-input {
            border-radius: 50px;
            padding: 0.75rem 1.25rem;
            border: 1px solid #dee2e6;
            resize: none;
        }
        
        .message-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
            border-color: var(--primary-color);
        }
        
        .send-button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .send-button:hover {
            background-color: var(--secondary-color);
            transform: scale(1.05);
        }
        
        .typing-indicator {
            position: absolute;
            top: -25px;
            left: 1.5rem;
            background-color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-size: 0.8rem;
            display: none;
        }
        
        .new-messages-alert {
            position: sticky;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .new-messages-alert:hover {
            background-color: var(--secondary-color);
            transform: translateX(-50%) scale(1.05);
        }
        
        .empty-state {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Custom scrollbar */
        .messages-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .messages-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <h2 class="chat-title"><i class="fas fa-headset me-2"></i>Admin Support</h2>
                <span class="status-badge" id="connectionStatus">
                    <i class="fas fa-circle text-success me-1"></i>
                    <span id="statusText">Online</span>
                </span>
            </div>
            
            <!-- Messages Container -->
            <div class="messages-container" id="chatBox">
                <div class="empty-state">
                    <i class="fas fa-comment-alt"></i>
                    <h4>Your conversation starts here</h4>
                    <p class="text-muted">Send your first message to admin</p>
                </div>
            </div>
            
            <!-- Chat Input Area -->
            <div class="chat-input-container">
                <div class="typing-indicator" id="typingIndicator">
                    <span class="typing-dots">
                        <span>.</span><span>.</span><span>.</span>
                    </span>
                    Admin is typing
                </div>
                
                <form id="chatForm" class="d-flex align-items-center gap-2">
                    <textarea id="messageInput" class="form-control message-input flex-grow-1" 
                              placeholder="Type your message..." rows="1" autocomplete="off"></textarea>
                    <button type="submit" class="send-button" id="sendButton">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Configuration
        const userId = <?= $user_id ?>;
        const userName = "<?= $user_name ?>";
        let lastMessageId = 0;
        let isScrolledUp = false;
        let isAdminTyping = false;
        let typingTimer;
        let connectionStatus = 'online';
        let retryCount = 0;
        const maxRetries = 5;
        const retryDelay = 3000;
        
        // DOM Elements
        const chatBox = $('#chatBox');
        const messageInput = $('#messageInput');
        const sendButton = $('#sendButton');
        const typingIndicator = $('#typingIndicator');
        const statusText = $('#statusText');
        
        // Initial load
        loadMessages();
        
        // Set up message polling
        let pollInterval = setInterval(pollForNewMessages, 3000);
        
        // Track scroll position
        chatBox.on('scroll', function() {
            const threshold = 100;
            isScrolledUp = $(this).scrollTop() + $(this).innerHeight() < $(this)[0].scrollHeight - threshold;
        });
        
        // Load messages (initial or refresh)
        function loadMessages() {
            chatBox.html(`
                <div class="d-flex justify-content-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading messages...</span>
                    </div>
                </div>
            `);
            
            $.get('fetch_messages.php', { user_id: userId })
                .done(function(data) {
                    chatBox.html(data);
                    
                    if ($('.message').length > 0) {
                        lastMessageId = $('.message').last().data('id');
                        scrollToBottom();
                    }
                    
                    isScrolledUp = false;
                    updateEmptyState();
                })
                .fail(function() {
                    chatBox.html(`
                        <div class="alert alert-danger m-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Failed to load messages. Please try again.
                        </div>
                    `);
                    updateConnectionStatus('offline');
                });
        }
        
        // Poll for new messages
        function pollForNewMessages() {
            if (connectionStatus === 'offline' && retryCount >= maxRetries) {
                clearInterval(pollInterval);
                return;
            }
            
            $.get('fetch_new_messages.php', { 
                user_id: userId,
                last_id: lastMessageId 
            })
            .done(function(data) {
                if (data.newMessages && data.newMessages.length > 0) {
                    lastMessageId = data.last_id;
                    
                    if (!isScrolledUp) {
                        appendMessages(data.newMessages);
                        scrollToBottom();
                    } else {
                        showNewMessageAlert(data.newMessages.length);
                    }
                }
                
                updateConnectionStatus('online');
                retryCount = 0;
            })
            .fail(function() {
                retryCount++;
                updateConnectionStatus('offline');
                
                if (retryCount >= maxRetries) {
                    statusText.text('Disconnected');
                }
            });
        }
        
        // Append new messages with animation
        function appendMessages(messages) {
            let hasNewMessages = false;
            
            messages.forEach(msg => {
                const isUser = msg.sender === 'user';
                const messageClass = isUser ? 'user-message' : 'admin-message';
                const time = formatTime(msg.created_at);
                
                const messageHtml = `
                    <div class="message ${messageClass}" data-id="${msg.id}">
                        <div class="message-content">${msg.message}</div>
                        <span class="message-time">${time}</span>
                    </div>
                `;
                
                chatBox.append(messageHtml);
                hasNewMessages = true;
            });
            
            if (hasNewMessages) {
                $('.message').last().hide().fadeIn(300);
                updateEmptyState();
            }
        }
        
        // Show new message alert when scrolled up
        function showNewMessageAlert(count) {
            if ($('#newMessagesAlert').length > 0) return;
            
            const alert = $(`
                <div id="newMessagesAlert" class="new-messages-alert">
                    <i class="fas fa-arrow-down"></i>
                    ${count} new message${count > 1 ? 's' : ''}
                </div>
            `);
            
            alert.click(function() {
                loadMessages();
                $(this).fadeOut(200, function() {
                    $(this).remove();
                });
            });
            
            chatBox.append(alert);
            alert.hide().fadeIn(200);
        }
        
        // Format time
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        
        // Scroll to bottom
        function scrollToBottom() {
            chatBox.scrollTop(chatBox[0].scrollHeight);
        }
        
        // Update connection status
        function updateConnectionStatus(status) {
            if (connectionStatus === status) return;
            
            connectionStatus = status;
            const statusElement = $('#connectionStatus i');
            
            if (status === 'online') {
                statusElement.removeClass('text-danger').addClass('text-success');
                statusText.text('Online');
            } else {
                statusElement.removeClass('text-success').addClass('text-danger');
                statusText.text('Reconnecting...');
            }
        }
        
        // Update empty state
        function updateEmptyState() {
            if ($('.message').length === 0) {
                chatBox.html(`
                    <div class="empty-state">
                        <i class="fas fa-comment-alt"></i>
                        <h4>Your conversation starts here</h4>
                        <p class="text-muted">Send your first message to admin</p>
                    </div>
                `);
            }
        }
        
        // Handle message submission
        $('#chatForm').on('submit', function(e) {
            e.preventDefault();
            const message = messageInput.val().trim();
            
            if (message === '') return;
            
            // Disable input during send
            messageInput.prop('disabled', true);
            sendButton.html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.post('send_message.php', {
                message: message,
                sender: 'user',
                user_id: userId
            })
            .done(function(response) {
                if (response.includes("Message sent")) {
                    messageInput.val('');
                    pollForNewMessages(); // Immediate check for new messages
                    
                    // Auto-resize textarea
                    messageInput.css('height', 'auto');
                } else {
                    showAlert('Failed to send message: ' + response, 'danger');
                }
            })
            .fail(function() {
                showAlert('Network error. Please check your connection.', 'danger');
            })
            .always(function() {
                messageInput.prop('disabled', false).focus();
                sendButton.html('<i class="fas fa-paper-plane"></i>');
            });
        });
        
        // Auto-resize textarea
        messageInput.on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            
            // Simulate admin typing after user stops typing
            clearTimeout(typingTimer);
            
            if (!isAdminTyping) {
                typingTimer = setTimeout(function() {
                    showTypingIndicator();
                }, 1000);
            }
        });
        
        // Show typing indicator
        function showTypingIndicator() {
            if (isAdminTyping) return;
            
            isAdminTyping = true;
            typingIndicator.fadeIn(200);
            
            // Hide after 3 seconds
            setTimeout(function() {
                typingIndicator.fadeOut(200);
                isAdminTyping = false;
            }, 3000);
        }
        
        // Show alert message
        function showAlert(message, type = 'danger') {
            const alert = $(`
                <div class="alert alert-${type} alert-dismissible fade show m-3">
                    <i class="fas ${type === 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            chatBox.prepend(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                alert.alert('close');
            }, 5000);
        }
        
        // Keyboard shortcuts
        messageInput.keydown(function(e) {
            // Submit on Enter (without Shift)
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $('#chatForm').submit();
            }
        });
    });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>