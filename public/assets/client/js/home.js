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

// register-sentence animation
document.addEventListener('DOMContentLoaded', function() {
    // Chọn phần tử cần theo dõi
    const trigger = document.querySelector('.animate-trigger');
    
    if (trigger) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Khi phần tử xuất hiện trong khung nhìn
                if (entry.isIntersecting) {
                    // Thêm class 'active' để kích hoạt CSS animation
                    entry.target.classList.add('active');
                    // Ngừng theo dõi sau khi đã kích hoạt
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 }); // Kích hoạt khi 50% phần tử xuất hiện

        observer.observe(trigger);
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.register-sentence-form');
    const inputs = form.querySelectorAll('.input-inline, .select-inline');

    // Các quy tắc Validate
    const validators = {
        fullname: (value) => value.trim().length >= 2,
        course: (value) => value !== "",
        phone: (value) => /(0[3|5|7|8|9])+([0-9]{8})\b/g.test(value), // Regex số ĐT VN
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
    };

    // Hàm kiểm tra từng Input
    function validateInput(input) {
        const name = input.getAttribute('name');
        const value = input.value;
        let isValid = true;

        if (validators[name]) {
            isValid = validators[name](value);
        }

        if (!isValid) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
        return isValid;
    }

    // Validate ngay khi người dùng nhập (Real-time)
    inputs.forEach(input => {
        input.addEventListener('input', () => validateInput(input));
        input.addEventListener('blur', () => validateInput(input)); // Khi rời khỏi input
    });

    // Chặn gửi Form nếu còn lỗi
    form.addEventListener('submit', function(e) {
        let isFormValid = true;

        inputs.forEach(input => {
            if (!validateInput(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault(); // Ngừng gửi form
            alert('Vui lòng kiểm tra lại thông tin đăng ký!');
        }
    });
});
// end register-sentence animation
document.addEventListener("DOMContentLoaded", function() {
    // 1. Hiệu ứng Fade Up khi cuộn trang
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fadeUp').forEach(el => {
        observer.observe(el);
    });

    // 2. Hiệu ứng vẽ chữ (Optional: nếu bạn dùng thư viện GSAP)
    // Nếu không dùng thư viện, CSS bên trên đã xử lý phần hover mực.
    
    // 3. Tự động vẽ path SVG ở tiêu đề chính khi load xong
    const mainTitlePath = document.querySelector('.title-style-4');
    if (mainTitlePath) {
        mainTitlePath.style.strokeDasharray = mainTitlePath.getTotalLength();
        mainTitlePath.style.strokeDashoffset = mainTitlePath.getTotalLength();
        
        setTimeout(() => {
            mainTitlePath.style.transition = "stroke-dashoffset 2s ease-in-out";
            mainTitlePath.style.strokeDashoffset = "0";
        }, 500);
    }
});