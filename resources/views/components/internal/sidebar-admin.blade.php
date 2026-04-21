@php
    $sections = [
        [
            'title' => 'Quản trị hệ thống',
            'groups' => [
                [
                    'label' => 'Điều hành',
                    'hint' => 'Toàn cục và phân quyền',
                    'icon' => 'fas fa-shield-halved',
                    'active' => ['admin.dashboard', 'admin.tai-khoan.*', 'admin.cau-hinh.*'],
                    'items' => [
                        ['label' => 'Dashboard', 'hint' => 'Tổng quan hệ thống', 'icon' => 'fas fa-chart-line', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
                        ['label' => 'Tài khoản hệ thống', 'hint' => 'Quản trị truy cập', 'icon' => 'fas fa-user-shield', 'route' => 'admin.tai-khoan.index', 'active' => ['admin.tai-khoan.*']],
                        ['label' => 'Cấu hình', 'hint' => 'Thiết lập cổng nội bộ', 'icon' => 'fas fa-sliders', 'route' => 'admin.cau-hinh.index', 'active' => ['admin.cau-hinh.*']],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Dữ liệu vận hành',
            'groups' => [
                [
                    'label' => 'Nhân sự',
                    'hint' => 'Giáo viên và nhân viên',
                    'icon' => 'fas fa-users-cog',
                    'active' => ['admin.giao-vien.*', 'admin.nhan-vien.*', 'admin.nhan-su.*'],
                    'items' => [
                        ['label' => 'Giáo viên', 'hint' => 'Danh sách & hồ sơ', 'icon' => 'fas fa-chalkboard-user', 'route' => 'admin.giao-vien.index', 'active' => ['admin.giao-vien.*']],
                        ['label' => 'Nhân viên', 'hint' => 'Tài khoản vận hành', 'icon' => 'fas fa-id-badge', 'route' => 'admin.nhan-vien.index', 'active' => ['admin.nhan-vien.*']],
                        ['label' => 'Mẫu quy định', 'hint' => 'Khung tính lương', 'icon' => 'fas fa-file-signature', 'route' => 'admin.nhan-su.mau-quy-dinh.index', 'active' => ['admin.nhan-su.mau-quy-dinh.*']],
                    ],
                ],
                [
                    'label' => 'Đào tạo',
                    'hint' => 'Cơ sở và chương trình',
                    'icon' => 'fas fa-book-open-reader',
                    'active' => ['admin.co-so.*', 'admin.phong-hoc.*', 'admin.ca-hoc.*', 'admin.danh-muc-khoa-hoc.*', 'admin.khoa-hoc.*'],
                    'items' => [
                        ['label' => 'Cơ sở', 'hint' => 'Địa điểm đào tạo', 'icon' => 'fas fa-building', 'route' => 'admin.co-so.index', 'active' => ['admin.co-so.*']],
                        ['label' => 'Ca học', 'hint' => 'Khung giờ giảng dạy', 'icon' => 'fas fa-clock', 'route' => 'admin.ca-hoc.index', 'active' => ['admin.ca-hoc.*']],
                        ['label' => 'Danh mục khóa học', 'hint' => 'Phân loại chương trình', 'icon' => 'fas fa-sitemap', 'route' => 'admin.danh-muc-khoa-hoc.index', 'active' => ['admin.danh-muc-khoa-hoc.*']],
                        ['label' => 'Khóa học', 'hint' => 'Nội dung và học phí', 'icon' => 'fas fa-graduation-cap', 'route' => 'admin.khoa-hoc.index', 'active' => ['admin.khoa-hoc.*']],
                    ],
                ],
                [
                    'label' => 'Nội dung & liên lạc',
                    'hint' => 'Website và thông báo',
                    'icon' => 'fas fa-layer-group',
                    'active' => ['admin.bai-viet.*', 'admin.danh-muc-bai-viet.*', 'admin.thong-bao.*', 'admin.lien-he.*'],
                    'items' => [
                        ['label' => 'Danh mục bài viết', 'hint' => 'Cấu trúc nội dung', 'icon' => 'fas fa-folder-tree', 'route' => 'admin.danh-muc-bai-viet.index', 'active' => ['admin.danh-muc-bai-viet.*']],
                        ['label' => 'Bài viết', 'hint' => 'Tin tức và landing', 'icon' => 'fas fa-newspaper', 'route' => 'admin.bai-viet.index', 'active' => ['admin.bai-viet.*']],
                        ['label' => 'Thông báo', 'hint' => 'Gửi tới người dùng nội bộ', 'icon' => 'fas fa-bell', 'route' => 'admin.thong-bao.index', 'active' => ['admin.thong-bao.*']],
                        ['label' => 'Liên hệ', 'hint' => 'Yêu cầu từ khách hàng', 'icon' => 'fas fa-envelope-open-text', 'route' => 'admin.lien-he.index', 'active' => ['admin.lien-he.*']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Admin Portal"
    portal-short-label="AD"
    portal-home-route="admin.dashboard"
    portal-accent="admin"
    :sections="$sections" />
