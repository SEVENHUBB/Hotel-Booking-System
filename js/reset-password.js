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

// 密码显示/隐藏切换
function togglePassword(inputId, toggleElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        toggleElement.innerHTML = '<i class="eye-icon">🙈</i>';
    } else {
        input.type = 'password';
        toggleElement.innerHTML = '<i class="eye-icon">👁️</i>';
    }
}

// 密码验证规则
const requirements = {
    length: { regex: /.{8,}/, element: document.getElementById('req-length') },
    uppercase: { regex: /[A-Z]/, element: document.getElementById('req-uppercase') },
    lowercase: { regex: /[a-z]/, element: document.getElementById('req-lowercase') },
    number: { regex: /[0-9]/, element: document.getElementById('req-number') }
};

const newPasswordInput = document.getElementById('newPassword');
const confirmPasswordInput = document.getElementById('confirmPassword');
const matchError = document.getElementById('matchError');
const submitBtn = document.getElementById('submitBtn');

// 检查密码要求
function checkPasswordRequirements(password) {
    let allValid = true;
    
    for (let key in requirements) {
        const req = requirements[key];
        const isValid = req.regex.test(password);
        
        if (isValid) {
            req.element.classList.add('valid');
        } else {
            req.element.classList.remove('valid');
            allValid = false;
        }
    }
    
    return allValid;
}

// 检查密码匹配
function checkPasswordMatch() {
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    if (confirmPassword === '') {
        matchError.textContent = '';
        matchError.style.color = '#666'; 
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        matchError.textContent = '✗ Passwords do not match';
        matchError.style.color = '#e74c3c';
        return false;
    } else {
        matchError.textContent = '✓ Passwords match';
        matchError.style.color = '#4caf50';
        return true;
    }
}

// 验证表单
function validateForm() {
    const passwordValid = checkPasswordRequirements(newPasswordInput.value);
    const matchValid = checkPasswordMatch();
    
    submitBtn.disabled = !(passwordValid && matchValid);
}

// 监听输入事件
newPasswordInput.addEventListener('input', () => {
    validateForm();
});

confirmPasswordInput.addEventListener('input', () => {
    validateForm();
});

// 表单提交
document.getElementById('resetPasswordForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    // 最终验证
    if (!checkPasswordRequirements(newPassword)) {
        alert('Password does not meet the requirements');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    // 提交表单
    e.target.submit();
});