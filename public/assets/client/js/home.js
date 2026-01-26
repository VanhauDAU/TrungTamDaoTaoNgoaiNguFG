$(document).ready(function(){
    // Khởi tạo slider ảnh chính
    $('.main-image-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        fade: true,
        autoplay: true,
        autoplaySpeed: 5000,
        arrows: true,
        prevArrow: $('.prev'),
        nextArrow: $('.next'),
        asNavFor: '.badge-slider, .teacher-info-slider' // Đồng bộ với 2 slider kia
    });

    // Slider cho điểm IELTS bên trong ngôi sao
    $('.badge-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        fade: true,
        arrows: false,
        asNavFor: '.main-image-slider, .teacher-info-slider'
    });

    // Slider cho thông tin văn bản bên dưới
    $('.teacher-info-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        fade: true,
        arrows: false,
        asNavFor: '.main-image-slider, .badge-slider'
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const counterSection = document.querySelector('.counter');
    const counters = document.querySelectorAll('.number');
    const speed = 200; // Tốc độ càng thấp thì số nhảy càng nhanh

    const startCounter = (target) => {
        const updateCount = () => {
            // Lấy giá trị đích từ data-count, bỏ dấu phẩy nếu có
            const targetNum = +target.getAttribute('data-count').replace(/,/g, '');
            const count = +target.innerText.replace(/,/g, '');
            
            const inc = targetNum / speed;

            if (count < targetNum) {
                target.innerText = Math.ceil(count + inc).toLocaleString();
                setTimeout(updateCount, 1);
            } else {
                target.innerText = targetNum.toLocaleString();
            }
        };
        updateCount();
    };

    // Theo dõi khi phần tử xuất hiện trên màn hình
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                counters.forEach(counter => startCounter(counter));
                // Ngừng theo dõi sau khi đã chạy xong hiệu ứng
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    if(counterSection) {
        observer.observe(counterSection);
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // 1. Khởi tạo AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // 2. Logic Counter (Số chạy)
    const counterSection = document.querySelector('.counter');
    const counters = document.querySelectorAll('.number');
    const speed = 200;

    const startCounter = (target) => {
        // 1. Ép kiểu và xóa bỏ mọi ký tự không phải số trong data-count
        const targetNum = parseInt(target.getAttribute('data-count').replace(/\D/g, ''));
        let currentCount = 0; // Luôn bắt đầu từ 0 để đảm bảo hiệu ứng chạy từ đầu
        
        const duration = 2000; // Tổng thời gian hiệu ứng (2000ms = 2 giây)
        const frameRate = 1000 / 60; // Chạy 60 khung hình/giây cho mượt
        const totalSteps = duration / frameRate;
        const increment = targetNum / totalSteps;

        const updateCount = () => {
            currentCount += increment;
            
            if (currentCount < targetNum) {
                // Hiển thị số nguyên đã được định dạng dấu phẩy
                target.innerText = Math.floor(currentCount).toLocaleString();
                requestAnimationFrame(updateCount); // Dùng requestAnimationFrame thay cho setTimeout để mượt hơn
            } else {
                // Đảm bảo số cuối cùng hiển thị đúng con số đích
                target.innerText = targetNum.toLocaleString();
            }
        };
        
        updateCount();
    };

    // Theo dõi khi người dùng cuộn tới phần Counter
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                counters.forEach(counter => startCounter(counter));
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    if(counterSection) {
        observer.observe(counterSection);
    }
});