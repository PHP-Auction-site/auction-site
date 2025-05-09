document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Password toggle visibility
    const togglePassword = document.querySelectorAll('.toggle-password');
    togglePassword.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password strength meter
    const newPassword = document.getElementById('new_password');
    if (newPassword) {
        newPassword.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrengthMeter(strength);
        });
    }

    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
            e.target.value = value;
        });
    }

    // Auto-hide flash messages
    const flashMessages = document.querySelectorAll('.alert');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });

    // Profile picture preview
    const profilePicInput = document.getElementById('profile_picture');
    const profilePicPreview = document.getElementById('profile_pic_preview');
    
    if (profilePicInput && profilePicPreview) {
        profilePicInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                    profilePicPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    
    // Contains number
    if (/\d/.test(password)) strength += 1;
    
    // Contains lowercase
    if (/[a-z]/.test(password)) strength += 1;
    
    // Contains uppercase
    if (/[A-Z]/.test(password)) strength += 1;
    
    // Contains special char
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    return strength;
}

// Update password strength meter
function updatePasswordStrengthMeter(strength) {
    const meter = document.getElementById('password-strength');
    if (!meter) return;

    const strengthText = document.getElementById('strength-text');
    let text = '';
    let color = '';

    switch(strength) {
        case 0:
        case 1:
            text = 'Weak';
            color = '#dc3545';
            break;
        case 2:
        case 3:
            text = 'Medium';
            color = '#ffc107';
            break;
        case 4:
            text = 'Strong';
            color = '#198754';
            break;
        case 5:
            text = 'Very Strong';
            color = '#0d6efd';
            break;
    }

    meter.style.width = (strength * 20) + '%';
    meter.style.backgroundColor = color;
    if (strengthText) strengthText.textContent = text;
} 