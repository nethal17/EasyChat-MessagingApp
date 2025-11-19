let currentChatUserId = null;
let lastMessageTime = null;
let messagePollingInterval = null;
let conversationsPollingInterval = null;

// Initialize messaging interface
document.addEventListener('DOMContentLoaded', () => {
    loadConversations();
    setupEventListeners();
    startConversationsPolling();
});

// Setup event listeners
function setupEventListeners() {
    // Message form submission
    document.getElementById('messageForm')?.addEventListener('submit', sendMessage);
    
    // New message button
    document.getElementById('newMessageBtn')?.addEventListener('click', showNewMessageModal);
    
    // Close modal
    document.getElementById('closeModal')?.addEventListener('click', closeNewMessageModal);
    
    // Message image upload preview
    document.getElementById('messageImage')?.addEventListener('change', previewMessageImage);
    
    // Remove image preview
    document.getElementById('removeImage')?.addEventListener('click', removeImagePreview);
    
    // Auto-resize textarea
    document.getElementById('messageText')?.addEventListener('input', autoResizeTextarea);
}

// Load conversations list
async function loadConversations() {
    try {
        const response = await fetch('../controllers/MessageController.php?action=get_conversations_list');
        const result = await response.json();
        
        if (result.success) {
            displayConversations(result.conversations);
        }
    } catch (error) {
        console.error('Error loading conversations:', error);
    }
}

// Display conversations in the sidebar
function displayConversations(conversations) {
    const container = document.getElementById('conversationsList');
    
    if (conversations.length === 0) {
        container.innerHTML = `
            <div class="flex items-center justify-center h-full text-gray-500">
                <div class="text-center p-4">
                    <p>No conversations yet</p>
                    <button onclick="showNewMessageModal()" class="mt-2 text-blue-600 hover:underline">
                        Start a new conversation
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = conversations.map(conv => {
        const isActive = conv.other_user_id == currentChatUserId;
        const profilePic = conv.other_user_picture 
            ? `${uploadUrl}profiles/${conv.other_user_picture}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(conv.other_user_name)}&size=48`;
        
        const lastMessage = conv.last_message_image 
            ? '📷 Image' 
            : (conv.last_message || 'No messages yet');
        
        const messagePreview = conv.last_sender_id == currentUserId 
            ? `You: ${lastMessage}` 
            : lastMessage;
        
        return `
            <div class="conversation-item p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer ${isActive ? 'bg-blue-50' : ''}"
                 onclick="openChat(${conv.other_user_id}, '${escapeHtml(conv.other_user_name)}', '${profilePic}')">
                <div class="flex items-center gap-3">
                    <img src="${profilePic}" alt="${escapeHtml(conv.other_user_name)}" 
                         class="w-12 h-12 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <h3 class="font-semibold text-gray-800 truncate">${escapeHtml(conv.other_user_name)}</h3>
                            <span class="text-xs text-gray-500">${formatTime(conv.last_message_time)}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">${escapeHtml(messagePreview)}</p>
                    </div>
                    ${conv.unread_count > 0 ? `
                        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                            ${conv.unread_count}
                        </span>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}

// Open chat with a specific user
async function openChat(userId, userName, userPicture) {
    currentChatUserId = userId;
    lastMessageTime = new Date().toISOString();
    
    // Update UI
    document.getElementById('noChatSelected').classList.add('hidden');
    document.getElementById('chatArea').classList.remove('hidden');
    
    // Update chat header
    document.getElementById('chatHeader').innerHTML = `
        <img src="${userPicture}" alt="${escapeHtml(userName)}" 
             class="w-10 h-10 rounded-full object-cover">
        <div class="ml-3">
            <h3 class="font-semibold text-gray-800">${escapeHtml(userName)}</h3>
            <p class="text-sm text-green-600">Active</p>
        </div>
    `;
    
    // Set receiver ID for form
    document.getElementById('receiverId').value = userId;
    
    // Load conversation
    await loadConversation(userId);
    
    // Start polling for new messages
    startMessagePolling();
}

// Load conversation messages
async function loadConversation(userId) {
    try {
        const response = await fetch(`../controllers/MessageController.php?action=get_conversation&user_id=${userId}`);
        const result = await response.json();
        
        if (result.success) {
            displayMessages(result.messages);
            scrollToBottom();
            
            // Update last message time
            if (result.messages.length > 0) {
                lastMessageTime = result.messages[result.messages.length - 1].created_at;
            }
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

// Display messages in chat area
function displayMessages(messages) {
    const container = document.getElementById('messagesContainer');
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="flex items-center justify-center h-full text-gray-500">
                <p>No messages yet. Start the conversation!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = messages.map(msg => {
        // Skip deleted messages
        if (msg.deleted_at) {
            const isSender = msg.sender_id == currentUserId;
            return `
                <div class="flex ${isSender ? 'justify-end' : 'justify-start'}">
                    <div class="flex gap-2 max-w-[70%] ${isSender ? 'flex-row-reverse' : ''}">
                        <div class="w-8"></div>
                        <div>
                            <div class="bg-gray-100 text-gray-500 italic rounded-lg px-4 py-2 border border-gray-300">
                                <p class="text-sm">🗑️ This message was deleted</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1 ${isSender ? 'justify-end' : ''}">
                                <span class="text-xs text-gray-400">${formatTime(msg.deleted_at)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        const isSender = msg.sender_id == currentUserId;
        const profilePic = msg.sender_picture 
            ? `${uploadUrl}profiles/${msg.sender_picture}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(msg.sender_name)}&size=40`;
        
        return `
            <div class="flex ${isSender ? 'justify-end' : 'justify-start'}" data-message-id="${msg.id}">
                <div class="flex gap-2 max-w-[70%] ${isSender ? 'flex-row-reverse' : ''}">
                    <img src="${profilePic}" alt="${escapeHtml(msg.sender_name)}" 
                         class="w-8 h-8 rounded-full object-cover">
                    <div>
                        <div class="${isSender ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'} 
                                    rounded-lg ${msg.message_text ? 'px-4 py-2' : 'p-1'} relative group">
                            ${msg.message_text ? `<p class="${msg.image_path ? 'mb-2' : ''}">${escapeHtml(msg.message_text)}</p>` : ''}
                            ${msg.image_path ? `
                                <img src="${uploadUrl}messages/${msg.image_path}" 
                                     alt="Message image" 
                                     class="rounded-lg max-w-xs cursor-pointer"
                                     onclick="window.open(this.src, '_blank')">
                            ` : ''}
                            ${isSender ? `
                                <button onclick="deleteMessage(${msg.id})" 
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                                        title="Delete message">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            ` : ''}
                        </div>
                        <div class="flex items-center gap-2 mt-1 ${isSender ? 'justify-end' : ''}">
                            <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
                            ${isSender && msg.is_read ? 
                                '<span class="text-xs text-blue-600">✓✓</span>' : 
                                (isSender ? '<span class="text-xs text-gray-400">✓</span>' : '')}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Send a message
async function sendMessage(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const messageText = formData.get('message_text').trim();
    const messageImage = formData.get('message_image');
    
    // Validate message
    if (!messageText && !messageImage.name) {
        return;
    }
    
    try {
        const response = await fetch('../controllers/MessageController.php?action=send', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear form
            document.getElementById('messageText').value = '';
            document.getElementById('messageImage').value = '';
            removeImagePreview();
            
            // Reload conversation
            await loadConversation(currentChatUserId);
            await loadConversations();
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    }
}

// Delete a message
async function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'delete_message');
        formData.append('message_id', messageId);

        const response = await fetch('../controllers/MessageController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Find and update the message in the DOM
            const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
            if (messageElement) {
                const isSender = true; // Only sender can delete
                messageElement.innerHTML = `
                    <div class="flex gap-2 max-w-[70%] flex-row-reverse">
                        <div class="w-8"></div>
                        <div>
                            <div class="bg-gray-100 text-gray-500 italic rounded-lg px-4 py-2 border border-gray-300">
                                <p class="text-sm">🗑️ This message was deleted</p>
                            </div>
                            <div class="flex items-center gap-2 mt-1 justify-end">
                                <span class="text-xs text-gray-400">Just now</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Reload conversations to update preview
            await loadConversations();
        } else {
            console.error('Delete failed:', result);
            alert(result.errors ? result.errors.join(', ') : 'Failed to delete message');
        }
    } catch (error) {
        console.error('Error deleting message:', error);
        alert('Failed to delete message. Please try again.');
    }
}

