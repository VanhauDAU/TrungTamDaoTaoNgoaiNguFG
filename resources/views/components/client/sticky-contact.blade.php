<div class="sticky-contact">
    <a href="{{ route('home.index') }}#form_register_wrapper" class="contact-link">
        <span class="text-vertical">Đăng ký tư vấn</span>
        <div class="icon-box">
            <i class="bi bi-chat-left-text-fill"></i>
        </div>
    </a>
</div>

<style>
    .sticky-contact {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;
    }

    .contact-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: #E32D2D; /* Màu đỏ như ảnh */
        color: white !important;
        text-decoration: none;
        padding: 15px 8px;
        border-radius: 20px 0 0 20px; /* Bo góc bên trái */
        box-shadow: -2px 0 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .contact-link:hover {
        padding-right: 15px;
        background-color: #cc2626;
    }

    .text-vertical {
        writing-mode: vertical-rl; /* Xoay chữ dọc */
        text-orientation: mixed;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .icon-box {
        font-size: 20px;
        margin-top: 5px;
    }
</style>