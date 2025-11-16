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
    if (password.length < 6) {
        showError('Password must be at least 6 characters');
        return false;
    }
    
    // 验证密码强度（至少包含一个数字和一个字母）
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]/;
    if (!passwordRegex.test(password)) {
        showError('Password must contain at least one letter and one number');
        return false;
    }
    
    // 验证密码匹配
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return false;
    }
    
    // 如果所有验证通过，提交表单
    this.submit();
});

// 页面加载完成
window.onload = function() {
    console.log('Register page loaded');
};