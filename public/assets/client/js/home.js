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
    if (!form) return; // Thoát nếu không tìm thấy form

    const fullnameInput = form.querySelector('input[name="fullname"]');
    const phoneInput = form.querySelector('input[name="phone"]');
    const emailInput = form.querySelector('input[name="email"]');
    
    // Các quy tắc validate cho từng trường
    const validators = {
        fullname: (value) => value.trim().length >= 2,
        phone: (value) => {
            // Chỉ validate nếu có giá trị
            if (!value || value.trim() === '') return true;
            return /(0[3|5|7|8|9])+([0-9]{8})\b/g.test(value);
        },
        email: (value) => {
            // Chỉ validate nếu có giá trị
            if (!value || value.trim() === '') return true;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }
    };

    // Hàm kiểm tra từng Input
    function validateInput(input, showError = true) {
        const name = input.getAttribute('name');
        const value = input.value;
        let isValid = true;

        // Validate theo quy tắc của từng trường
        if (validators[name]) {
            isValid = validators[name](value);
        }

        if (showError) {
            if (!isValid) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        }
        
        return isValid;
    }

    // Kiểm tra ít nhất 1 trong 2: email hoặc phone
    function validateContactFields() {
        const phoneValue = phoneInput ? phoneInput.value.trim() : '';
        const emailValue = emailInput ? emailInput.value.trim() : '';
        
        // Ít nhất 1 trong 2 phải có giá trị
        const hasContact = phoneValue !== '' || emailValue !== '';
        
        if (!hasContact) {
            if (phoneInput) phoneInput.classList.add('is-invalid');
            if (emailInput) emailInput.classList.add('is-invalid');
            return false;
        }
        
        // Nếu có giá trị, validate format
        let isValid = true;
        
        if (phoneValue !== '' && phoneInput) {
            const phoneValid = validators.phone(phoneValue);
            if (!phoneValid) {
                phoneInput.classList.add('is-invalid');
                isValid = false;
            } else {
                phoneInput.classList.remove('is-invalid');
            }
        } else if (phoneInput) {
            phoneInput.classList.remove('is-invalid');
        }
        
        if (emailValue !== '' && emailInput) {
            const emailValid = validators.email(emailValue);
            if (!emailValid) {
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailInput.classList.remove('is-invalid');
            }
        } else if (emailInput) {
            emailInput.classList.remove('is-invalid');
        }
        
        return isValid;
    }

    // Validate real-time cho fullname
    if (fullnameInput) {
        fullnameInput.addEventListener('input', () => validateInput(fullnameInput));
        fullnameInput.addEventListener('blur', () => validateInput(fullnameInput));
    }

    // Validate real-time cho phone và email (kiểm tra cả 2 cùng lúc)
    if (phoneInput) {
        phoneInput.addEventListener('input', validateContactFields);
        phoneInput.addEventListener('blur', validateContactFields);
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', validateContactFields);
        emailInput.addEventListener('blur', validateContactFields);
    }

    // Hàm hiển thị toast notification
    function showToast(message, type = 'success') {
        // Tạo toast container nếu chưa có
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        // Tạo toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = type === 'success' ? '✓' : '✕';
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        toastContainer.appendChild(toast);

        // Animation vào
        setTimeout(() => toast.classList.add('show'), 10);

        // Tự động ẩn sau 5s
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // AJAX Form Submit
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Ngăn submit mặc định
        
        let isFormValid = true;

        // 1. Kiểm tra fullname
        if (fullnameInput && !validateInput(fullnameInput, true)) {
            isFormValid = false;
        }

        // 2. Kiểm tra email HOẶC phone (ít nhất 1 trong 2)
        if (!validateContactFields()) {
            isFormValid = false;
        }

        // Nếu form không hợp lệ, dừng lại
        if (!isFormValid) {
            showToast('Vui lòng kiểm tra lại thông tin đăng ký!', 'error');
            return;
        }

        // Lấy dữ liệu form
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Disable button để tránh submit nhiều lần
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Đang gửi...';

        // Gửi AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reset form sau khi thành công
                form.reset();
                // Xóa các class invalid
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            } else {
                // Hiển thị lỗi validation
                let errorMessage = data.message || 'Có lỗi xảy ra!';
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    if (firstError && firstError.length > 0) {
                        errorMessage = firstError[0];
                    }
                }
                showToast(errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra! Vui lòng thử lại sau.', 'error');
        })
        .finally(() => {
            // Enable lại button
            submitButton.disabled = false;
            submitButton.innerHTML = 'GỬI YÊU CẦU TƯ VẤN<i class="bi bi-send-fill ms-2"></i>';
        });
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