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
