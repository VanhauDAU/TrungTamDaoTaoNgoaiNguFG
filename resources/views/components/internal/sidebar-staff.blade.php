@php
    $sections = [
        [
            'title' => 'Nghiệp vụ',
            'groups' => [
                [
                    'label' => 'Vận hành',
                    'icon' => 'fas fa-briefcase',
                    'active' => ['staff.dashboard', 'staff.lien-he.*', 'staff.hoc-vien.*', 'staff.dang-ky.*', 'staff.lop-hoc.*', 'staff.buoi-hoc.*', 'staff.hoa-don.*', 'staff.evaluations.*', 'staff.bai-viet.*', 'staff.danh-muc-bai-viet.*', 'staff.notifications.*'],
                    'open' => true,
                    'items' => [
                        ['label' => 'Dashboard', 'icon' => 'fas fa-chart-pie', 'route' => 'staff.dashboard', 'active' => ['staff.dashboard']],
                        ['label' => 'Liên hệ', 'icon' => 'fas fa-headset', 'route' => 'staff.lien-he.index', 'active' => ['staff.lien-he.*']],
                        ['label' => 'Học viên', 'icon' => 'fas fa-user-graduate', 'route' => 'staff.hoc-vien.index', 'active' => ['staff.hoc-vien.*']],
                        ['label' => 'Đăng ký học', 'icon' => 'fas fa-file-circle-plus', 'route' => 'staff.dang-ky.index', 'active' => ['staff.dang-ky.*']],
                        ['label' => 'Lớp học', 'icon' => 'fas fa-chalkboard', 'route' => 'staff.lop-hoc.index', 'active' => ['staff.lop-hoc.*', 'staff.buoi-hoc.*']],
                        ['label' => 'Hóa đơn', 'icon' => 'fas fa-receipt', 'route' => 'staff.hoa-don.index', 'active' => ['staff.hoa-don.*']],
                        ['label' => 'Báo cáo học tập', 'icon' => 'fas fa-file-signature', 'route' => 'staff.evaluations.index', 'active' => ['staff.evaluations.*']],
                        ['label' => 'Bài viết', 'icon' => 'fas fa-newspaper', 'route' => 'staff.bai-viet.index', 'active' => ['staff.bai-viet.*', 'staff.danh-muc-bai-viet.*']],
                        ['label' => 'Thông báo', 'icon' => 'fas fa-bell', 'route' => 'staff.notifications.index', 'active' => ['staff.notifications.*']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Cổng nhân viên"
    portal-short-label="ST"
    portal-home-route="staff.dashboard"
    portal-accent="staff"
    :sections="$sections" />
