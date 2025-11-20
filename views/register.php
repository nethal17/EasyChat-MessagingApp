<?php
require_once __DIR__ . '/../config/config.php';

// Redirect to messages if already logged in
if (isLoggedIn()) {
    redirect('views/messages.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Message App</title>
    <script src="https://cdn.tailwindcss.com"></script>    
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto">
            <!-- Registration Form -->
            <div class="bg-white rounded-xl p-8 mb-4 shadow-lg">
                <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-blue-600 mb-2 flex items-center justify-center gap-2">
                    <svg class="w-10 h-10 inline text-blue-600 bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    EasyChat
                </h1>
                <p class="text-gray-600">Join our messaging community</p>
            </div>
                
                <form id="registerForm">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="name">
                            Full Name *
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="John Doe"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="reg_email">
                            Email Address *
                        </label>
                        <input 
                            type="email" 
                            id="reg_email" 
                            name="email" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="you@example.com"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">
                            Phone Number *
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0761234567"
                            pattern="[0-9]{10}"
                            maxlength="10"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Must be exactly 10 digits (e.g., 0761234567)
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="reg_password">
                            Password *
                        </label>
                        <input 
                            type="password" 
                            id="reg_password" 
                            name="password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your password"
                        >
                        <p class="mt-1 text-xs text-gray-500">
                            Password must contain at least 6 characters, including uppercase, lowercase, number, and special character
                        </p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm_password">
                            Confirm Password *
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Re-enter password"
                        >
                    </div>

                    <div id="registerError" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"></div>
                    <div id="registerSuccess" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg"></div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
                    >
                        Create Account
                    </button>
                </form>
            </div>

            <div class="text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="../index.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Login here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="../public/js/auth.js"></script>
</body>
</html>
