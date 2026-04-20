@php
    $sections = [
        [
            'title' => 'Vận hành đào tạo',
            'groups' => [
                [
                    'label' => 'Nghiệp vụ chính',
                    'hint' => 'Học viên, lớp học, học phí, nội dung',
                    'icon' => 'fas fa-briefcase',
                    'active' => ['staff.dashboard', 'staff.hoc-vien.*', 'staff.dang-ky.*', 'staff.lop-hoc.*', 'staff.buoi-hoc.*', 'staff.hoa-don.*', 'staff.bai-viet.*', 'staff.danh-muc-bai-viet.*', 'staff.notifications.*'],
                    'open' => true,
                    'items' => [
                        ['label' => 'Dashboard', 'hint' => 'Hiệu suất vận hành', 'icon' => 'fas fa-chart-pie', 'route' => 'staff.dashboard', 'active' => ['staff.dashboard']],
                        ['label' => 'Học viên', 'hint' => 'Hồ sơ và quản lý', 'icon' => 'fas fa-user-graduate', 'route' => 'staff.hoc-vien.index', 'active' => ['staff.hoc-vien.*']],
                        ['label' => 'Đăng ký học', 'hint' => 'Ghi danh và điều chuyển', 'icon' => 'fas fa-file-circle-plus', 'route' => 'staff.dang-ky.index', 'active' => ['staff.dang-ky.*']],
                        ['label' => 'Lớp học', 'hint' => 'Mở lớp và xếp lịch', 'icon' => 'fas fa-chalkboard', 'route' => 'staff.lop-hoc.index', 'active' => ['staff.lop-hoc.*', 'staff.buoi-hoc.*']],
                        ['label' => 'Hóa đơn & phiếu thu', 'hint' => 'Theo dõi thanh toán', 'icon' => 'fas fa-receipt', 'route' => 'staff.hoa-don.index', 'active' => ['staff.hoa-don.*']],
                        ['label' => 'Bài viết', 'hint' => 'Tin tức và landing', 'icon' => 'fas fa-newspaper', 'route' => 'staff.bai-viet.index', 'active' => ['staff.bai-viet.*', 'staff.danh-muc-bai-viet.*']],
                        ['label' => 'Thông báo', 'hint' => 'Trao đổi nội bộ', 'icon' => 'fas fa-bell', 'route' => 'staff.notifications.index', 'active' => ['staff.notifications.*']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Staff Portal"
    portal-short-label="ST"
    portal-home-route="staff.dashboard"
    portal-accent="staff"
    :sections="$sections" />
