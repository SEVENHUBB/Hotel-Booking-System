// 显示/隐藏密码
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁️';
    }
}

const passwordInput = document.getElementById('password');
const reqLength = document.getElementById('req-length');
const reqUppercase = document.getElementById('req-uppercase');
const reqLowercase = document.getElementById('req-lowercase');
const reqNumber = document.getElementById('req-number');

passwordInput.addEventListener('input', function() {
    const password = this.value;
    
    // 检查长度（至少8个字符）
    if (password.length >= 8) {
        reqLength.classList.add('valid');
    } else {
        reqLength.classList.remove('valid');
    }
    
    // 检查大写字母
    if (/[A-Z]/.test(password)) {
        reqUppercase.classList.add('valid');
    } else {
        reqUppercase.classList.remove('valid');
    }
    
    // 检查小写字母
    if (/[a-z]/.test(password)) {
        reqLowercase.classList.add('valid');
    } else {
        reqLowercase.classList.remove('valid');
    }
    
    // 检查数字
    if (/[0-9]/.test(password)) {
        reqNumber.classList.add('valid');
    } else {
        reqNumber.classList.remove('valid');
    }
});

// 显示错误信息
function showError(message) {
    alert(message);
}

// 表单验证
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fullname = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const country = document.getElementById('country').value;
    const phone = document.getElementById('phone').value.trim();
    const gender = document.getElementById('gender').value;
    
    // 验证全名
    if (fullname.length < 2) {
        showError('Full name must be at least 2 characters');
        return false;
    }
    
    // 验证邮箱格式
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address');
        return false;
    }
    
    // 验证用户名
    if (username.length < 3) {
        showError('Username must be at least 3 characters');
        return false;
    }
    
    // 验证密码长度
    if (password.length < 8) {
        showError('Password must be at least 8 characters');
        return false;
    }
    
    // 验证密码强度 - 必须包含大写、小写、数字
    if (!/[A-Z]/.test(password)) {
        showError('Password must contain at least one uppercase letter (A-Z)');
        return false;
    }
    
    if (!/[a-z]/.test(password)) {
        showError('Password must contain at least one lowercase letter (a-z)');
        return false;
    }
    
    if (!/[0-9]/.test(password)) {
        showError('Password must contain at least one number (0-9)');
        return false;
    }
    
    // 验证密码匹配
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return false;
    }

    // ✅ Country 必填
    if (country === "") {
        showError("Please select your country");
        return false;
    }

    // 电话号码验证（至少 7 位）
    if (!/^[0-9]{7,15}$/.test(phone)) {
        showError("Please enter a valid phone number (7-15 digits)");
        return false;
    }

    // ✅ Gender 必填
    if (gender === "") {
        showError("Please select your gender");
        return false;
    }
    
    // 如果所有验证通过，提交表单
    this.submit();
});

// 页面加载完成
window.onload = function() {
    console.log('Register page loaded');
};