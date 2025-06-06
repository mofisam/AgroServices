<?php
session_start();
require '../../config/db.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login");
    exit();
}

// Get active user from query parameter
$active_user_id = isset($_GET['user']) ? intval($_GET['user']) : null;
require '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Message Center</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --admin-color: #4361ee;
            --user-color: #4cc9f0;
            --admin-message-bg: #e3f2fd;
            --user-message-bg: #f0f0f0;
            --sidebar-bg: #f8f9fa;
            --chat-bg: #ffffff;
        }
        
        body {
            background-color: #f5f7fb;
        }
        
        .chat-app-container {
            height: calc(100vh - 120px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            background-color: white;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 350px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid #e9ecef;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 1.25rem;
            background-color: var(--primary-color);
            color: white;
        }
        
        .user-list-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-list-item:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        .user-list-item.active {
            background-color: rgba(67, 97, 238, 0.1);
            border-left: 3px solid var(--primary-color);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .last-message {
            font-size: 0.85rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .last-time {
            font-size: 0.75rem;
            color: #adb5bd;
        }
        
        /* Chat Area Styles */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            background-color: var(--chat-bg);
        }
        
        .chat-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e9ecef;
            background-color: white;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f5f7fb;
            background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill="rgba(0,0,0,0.05)" d="M30 10L50 30L70 10L90 30L70 50L90 70L70 90L50 70L30 90L10 70L30 50L10 30L30 10Z" /></svg>');
            background-size: 30px 30px;
            background-repeat: repeat;
            background-attachment: local;
        }
        
        .message {
            max-width: 70%;
            padding: 0.75rem 1.25rem;
            border-radius: 18px;
            margin-bottom: 1rem;
            position: relative;
            animation: fadeIn 0.3s ease-out;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .admin-message {
            background-color: var(--admin-message-bg);
            margin-left: auto;
            border-bottom-right-radius: 5px !important;
            color: var(--text-dark);
        }
        
        .user-message {
            background-color: var(--user-message-bg);
            margin-right: auto;
            border-bottom-left-radius: 5px !important;
            color: var(--text-dark);
        }
        
        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.5rem;
            display: block;
            text-align: right;
        }
        
        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid #e9ecef;
            background-color: white;
        }
        
        .message-input {
            border-radius: 25px;
            padding: 0.75rem 1.25rem;
            border: 1px solid #e9ecef;
            resize: none;
            box-shadow: none !important;
        }
        
        .message-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.1) !important;
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
            background-color: #3a56d4;
            transform: scale(1.05);
        }
        
        .empty-state {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .typing-indicator {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: white;
            border-radius: 18px;
            font-size: 0.85rem;
            color: #6c757d;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-left: 10px;
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .new-messages-alert:hover {
            background-color: #3a56d4;
            transform: translateX(-50%) scale(1.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Custom scrollbar */
        .sidebar::-webkit-scrollbar,
        .messages-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track,
        .messages-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar::-webkit-scrollbar-thumb,
        .messages-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover,
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="chat-app-container d-flex">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Conversations</h5>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-shield-alt me-1"></i> Admin
                    </span>
                </div>
                
                <div class="p-3 border-bottom">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                    </div>
                </div>
                
                <div id="userList">
                    <?php
                    // Get users with recent messages first
                    $users = $conn->query("
                        SELECT u.id, u.first_name, u.last_name, MAX(ac.created_at) as last_message_time
                        FROM users u
                        JOIN admin_chats ac ON u.id = ac.user_id
                        GROUP BY u.id
                        ORDER BY last_message_time DESC
                    ");
                    
                    while ($user = $users->fetch_assoc()):
                        $isActive = $active_user_id == $user['id'];
                        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                    ?>
                        <div class="user-list-item <?= $isActive ? 'active' : '' ?>" data-user-id="<?= $user['id'] ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-avatar" style="background-color: <?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>">
                                    <?= $initials ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between">
                                        <span class="user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                                        <small class="last-time"><?= date('H:i', strtotime($user['last_message_time'])) ?></small>
                                    </div>
                                    <div class="last-message">
                                        <?php
                                        $last_msg = $conn->query("
                                            SELECT message FROM admin_chats 
                                            WHERE user_id = {$user['id']} 
                                            ORDER BY created_at DESC LIMIT 1
                                        ")->fetch_assoc();
                                        echo htmlspecialchars(substr($last_msg['message'] ?? '', 0, 30)) . (strlen($last_msg['message'] ?? '') > 30 ? '...' : '');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="chat-area">
                <?php if ($active_user_id): ?>
                    <?php
                    // Get user details
                    $user = $conn->query("
                        SELECT first_name, last_name 
                        FROM users 
                        WHERE id = $active_user_id
                    ")->fetch_assoc();
                    
                    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                    ?>
                    
                    <!-- Chat Header -->
                    <div class="chat-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="user-avatar" style="background-color: <?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>">
                                <?= $initials ?>
                            </div>
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                                <small class="text-muted" id="typingIndicator"></small>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary me-2" id="refreshChat">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" id="clearChat" title="Clear conversation">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Messages Container -->
                    <div class="messages-container" id="adminChatBox">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading messages...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message Input -->
                    <div class="chat-input-area">
                        <form id="adminChatForm" class="d-flex align-items-center gap-2">
                            <textarea id="adminMessageInput" class="form-control message-input flex-grow-1" 
                                      placeholder="Type your message..." rows="1" autocomplete="off"></textarea>
                            <button type="submit" class="send-button" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-dots fa-3x mb-3"></i>
                        <h4>Select a conversation</h4>
                        <p class="text-muted">Choose a user from the sidebar to view messages</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        const activeUserId = <?= $active_user_id ?: 'null' ?>;
        let lastMessageId = 0;
        let isScrolledUp = false;
        let pollInterval;
        
        // Initialize chat if user is selected
        if (activeUserId) {
            loadMessages();
            startPolling();
        }
        
        // User list item click handler
        $(document).on('click', '.user-list-item', function() {
            const userId = $(this).data('user-id');
            window.location.href = `?user=${userId}`;
        });
        
        // Load messages (initial load)
        function loadMessages() {
            $('#adminChatBox').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading messages...</span>
                    </div>
                </div>
            `);
            
            $.get('fetch_messages.php', { user_id: activeUserId })
                .done(function(data) {
                    $('#adminChatBox').html(data);
                    scrollToBottom();
                    updateLastMessageId();
                    
                    // Highlight any new messages
                    $('.message').each(function() {
                        const messageId = $(this).data('id');
                        if (messageId > lastMessageId) {
                            $(this).css('background-color', 'rgba(67, 97, 238, 0.1)');
                            setTimeout(() => {
                                $(this).css('background-color', '');
                            }, 2000);
                        }
                    });
                })
                .fail(function() {
                    showAlert('Failed to load messages. Please try again.', 'danger');
                });
        }
        
        // Start polling for new messages
        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(checkForNewMessages, 2000);
        }
        
        // Check for new messages
        function checkForNewMessages() {
            $.get('fetch_new_messages.php', { 
                user_id: activeUserId,
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
            })
            .fail(function() {
                console.error('Failed to check for new messages');
            });
        }
        
        // Append new messages with animation
        function appendMessages(messages) {
            let hasNewMessages = false;
            
            messages.forEach(msg => {
                const isAdmin = msg.sender === 'admin';
                const messageClass = isAdmin ? 'admin-message' : 'user-message';
                const time = formatTime(msg.created_at);
                
                const messageHtml = `
                    <div class="message ${messageClass}" data-id="${msg.id}">
                        <div class="message-content">${msg.message}</div>
                        <span class="message-time">${time}</span>
                    </div>
                `;
                
                $('#adminChatBox').append(messageHtml);
                hasNewMessages = true;
            });
            
            if (hasNewMessages) {
                // Highlight new messages
                $('.message').filter(function() {
                    return $(this).data('id') > lastMessageId;
                }).css('background-color', 'rgba(67, 97, 238, 0.1)')
                  .animate({ backgroundColor: '' }, 2000);
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
            
            $('#adminChatBox').append(alert);
            alert.hide().fadeIn(200);
        }
        
        // Update last message ID
        function updateLastMessageId() {
            const lastMessage = $('.message').last();
            if (lastMessage.length) {
                lastMessageId = parseInt(lastMessage.data('id')) || 0;
            }
        }
        
        // Format time
        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        
        // Scroll to bottom
        function scrollToBottom() {
            const chatBox = $('#adminChatBox')[0];
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        
        // Track scroll position
        $('#adminChatBox').on('scroll', function() {
            const threshold = 100;
            isScrolledUp = $(this).scrollTop() + $(this).innerHeight() < $(this)[0].scrollHeight - threshold;
        });
        
        // Message submission
        $('#adminChatForm').on('submit', function(e) {
            e.preventDefault();
            const message = $('#adminMessageInput').val().trim();
            
            if (message === '') return;
            
            const submitBtn = $('#sendButton');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.post('send_message.php', {
                message: message,
                sender: 'admin',
                user_id: activeUserId
            })
            .done(function(response) {
                if (response.includes("Message sent")) {
                    $('#adminMessageInput').val('');
                    checkForNewMessages(); // Immediate check for new messages
                    
                    // Auto-resize textarea
                    $('#adminMessageInput').css('height', 'auto');
                } else {
                    showAlert('Failed to send message: ' + response, 'danger');
                }
            })
            .fail(function() {
                showAlert('Network error. Please check your connection.', 'danger');
            })
            .always(function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                $('#adminMessageInput').focus();
            });
        });
        
        // Auto-resize textarea
        $('#adminMessageInput').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // User search functionality
        $('#userSearch').on('keyup', function() {
            const search = $(this).val().toLowerCase();
            $('.user-list-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(search) > -1);
            });
        });
        
        // Manual refresh
        $('#refreshChat').click(function() {
            loadMessages();
            $(this).find('i').addClass('fa-spin');
            setTimeout(() => $(this).find('i').removeClass('fa-spin'), 1000);
        });
        
        // Clear chat confirmation
        $('#clearChat').click(function() {
            if (confirm('Are you sure you want to clear this conversation?')) {
                $.post('clear_chat.php', { user_id: activeUserId })
                    .done(function() {
                        loadMessages();
                        showAlert('Conversation cleared', 'success');
                    })
                    .fail(function() {
                        showAlert('Failed to clear conversation', 'danger');
                    });
            }
        });
        
        // Show alert
        function showAlert(message, type = 'danger') {
            const alert = $(`
                <div class="alert alert-${type} alert-dismissible fade show m-3">
                    <i class="fas ${type === 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            
            $('#adminChatBox').prepend(alert);
            
            setTimeout(() => alert.alert('close'), 5000);
        }
        
        // Keyboard shortcut - Enter to send (Shift+Enter for new line)
        $('#adminMessageInput').keydown(function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $('#adminChatForm').submit();
            }
        });
    });
    </script>
</body>
</html>

<?php include '../../includes/footer.php'; ?>