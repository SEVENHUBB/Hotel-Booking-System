document.addEventListener('DOMContentLoaded', function () {
  const carouselInner = document.getElementById('carouselInner');
  const dotsContainer = document.getElementById('dots');
  const prevBtn = document.querySelector('.prev');
  const nextBtn = document.querySelector('.next');
  const items = document.querySelectorAll('.carousel-item');
  let currentIndex = 0;
  const total = items.length;

  // 创建小圆点
  for (let i = 0; i < total; i++) {
    const dot = document.createElement('span');
    dot.className = 'dot' + (i === 0 ? ' active' : '');
    dot.addEventListener('click', () => goToSlide(i));
    dotsContainer.appendChild(dot);
  }
  const dots = document.querySelectorAll('.dot');

  function updateCarousel() {
    carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;
    dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % total;
    updateCarousel();
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + total) % total;
    updateCarousel();
  }

  function goToSlide(index) {
    currentIndex = index;
    updateCarousel();
  }

  prevBtn.addEventListener('click', prevSlide);
  nextBtn.addEventListener('click', nextSlide);

  // 自动播放 + 鼠标悬停暂停
  let autoPlay = setInterval(nextSlide, 5000);
  document.getElementById('carousel').addEventListener('mouseenter', () => clearInterval(autoPlay));
  document.getElementById('carousel').addEventListener('mouseleave', () => autoPlay = setInterval(nextSlide, 5000));

  // 键盘左右键
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') prevSlide();
    if (e.key === 'ArrowRight') nextSlide();
  });
});