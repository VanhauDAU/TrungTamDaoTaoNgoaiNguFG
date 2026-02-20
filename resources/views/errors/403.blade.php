<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/errors/error.css') }}">
</head>

<body>
    <div class="page">
        <div class="card">
            <div>
                <div class="badge">
                    <span class="badge-dot"></span>
                    <span>Oops, có gì đó không ổn...</span>
                </div>

                <div style="margin-top: 14px;">
                    <div class="code">403</div>
                    <h1 class="title">
                        Bạn không có quyền truy cập trang này.
                    </h1>
                    <p class="description">
                        Bạn không có quyền truy cập trang này. Vui lòng kiểm tra lại quyền truy cập hoặc liên hệ với
                        quản trị viên để được hỗ trợ.
                    </p>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-primary">
                        <span>Quay lại trang chủ</span>
                        <span style="font-size: 16px;">→</span>
                    </a>
                    <a href="javascript:history.back();" class="btn-secondary">
                        <span class="icon">⟵</span>
                        <span>Trở về trang trước</span>
                    </a>
                </div>

                <form class="search" action="{{ url('/') }}" method="get">
                    <input type="text" name="q"
                        placeholder="Thử tìm kiếm khóa học, bài viết hoặc nội dung bạn cần..." />
                    <button class="search-button" type="submit">Tìm kiếm</button>
                </form>

                <div class="meta">
                    <span>Gợi ý:</span>
                    <span class="meta-tag">Kiểm tra lại quyền truy cập</span>
                    <span class="meta-tag">Quay về trang chủ</span>
                    <span class="meta-tag">Liên hệ hỗ trợ nếu cần</span>
                </div>
            </div>

            <div class="illustration" aria-hidden="true">
                <div class="illu-circle"></div>
                <div class="illu-shadow"></div>

                <div class="illu-content">
                    <div class="illu-main">
                        <div class="illu-main-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="illu-avatar"></div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 600;">Hướng dẫn viên ảo</div>
                                    <div style="font-size: 11px; color: #cbd5f5;">Luôn sẵn sàng hỗ trợ bạn</div>
                                </div>
                            </div>
                            <div style="font-size: 11px; color: #e5e7eb;">Online • 24/7</div>
                        </div>

                        <h3>Bạn không có quyền truy cập trang này.</h3>
                        <p>
                            Đừng lo, hãy quay lại trang chủ để tiếp tục hành trình học tập và khám phá những điều thú
                            vị cùng chúng tôi.
                        </p>
                    </div>

                    <div class="illu-badge">
                        <span class="icon">⭐</span>
                        <span>Trải nghiệm thân thiện</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