// Start polling for new messages
function startMessagePolling() {
    // Clear existing interval
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    // Poll every 3 seconds
    messagePollingInterval = setInterval(async () => {
        if (currentChatUserId && lastMessageTime) {
            try {
                // Check for new messages
                const response = await fetch(
                    `../controllers/MessageController.php?action=get_new_messages&user_id=${currentChatUserId}&since=${encodeURIComponent(lastMessageTime)}`
                );
                const result = await response.json();
                
                if (result.success && result.messages.length > 0) {
                    // Append new messages
                    const container = document.getElementById('messagesContainer');
                    const wasAtBottom = isScrolledToBottom();
                    
                    result.messages.forEach(msg => {
                        const isSender = msg.sender_id == currentUserId;
                        const profilePic = msg.sender_picture 
                            ? `${uploadUrl}profiles/${msg.sender_picture}`
                            : `https://ui-avatars.com/api/?name=${encodeURIComponent(msg.sender_name)}&size=40`;
                        
                        const messageHtml = `
                            <div class="flex ${isSender ? 'justify-end' : 'justify-start'}" data-message-id="${msg.id}">
                                <div class="flex gap-2 max-w-[70%] ${isSender ? 'flex-row-reverse' : ''}">
                                    <img src="${profilePic}" alt="${escapeHtml(msg.sender_name)}" 
                                         class="w-8 h-8 rounded-full object-cover">
                                    <div>
                                        <div class="${isSender ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'} 
                                                    rounded-lg ${msg.message_text ? 'px-4 py-2' : 'p-1'} relative group">
                                            ${msg.message_text ? `<p class="${msg.image_path ? 'mb-2' : ''}">${escapeHtml(msg.message_text)}</p>` : ''}
                                            ${msg.image_path ? `
                                                <img src="${uploadUrl}messages/${msg.image_path}" 
                                                     alt="Message image" 
                                                     class="rounded-lg max-w-xs cursor-pointer"
                                                     onclick="window.open(this.src, '_blank')">
                                            ` : ''}
                                            ${isSender ? `
                                                <button onclick="deleteMessage(${msg.id})" 
                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                                                        title="Delete message">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            ` : ''}
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 ${isSender ? 'justify-end' : ''}">
                                            <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        container.insertAdjacentHTML('beforeend', messageHtml);
                    });
                    
                    // Update last message time
                    lastMessageTime = result.messages[result.messages.length - 1].created_at;
                    
                    // Scroll to bottom if user was already at bottom
                    if (wasAtBottom) {
                        scrollToBottom();
                    }
                    
                    // Reload conversations list to update preview
                    await loadConversations();
                }

                // Check for deleted messages
                const deletedResponse = await fetch(
                    `../controllers/MessageController.php?action=get_deleted_messages&user_id=${currentChatUserId}&since=${encodeURIComponent(lastMessageTime)}`
                );
                const deletedResult = await deletedResponse.json();

                if (deletedResult.success && deletedResult.deleted_messages.length > 0) {
                    deletedResult.deleted_messages.forEach(deleted => {
                        const messageElement = document.querySelector(`[data-message-id="${deleted.id}"]`);
                        if (messageElement) {
                            const isSender = deleted.deleted_by == currentUserId;
                            messageElement.innerHTML = `
                                <div class="flex gap-2 max-w-[70%] ${isSender ? 'flex-row-reverse' : ''}">
                                    <div class="w-8"></div>
                                    <div>
                                        <div class="bg-gray-100 text-gray-500 italic rounded-lg px-4 py-2 border border-gray-300">
                                            <p class="text-sm">🗑️ This message was deleted</p>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 ${isSender ? 'justify-end' : ''}">
                                            <span class="text-xs text-gray-400">${formatTime(deleted.deleted_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });

                    // Reload conversations list to update preview
                    await loadConversations();
                }
            } catch (error) {
                console.error('Error polling messages:', error);
            }
        }
    }, 3000);
}

// Start polling for conversations updates
function startConversationsPolling() {
    conversationsPollingInterval = setInterval(() => {
        loadConversations();
    }, 5000);
}

// Show new message modal
async function showNewMessageModal() {
    document.getElementById('newMessageModal').classList.remove('hidden');
    
    // Load users
    try {
        const response = await fetch('../controllers/MessageController.php?action=get_users');
        const result = await response.json();
        
        if (result.success) {
            displayUsersList(result.users);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Display users list in modal
function displayUsersList(users) {
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center">No users found</p>';
        return;
    }
    
    container.innerHTML = users.map(user => {
        const profilePic = user.profile_picture 
            ? `${uploadUrl}profiles/${user.profile_picture}`
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=48`;
        
        return `
            <div class="flex items-center gap-3 p-3 hover:bg-gray-100 rounded-lg cursor-pointer"
                 onclick="selectUserAndClose(${user.id}, '${escapeHtml(user.name)}', '${profilePic}')">
                <img src="${profilePic}" alt="${escapeHtml(user.name)}" 
                     class="w-12 h-12 rounded-full object-cover">
                <div>
                    <h4 class="font-semibold text-gray-800">${escapeHtml(user.name)}</h4>
                    <p class="text-sm text-gray-600">${escapeHtml(user.email)}</p>
                </div>
            </div>
        `;
    }).join('');
}

// Select user and close modal
function selectUserAndClose(userId, userName, userPicture) {
    closeNewMessageModal();
    openChat(userId, userName, userPicture);
}

// Close new message modal
function closeNewMessageModal() {
    document.getElementById('newMessageModal').classList.add('hidden');
}

// Preview message image
function previewMessageImage(e) {
    const file = e.target.files[0];
    if (file) {
        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            e.target.value = '';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            e.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

// Remove image preview
function removeImagePreview() {
    document.getElementById('messageImage').value = '';
    document.getElementById('imagePreview').classList.add('hidden');
}

// Auto-resize textarea
function autoResizeTextarea(e) {
    e.target.style.height = 'auto';
    e.target.style.height = e.target.scrollHeight + 'px';
}

// Utility functions
function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

function isScrolledToBottom() {
    const container = document.getElementById('messagesContainer');
    return container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    // Less than a minute
    if (diff < 60000) {
        return 'Just now';
    }
    
    // Less than an hour
    if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `${minutes}m ago`;
    }
    
    // Today
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    // Yesterday
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    }
    
    // This week
    if (diff < 604800000) {
        return date.toLocaleDateString('en-US', { weekday: 'short' });
    }
    
    // Older
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (messagePollingInterval) clearInterval(messagePollingInterval);
    if (conversationsPollingInterval) clearInterval(conversationsPollingInterval);
});
