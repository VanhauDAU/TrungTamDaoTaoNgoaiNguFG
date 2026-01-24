window.addEventListener('scroll', function() {
    const header = document.querySelector('.client-header');
    if (window.scrollY > 50) {
        header.classList.add('py-1', 'bg-white');
        header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
    } else {
        header.classList.remove('py-1');
        header.style.boxShadow = 'none';
    }
});