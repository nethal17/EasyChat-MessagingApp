<?php
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Message App</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="messages.php" class="flex items-center space-x-1">
                    <svg class="w-10 h-10 inline text-blue-600 bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    <h1 class="text-2xl text-blue-600">EasyChat</h1>

                </a>
                <div class="flex items-center space-x-6">
                    <span class="text-gray-700">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    <a href="profile.php" class="text-gray-700 hover:text-blue-600 transition">
                        <svg class="w-7 h-7 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile
                    </a>
                    <a href="logout.php" class="text-gray-700 hover:text-red-600 transition">
                        <svg class="w-7 h-7 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="flex gap-6 h-[calc(100vh-120px)]">
            <!-- Conversations List -->
            <div class="w-1/3 bg-white rounded-lg shadow-lg flex flex-col">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Messages</h2>
                    <button id="newMessageBtn" class="mt-2 w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                        + New Message
                    </button>
                </div>
                <div id="conversationsList" class="flex-1 overflow-y-auto">
                    <div class="flex items-center justify-center h-full text-gray-500">
                        Loading conversations...
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 bg-white rounded-lg shadow-lg flex flex-col">
                <div id="noChatSelected" class="flex items-center justify-center h-full text-gray-500">
                    <div class="text-center">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-xl">Select a conversation to start messaging</p>
                    </div>
                </div>

                <div id="chatArea" class="hidden flex flex-col h-full">
                    <!-- Chat Header -->
                    <div id="chatHeader" class="p-4 border-b border-gray-200 flex items-center">
                        <!-- Will be populated dynamically -->
                    </div>

                    <!-- Messages Container -->
                    <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <!-- Messages will be loaded here -->
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200">
                        <form id="messageForm" enctype="multipart/form-data">
                            <div class="flex items-end gap-2">
                                <input type="hidden" id="receiverId" name="receiver_id">
                                <input type="hidden" name="action" value="send">
                                
                                <label for="messageImage" class="cursor-pointer text-gray-500 hover:text-blue-600 transition mb-1">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </label>
                                <input type="file" id="messageImage" name="message_image" accept="image/*" class="hidden">
                                
                                <div class="flex-1 relative">
                                    <textarea 
                                        id="messageText" 
                                        name="message_text" 
                                        rows="1"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                                        placeholder="Type a message..."
                                    ></textarea>
                                    <div id="imagePreview" class="hidden mt-2 relative inline-block">
                                        <img src="" alt="Preview" class="max-w-xs rounded-lg">
                                        <button type="button" id="removeImage" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition mb-2"
                                >
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Message Modal -->
    <div id="newMessageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">New Message</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="usersList" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Users will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        const currentUserId = <?php echo getCurrentUserId(); ?>;
        const uploadUrl = '<?php echo UPLOAD_URL; ?>';
    </script>
    <script src="../public/js/messages.js"></script>

</body>
</html>
