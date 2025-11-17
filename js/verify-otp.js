// 获取 URL 参数中的 email
const urlParams = new URLSearchParams(window.location.search);
const email = urlParams.get('email');

// 显示 email
if (email) {
    document.getElementById('emailDisplay').textContent = email;
    document.getElementById('email').value = email;
} else {
    alert('Invalid access. Please try again.');
    window.location.href = 'forgot-password.html';
}

// OTP 输入框自动跳转
const otpInputs = document.querySelectorAll('.otp-input');

otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = e.target.value;
        
        // 只允许数字
        if (!/^\d*$/.test(value)) {
            e.target.value = '';
            return;
        }
        
        // 自动跳转到下一个输入框
        if (value.length === 1 && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
    });
    
    // 处理退格键
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
    
    // 处理粘贴
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text');
        const digits = pastedData.replace(/\D/g, '').slice(0, 6);
        
        digits.split('').forEach((digit, i) => {
            if (otpInputs[i]) {
                otpInputs[i].value = digit;
            }
        });
        
        if (digits.length === 6) {
            otpInputs[5].focus();
        }
    });
});

// 表单提交
document.getElementById('verifyOtpForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    let otp = '';
    otpInputs.forEach(input => {
        otp += input.value;
    });
    
    if (otp.length !== 6) {
        alert('Please enter all 6 digits');
        return;
    }
    
    document.getElementById('otpCode').value = otp;
    e.target.submit();
});

// 倒计时功能
let countdown = 60;
const timerElement = document.getElementById('timer');
const resendBtn = document.getElementById('resendBtn');

function startTimer() {
    resendBtn.disabled = true;
    countdown = 60;
    
    const interval = setInterval(() => {
        countdown--;
        timerElement.textContent = `Resend available in ${countdown}s`;
        
        if (countdown <= 0) {
            clearInterval(interval);
            timerElement.textContent = '';
            resendBtn.disabled = false;
        }
    }, 1000);
}

// 页面加载时启动倒计时
startTimer();

// 重新发送 OTP
resendBtn.addEventListener('click', () => {
    if (!email) {
        alert('Email not found. Please try again.');
        return;
    }
    
    // 发送 AJAX 请求重新发送 OTP
    fetch('php/forgot-password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.text())
    .then(data => {
        alert('A new OTP has been sent to your email!');
        startTimer();
        
        // 清空输入框
        otpInputs.forEach(input => {
            input.value = '';
        });
        otpInputs[0].focus();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to resend OTP. Please try again.');
    });
});