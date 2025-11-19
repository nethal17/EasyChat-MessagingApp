// Login Form Handler
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('loginError');
    
    try {
        const response = await fetch('../controllers/AuthController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Redirect to messages page
            window.location.href = 'views/messages.php';
        } else {
            // Show errors
            errorDiv.innerHTML = result.errors.join('<br>');
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});

// Registration Form Handler
document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('registerError');
    const successDiv = document.getElementById('registerSuccess');
    
    // Hide previous messages
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    // Client-side validation
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    
    if (password !== confirmPassword) {
        errorDiv.innerHTML = 'Passwords do not match';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Validate password complexity
    const passwordErrors = validatePassword(password);
    if (passwordErrors.length > 0) {
        errorDiv.innerHTML = passwordErrors.join('<br>');
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('../controllers/AuthController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message and redirect
            successDiv.innerHTML = result.message;
            successDiv.classList.remove('hidden');
            
            setTimeout(() => {
                window.location.href = 'messages.php';
            }, 1500);
        } else {
            // Show errors
            errorDiv.innerHTML = result.errors.join('<br>');
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Password validation
function validatePassword(password) {
    const errors = [];
    
    if (password.length < 6) {
        errors.push('Password must be at least 6 characters');
    }
    
    if (!/[A-Z]/.test(password)) {
        errors.push('Password must contain at least one uppercase letter');
    }
    
    if (!/[a-z]/.test(password)) {
        errors.push('Password must contain at least one lowercase letter');
    }
    
    if (!/[0-9]/.test(password)) {
        errors.push('Password must contain at least one number');
    }
    
    if (!/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/;'`~]/.test(password)) {
        errors.push('Password must contain at least one special character (!@#$%^&*(),.?":{}|<>_-+=[]\\\/;\'`~)');
    }
    
    return errors;
}
