// Client-side form validation

document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                showFieldError(this, '有効なメールアドレスを入力してください。');
            } else {
                clearFieldError(this);
            }
        });
    });
    
    // Password confirmation validation
    const passwordConfirm = document.getElementById('password_confirm');
    if (passwordConfirm) {
        passwordConfirm.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                showFieldError(this, 'パスワードが一致しません。');
            } else {
                clearFieldError(this);
            }
        });
    }
    
    // Required field validation
    const requiredInputs = document.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                showFieldError(this, 'この項目は必須です。');
            } else {
                clearFieldError(this);
            }
        });
    });
});

function showFieldError(input, message) {
    // Remove existing error
    clearFieldError(input);
    
    // Add error class
    input.classList.add('border-red-500');
    
    // Create error message
    const errorDiv = document.createElement('p');
    errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
    errorDiv.textContent = message;
    
    // Insert after input
    input.parentNode.insertBefore(errorDiv, input.nextSibling);
}

function clearFieldError(input) {
    input.classList.remove('border-red-500');
    
    const errorMsg = input.parentNode.querySelector('.field-error');
    if (errorMsg) {
        errorMsg.remove();
    }
}
