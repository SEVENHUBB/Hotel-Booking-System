// 验证退房日期必须在入住日期之后
document.querySelectorAll('form').forEach(form => {
    const checkin = form.querySelector('input[name="checkin_date"]');
    const checkout = form.querySelector('input[name="checkout_date"]');

    if (checkin && checkout) {
        checkin.addEventListener('change', function () {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkout.min = nextDay.toISOString().split('T')[0];

            // 如果当前checkout日期小于新的最小值，重置它
            if (checkout.value && checkout.value <= this.value) {
                checkout.value = checkout.min;
            }
        });

        checkout.addEventListener('change', function () {
            if (checkin.value && this.value <= checkin.value) {
                alert('Check-out date must be after check-in date!');
                this.value = '';
            }
        });
    }
});

// 平滑滚动效果
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// 添加加载动画
window.addEventListener('load', function () {
    document.querySelectorAll('.room-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});