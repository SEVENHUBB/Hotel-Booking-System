// 显示错误信息
function showMessage(message, type = 'error') {
    alert(message);
}

// 发送OTP表单验证
document.getElementById('forgotPasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    
    // 验证邮箱格式
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Please enter a valid email address');
        return false;
    }
    
    // 显示加载状态
    const submitBtn = document.querySelector('.login-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending OTP...';
    submitBtn.disabled = true;
    
    // 提交表单
    this.submit();
});

// 验证OTP表单
document.getElementById('verifyOtpForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const otp = document.getElementById('otp').value.trim();
    
    // 验证OTP格式（6位数字）
    if (!/^\d{6}$/.test(otp)) {
        showMessage('OTP must be 6 digits');
        return false;
    }
    
    // 提交表单
    this.submit();
});

// 重置密码表单
document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // 验证密码长度
    if (newPassword.length < 6) {
        showMessage('Password must be at least 6 characters');
        return false;
    }
    
    // 验证密码强度
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]/;
    if (!passwordRegex.test(newPassword)) {
        showMessage('Password must contain at least one letter and one number');
        return false;
    }
    
    // 验证密码匹配
    if (newPassword !== confirmPassword) {
        showMessage('Passwords do not match');
        return false;
    }
    
    // 提交表单
    this.submit();
});

// 倒计时重新发送OTP
function startResendTimer() {
    let timeLeft = 60;
    const resendBtn = document.getElementById('resendOtp');
    
    if (!resendBtn) return;
    
    resendBtn.disabled = true;
    resendBtn.textContent = `Resend OTP (${timeLeft}s)`;
    
    const timer = setInterval(function() {
        timeLeft--;
        resendBtn.textContent = `Resend OTP (${timeLeft}s)`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend OTP';
        }
    }, 1000);
}

// 页面加载时启动倒计时
window.onload = function() {
    if (document.getElementById('verifyOtpForm')) {
        startResendTimer();
    }
};