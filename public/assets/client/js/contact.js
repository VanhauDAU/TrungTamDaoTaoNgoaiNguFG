$(document).ready(function(){
    $('.map_main_slider').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true, // Hiệu ứng mượt mà cho bản đồ
        cssEase: 'linear',
        // Liên kết nút bấm tùy chỉnh của bạn
        prevArrow: $('.btn-prev'),
        nextArrow: $('.btn-next'),
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    adaptiveHeight: true
                }
            }
        ]
    });
});