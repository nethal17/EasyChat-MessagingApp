<?php
require_once __DIR__ . '/config/config.php';

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
    <title>Message App - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto">

            <!-- Login Form -->
            <div class="bg-white rounded-xl p-8 mb-4 mt-32 shadow-lg">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-blue-600 mb-2 flex items-center justify-center gap-2">
                        <svg class="w-10 h-10 inline text-blue-600 bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        EasyChat
                    </h1>
                    <p class="text-gray-600">Connect with people instantly</p>
                </div>
                
                <form id="loginForm">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="you@example.com"
                        >
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2" for="password">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="••••••••"
                        >
                    </div>

                    <div id="loginError" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"></div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
                    >
                        Login
                    </button>
                </form>
            </div>

            <div class="text-center">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="views/register.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="public/js/auth.js"></script>
</body>
</html>
