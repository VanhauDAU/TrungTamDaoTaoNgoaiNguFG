document.addEventListener("DOMContentLoaded", function() {
    // 1. Hiệu ứng vẽ nét mực tiêu đề chính
    const mainTitlePath = document.querySelector('.title-style-1');
    if (mainTitlePath) {
        const length = mainTitlePath.getTotalLength();
        mainTitlePath.style.strokeDasharray = length;
        mainTitlePath.style.strokeDashoffset = length;
        
        // Chạy animation sau khi trang load
        setTimeout(() => {
            mainTitlePath.style.transition = "stroke-dashoffset 2s ease-in-out";
            mainTitlePath.style.strokeDashoffset = "0";
        }, 300);
    }

    // 2. Tự động cuộn thanh danh mục đến mục đang Active
    const activeCate = document.querySelector('.cate_menu li.active');
    const cateMenu = document.querySelector('.cate_menu');
    if (activeCate && cateMenu) {
        cateMenu.scrollLeft = activeCate.offsetLeft - 20;
    }

    // 3. Animation Fade In cho các bài viết khi cuộn trang
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    }, observerOptions);

    document.querySelectorAll('.post_item').forEach(el => {
        el.style.opacity = "0";
        el.style.transform = "translateY(30px)";
        el.style.transition = "0.6s ease-out";
        observer.observe(el);
    });
});
