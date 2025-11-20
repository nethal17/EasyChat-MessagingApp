<?php
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$userData = $userModel->getUserById(getCurrentUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Message App</title>
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
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-3xl font-semibold text-gray-800 mb-6">My Profile</h2>

                <!-- Profile Picture -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <img 
                            id="profilePicturePreview"
                            src="<?php echo $userData['profile_picture'] ? UPLOAD_URL . 'profiles/' . $userData['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($userData['name']) . '&size=150'; ?>"
                            alt="Profile Picture" 
                            class="w-32 h-32 rounded-full object-cover border-4 border-blue-500"
                        >
                        <label for="profilePictureInput" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 cursor-pointer hover:bg-blue-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </label>
                    </div>
                </div>

                <!-- Profile Form -->
                <form id="profileForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" class="hidden">

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="profile_name">
                            Full Name *
                        </label>
                        <input 
                            type="text" 
                            id="profile_name" 
                            name="name" 
                            value="<?php echo htmlspecialchars($userData['name']); ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="profile_email">
                            Email Address *
                        </label>
                        <input 
                            type="email" 
                            id="profile_email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($userData['email']); ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="you@example.com"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Enter a valid email address
                        </p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="profile_phone">
                            Phone Number
                        </label>
                        <input 
                            type="tel" 
                            id="profile_phone" 
                            name="phone" 
                            value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0771234567"
                            pattern="[0-9]{10}"
                            maxlength="10"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Optional. Must be exactly 10 digits (e.g., 0771234567)
                        </p>
                    </div>

                    <div id="profileError" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"></div>
                    <div id="profileSuccess" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg"></div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
                    >
                        Save Changes
                    </button>
                </form>

                <!-- Account Info -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Account Information</h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Member since: <?php echo date('F j, Y', strtotime($userData['created_at'])); ?>
                    </p>
                    <button 
                        id="changePasswordBtn"
                        class="bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-200"
                    >
                        Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Change Password</h3>
                <button id="closePasswordModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="changePasswordForm">
                <input type="hidden" name="action" value="change_password">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="current_password">
                        Current Password *
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter current password"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="new_password">
                        New Password *
                    </label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter new password"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Must contain at least 6 characters, including uppercase, lowercase, number, and special character
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm_password">
                        Confirm New Password *
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Confirm new password"
                    >
                </div>

                <div id="passwordError" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"></div>
                <div id="passwordSuccess" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm"></div>

                <div class="flex gap-3">
                    <button 
                        type="button"
                        id="cancelPasswordBtn"
                        class="flex-1 bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-200"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
                    >
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../public/js/profile.js"></script>
</body>
</html>
