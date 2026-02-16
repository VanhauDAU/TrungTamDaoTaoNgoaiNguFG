<!DOCTYPE html> 
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Trang không tồn tại</title>

    <style>
        :root {
            --bg-page: #f4f6fb;
            --bg-card: #ffffff;
            --primary: #4f46e5;
            --primary-soft: #e0e7ff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --accent: #f97316;
            --border-subtle: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #e0f2fe, #fdf2ff 45%, var(--bg-page) 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text-main);
        }

        .page {
            width: 100%;
            max-width: 1120px;
            padding: 24px 16px;
        }

        .card {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            gap: 40px;
            padding: 40px 40px 36px;
            background: var(--bg-card);
            border-radius: 28px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow:
                0 28px 60px rgba(15, 23, 42, 0.16),
                0 0 0 1px rgba(255, 255, 255, 0.7) inset;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px 6px 10px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 13px;
            font-weight: 500;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #facc15;
            box-shadow: 0 0 0 5px rgba(250, 204, 21, 0.35);
        }

        .code {
            font-size: 56px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--primary);
        }

        .title {
            margin-top: 10px;
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
        }

        .highlight {
            color: var(--accent);
        }

        .description {
            margin-top: 12px;
            font-size: 15px;
            line-height: 1.7;
            color: var(--text-muted);
        }

        .actions {
            margin-top: 22px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(79, 70, 229, 0.35);
            transition: transform 0.15s ease-out, box-shadow 0.15s ease-out, filter 0.15s ease-out;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 18px 34px rgba(79, 70, 229, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.32);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 999px;
            border: 1px solid var(--border-subtle);
            background: #f9fafb;
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
        }

        .btn-secondary span.icon {
            font-size: 16px;
        }

        .search {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-subtle);
            background: #f9fafb;
        }

        .search input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 13px;
            background: transparent;
            color: var(--text-main);
        }

        .search input::placeholder {
            color: #9ca3af;
        }

        .search-button {
            padding: 6px 12px;
            border-radius: 999px;
            border: none;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-soft);
            color: var(--primary);
            cursor: pointer;
        }

        .meta {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12px;
            color: #9ca3af;
        }

        .meta-tag {
            padding: 3px 10px;
            border-radius: 999px;
            background: #f3f4f6;
        }

        .illustration {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .illu-circle {
            position: absolute;
            inset: 12%;
            border-radius: 999px;
            background: radial-gradient(circle at top, #e0f2fe, #e0e7ff 55%, #f5f3ff 100%);
        }

        .illu-shadow {
            position: absolute;
            bottom: 10%;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 40px;
            border-radius: 999px;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.32), transparent 70%);
            filter: blur(4px);
            opacity: 0.6;
        }

        .illu-content {
            position: relative;
            z-index: 1;
            width: 90%;
            max-width: 360px;
        }

        .illu-main {
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            padding: 20px 18px 18px;
            color: #e5e7eb;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.55);
        }

        .illu-main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .illu-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 2px solid rgba(248, 250, 252, 0.7);
            background: radial-gradient(circle at 30% 20%, #f97316, #ef4444 60%, #7c3aed 100%);
        }

        .illu-main h3 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .illu-main p {
            font-size: 12px;
            line-height: 1.7;
            color: #cbd5f5;
        }

        .illu-badge {
            position: absolute;
            top: 10%;
            right: 4%;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            color: #f9fafb;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .illu-badge span.icon {
            font-size: 14px;
        }

        @media (max-width: 840px) {
            .card {
                grid-template-columns: minmax(0, 1fr);
                padding: 28px 24px 24px;
            }

            .illustration {
                order: -1;
            }

            .page {
                padding-top: 18px;
                padding-bottom: 18px;
            }
        }
    </style>
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
                    <div class="code">404</div>
                    <h1 class="title">
                        Trang bạn tìm <span class="highlight">không tồn tại</span> hoặc đã bị di chuyển.
                    </h1>
                    <p class="description">
                        Có thể bạn đã gõ sai đường dẫn, trang đã bị xóa hoặc tạm thời không khả dụng. Bạn có thể quay
                        lại trang chủ hoặc tiếp tục khám phá những nội dung khác trên website.
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
                    <input type="text" name="q" placeholder="Thử tìm kiếm khóa học, bài viết hoặc nội dung bạn cần..." />
                    <button class="search-button" type="submit">Tìm kiếm</button>
                </form>

                <div class="meta">
                    <span>Gợi ý:</span>
                    <span class="meta-tag">Kiểm tra lại đường dẫn</span>
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

                        <h3>Bạn đang đi lạc?</h3>
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
