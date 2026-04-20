@php
    $sections = [
        [
            'title' => 'Không gian giảng dạy',
            'groups' => [
                [
                    'label' => 'Công việc hằng ngày',
                    'hint' => 'Lớp học và tương tác',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'active' => ['teacher.dashboard', 'teacher.profile', 'teacher.classes.*', 'teacher.schedule.*', 'teacher.notifications.*', 'teacher.materials.*', 'teacher.evaluations.*', 'teacher.attendance.*'],
                    'open' => true,
                    'items' => [
                        ['label' => 'Dashboard', 'hint' => 'Tổng quan dạy học', 'icon' => 'fas fa-compass', 'route' => 'teacher.dashboard', 'active' => ['teacher.dashboard']],
                        ['label' => 'Hồ sơ', 'hint' => 'Thông tin cá nhân', 'icon' => 'fas fa-id-card', 'route' => 'teacher.profile', 'active' => ['teacher.profile']],
                        ['label' => 'Lớp học của tôi', 'hint' => 'Danh sách lớp phụ trách', 'icon' => 'fas fa-people-roof', 'route' => 'teacher.classes.index', 'active' => ['teacher.classes.*']],
                        ['label' => 'Lịch dạy', 'hint' => 'Ca dạy sắp tới', 'icon' => 'fas fa-calendar-days', 'route' => 'teacher.schedule.index', 'active' => ['teacher.schedule.*']],
                        ['label' => 'Thông báo', 'hint' => 'Tin nhắn và thông tin', 'icon' => 'fas fa-bell', 'route' => 'teacher.notifications.index', 'active' => ['teacher.notifications.*']],
                        ['label' => 'Tài liệu', 'hint' => 'Học liệu lớp học', 'icon' => 'fas fa-folder-open', 'route' => 'teacher.materials.index', 'active' => ['teacher.materials.*']],
                        ['label' => 'Nhận xét', 'hint' => 'Đánh giá học viên', 'icon' => 'fas fa-comment-dots', 'route' => 'teacher.evaluations.index', 'active' => ['teacher.evaluations.*']],
                        ['label' => 'Điểm danh', 'hint' => 'Theo buổi học', 'icon' => 'fas fa-user-check', 'route' => 'teacher.attendance.index', 'active' => ['teacher.attendance.*']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Teacher Portal"
    portal-short-label="TC"
    portal-home-route="teacher.dashboard"
    portal-accent="teacher"
    :sections="$sections" />
