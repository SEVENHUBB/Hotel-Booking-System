// 返回主页
function goBack() {
    window.location.href = '/Hotel_Booking_System/php/index.php';
}

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

// 显示错误信息
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
    
    // 3秒后自动隐藏
    setTimeout(() => {
        errorDiv.classList.remove('show');
    }, 3000);
}

// 显示成功信息
function showSuccess(message) {
    alert(message);
}

// 锁定表单
function lockForm(seconds) {
    const submitBtn = document.querySelector('.login-btn');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    // 禁用输入和按钮
    submitBtn.disabled = true;
    usernameInput.disabled = true;
    passwordInput.disabled = true;
    
    let timeRemaining = seconds;
    
    // 更新按钮文字显示倒计时
    const countdownInterval = setInterval(() => {
        submitBtn.textContent = `Locked (${timeRemaining}s)`;
        timeRemaining--;
        
        if (timeRemaining < 0) {
            clearInterval(countdownInterval);
            // 解锁表单
            submitBtn.disabled = false;
            usernameInput.disabled = false;
            passwordInput.disabled = false;
            submitBtn.textContent = 'Login';
            showSuccess('Account unlocked! You can try again now.');
        }
    }, 1000);
}

// 表单验证
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // 清空之前的错误信息
    document.getElementById('errorMessage').classList.remove('show');
    
    // 用户名验证
    if (username.length < 3) {
        e.preventDefault();
        showError('Username/Email must be at least 3 characters');
        return false;
    }
    
    // 密码验证
    if (password.length < 6) {
        e.preventDefault();
        showError('Password must be at least 6 characters');
        return false;
    }
    
    // 显示加载状态
    const submitBtn = document.querySelector('.login-btn');
    submitBtn.textContent = 'Logging in...';
    submitBtn.disabled = true;
    
    console.log('Form submitting...');
});

// Remember Me 功能
document.addEventListener('DOMContentLoaded', function() {
    // 检查是否有保存的用户名
    const savedUsername = localStorage.getItem('rememberedUsername');
    if (savedUsername) {
        document.getElementById('username').value = savedUsername;
        document.getElementById('remember').checked = true;
    }
});

// 保存 Remember Me
document.getElementById('loginForm').addEventListener('submit', function() {
    const rememberCheckbox = document.getElementById('remember');
    const username = document.getElementById('username').value.trim();
    
    if (rememberCheckbox.checked) {
        localStorage.setItem('rememberedUsername', username);
    } else {
        localStorage.removeItem('rememberedUsername');
    }
});

// 页面加载完成
window.onload = function() {
    console.log('Login page loaded');
    
    // 检查URL参数中是否有错误信息
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');
    const remaining = urlParams.get('remaining');
    const lockTime = urlParams.get('time');
    
    if (error === 'locked' && lockTime) {
        showError(`Account locked! Please wait ${lockTime} seconds.`);
        lockForm(parseInt(lockTime));
    } else if (error === 'invalid') {
        if (remaining) {
            showError(`Invalid username or password! Remaining attempts: ${remaining}`);
        } else {
            showError('Invalid username or password!');
        }
    } else if (error === 'empty') {
        showError('Please fill in all fields!');
    } else if (error === 'notfound') {
        if (remaining) {
            showError(`User not found! Remaining attempts: ${remaining}`);
        } else {
            showError('User not found!');
        }
    } else if (success === 'registered') {
        showSuccess('Registration successful! Please login.');
    } else if (success === 'password_reset') {
        showSuccess('Password reset successful! Please login with your new password.');
    } else if (success === 'logout') {
        showSuccess('You have been logged out successfully.');
    }
    
    // 清除URL参数
    if (error || success) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
};

// 防止表单重复提交
let isSubmitting = false;
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    isSubmitting = true;
});

function switchRole() {
    const roleInput = document.getElementById('role');
    const title = document.getElementById('loginTitle');
    const switchText = document.getElementById('switchRoleText');
    
    if (roleInput.value === 'tenant') {
        roleInput.value = 'admin';
        title.textContent = 'Admin Login';
        switchText.textContent = 'User';
    } else {
        roleInput.value = 'tenant';
        title.textContent = 'User Login';
        switchText.textContent = 'Admin';
    }
}