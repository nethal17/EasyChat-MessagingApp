// Profile picture preview
document.getElementById('profilePictureInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePicturePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Profile form handler
document.getElementById('profileForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('profileError');
    const successDiv = document.getElementById('profileSuccess');
    
    // Hide previous messages
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    // Client-side validation
    const email = formData.get('email');
    const phone = formData.get('phone');
    
    if (!validateEmail(email)) {
        errorDiv.innerHTML = 'Please enter a valid email address';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Validate phone number if provided
    if (phone && !validatePhone(phone)) {
        errorDiv.innerHTML = 'Please enter a valid phone number (10 digits)';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('../controllers/ProfileController.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            successDiv.innerHTML = result.message;
            successDiv.classList.remove('hidden');
            
            // Scroll to top to show message
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            // Show errors
            errorDiv.innerHTML = result.errors.join('<br>');
            errorDiv.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    } catch (error) {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
});

// Email validation helper
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Phone validation helper
function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone);
}

// Change Password Modal functionality
const changePasswordModal = document.getElementById('changePasswordModal');
const changePasswordBtn = document.getElementById('changePasswordBtn');
const closePasswordModal = document.getElementById('closePasswordModal');
const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
const changePasswordForm = document.getElementById('changePasswordForm');

// Open modal
changePasswordBtn?.addEventListener('click', () => {
    changePasswordModal.classList.remove('hidden');
    // Reset form
    changePasswordForm.reset();
    document.getElementById('passwordError').classList.add('hidden');
    document.getElementById('passwordSuccess').classList.add('hidden');
});

// Close modal
const closeModal = () => {
    changePasswordModal.classList.add('hidden');
    changePasswordForm.reset();
    document.getElementById('passwordError').classList.add('hidden');
    document.getElementById('passwordSuccess').classList.add('hidden');
};

closePasswordModal?.addEventListener('click', closeModal);
cancelPasswordBtn?.addEventListener('click', closeModal);

// Close modal when clicking outside
changePasswordModal?.addEventListener('click', (e) => {
    if (e.target === changePasswordModal) {
        closeModal();
    }
});

// Handle change password form submission
changePasswordForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('passwordError');
    const successDiv = document.getElementById('passwordSuccess');
    
    // Hide previous messages
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');
    
    // Client-side validation
    const currentPassword = formData.get('current_password');
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        errorDiv.innerHTML = 'All fields are required';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Validate new password complexity
    const passwordErrors = validatePassword(newPassword);
    if (passwordErrors.length > 0) {
        errorDiv.innerHTML = passwordErrors.join('<br>');
        errorDiv.classList.remove('hidden');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorDiv.innerHTML = 'New passwords do not match';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    try {
        const response = await fetch('../controllers/ProfileController.php', {
            method: 'POST',
            body: formData
        });
        
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        let result;
        
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text:', text);
            throw new Error('Invalid JSON response from server');
        }
        
        if (result.success) {
            // Show success message
            successDiv.innerHTML = result.message;
            successDiv.classList.remove('hidden');
            
            // Reset form
            changePasswordForm.reset();
            
            // Close modal after 2 seconds
            setTimeout(() => {
                closeModal();
            }, 2000);
        } else {
            // Show errors
            errorDiv.innerHTML = result.errors.join('<br>');
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        errorDiv.innerHTML = 'An error occurred: ' + error.message;
        errorDiv.classList.remove('hidden');
    }
});

// Password validation helper
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
    
    if (!/[!@#$%^&*()\[\]{}<>,.?":;|_\-+=\\\/`~']/.test(password)) {
        errors.push('Password must contain at least one special character');
    }
    
    return errors;
}
