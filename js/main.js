function goToLogin() {
    window.location.href = "/Hotel_Booking_System/login.html";
}

function goToRegister() {
    window.location.href = "/Hotel_Booking_System/register.html";
}

// 登出函数
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

// 检查用户登录状态
function checkLoginStatus() {
    fetch('check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                // 用户已登录
                document.getElementById('userNav').style.display = 'block';
                document.getElementById('guestNav').style.display = 'none';
                document.getElementById('welcomeMessage').textContent = 'Welcome, ' + data.tenant_name + '!';
            } else {
                // 用户未登录
                document.getElementById('guestNav').style.display = 'block';
                document.getElementById('userNav').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
            // 出错时显示guest导航
            document.getElementById('guestNav').style.display = 'block';
            document.getElementById('userNav').style.display = 'none';
        });
}

// 页面加载时检查登录状态
window.onload = function() {
    console.log('Main page loaded');
    checkLoginStatus();
};