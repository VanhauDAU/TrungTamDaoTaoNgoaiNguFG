@php
    $sections = [
        [
            'title' => 'Điều hành',
            'groups' => [
                [
                    'label' => 'Hệ thống',
                    'icon' => 'fas fa-shield-halved',
                    'active' => ['admin.dashboard', 'admin.tai-khoan.*', 'admin.cau-hinh.*', 'admin.lien-he.*'],
                    'open' => true,
                    'items' => [
                        ['label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
                        ['label' => 'Tài khoản', 'icon' => 'fas fa-user-shield', 'route' => 'admin.tai-khoan.index', 'active' => ['admin.tai-khoan.*']],
                        ['label' => 'Liên hệ', 'icon' => 'fas fa-envelope-open-text', 'route' => 'admin.lien-he.index', 'active' => ['admin.lien-he.*']],
                        ['label' => 'Cấu hình', 'icon' => 'fas fa-sliders', 'route' => 'admin.cau-hinh.index', 'active' => ['admin.cau-hinh.*']],
                    ],
                ],
            ],
        ],
        [
            'title' => 'Dữ liệu',
            'groups' => [
                [
                    'label' => 'Nhân sự',
                    'icon' => 'fas fa-users-cog',
                    'active' => ['admin.giao-vien.*', 'admin.nhan-vien.*', 'admin.nhan-su.*'],
                    'items' => [
                        ['label' => 'Giáo viên', 'icon' => 'fas fa-chalkboard-user', 'route' => 'admin.giao-vien.index', 'active' => ['admin.giao-vien.*']],
                        ['label' => 'Nhân viên', 'icon' => 'fas fa-id-badge', 'route' => 'admin.nhan-vien.index', 'active' => ['admin.nhan-vien.*']],
                        ['label' => 'Mẫu quy định', 'icon' => 'fas fa-file-signature', 'route' => 'admin.nhan-su.mau-quy-dinh.index', 'active' => ['admin.nhan-su.mau-quy-dinh.*']],
                    ],
                ],
                [
                    'label' => 'Đào tạo',
                    'icon' => 'fas fa-book-open-reader',
                    'active' => ['admin.co-so.*', 'admin.phong-hoc.*', 'admin.ca-hoc.*', 'admin.danh-muc-khoa-hoc.*', 'admin.khoa-hoc.*'],
                    'items' => [
                        ['label' => 'Cơ sở', 'icon' => 'fas fa-building', 'route' => 'admin.co-so.index', 'active' => ['admin.co-so.*']],
                        ['label' => 'Ca học', 'icon' => 'fas fa-clock', 'route' => 'admin.ca-hoc.index', 'active' => ['admin.ca-hoc.*']],
                        ['label' => 'Danh mục khóa học', 'icon' => 'fas fa-sitemap', 'route' => 'admin.danh-muc-khoa-hoc.index', 'active' => ['admin.danh-muc-khoa-hoc.*']],
                        ['label' => 'Khóa học', 'icon' => 'fas fa-graduation-cap', 'route' => 'admin.khoa-hoc.index', 'active' => ['admin.khoa-hoc.*']],
                    ],
                ],
                [
                    'label' => 'Nội dung',
                    'icon' => 'fas fa-layer-group',
                    'active' => ['admin.bai-viet.*', 'admin.danh-muc-bai-viet.*', 'admin.thong-bao.*'],
                    'items' => [
                        ['label' => 'Danh mục bài viết', 'icon' => 'fas fa-folder-tree', 'route' => 'admin.danh-muc-bai-viet.index', 'active' => ['admin.danh-muc-bai-viet.*']],
                        ['label' => 'Bài viết', 'icon' => 'fas fa-newspaper', 'route' => 'admin.bai-viet.index', 'active' => ['admin.bai-viet.*']],
                        ['label' => 'Thông báo', 'icon' => 'fas fa-bell', 'route' => 'admin.thong-bao.index', 'active' => ['admin.thong-bao.*']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Cổng quản trị"
    portal-short-label="AD"
    portal-home-route="admin.dashboard"
    portal-accent="admin"
    :sections="$sections" />
